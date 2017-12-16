<?php
namespace NeosRulez\ShoppingCart\Controller;

/*
 * This file is part of the NeosRulez.ShoppingCart package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

/**
 * @Flow\Scope("singleton")
 */
class CartController extends ActionController
{

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Model\Cart
     */
    protected $cart;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\I18n\Translator
     */
    protected $translator;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings) {
        $this->settings = $settings;
    }

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\CountryRepository
     */
    protected $countryRepository;

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\DeliveryRepository
     */
    protected $deliveryRepository;

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\DeliverycostRepository
     */
    protected $deliverycostRepository;

    /**
     * @param array $item
     * @return void
     */
    public function addItemAction($item) {
        $this->cart->addItem($item);
        $nodeUri = $item['nodeUri'];
        //$this->addFlashMessage($this->translator->translateById('added', $sourceName = 'NodeTypes/Article', $packageKey = 'NeosRulez.Cart'));
        $this->addFlashMessage("Artikel hinzugefügt.");
        $this->redirectToUri($nodeUri);
    }

    /**
     * @param array $item
     * @return void
     */
    public function updateItemAction($item) {
        $this->cart->updateItem($item);
        $linkToCart = $this->settings['linkToCart'];
        $this->redirectToUri($linkToCart);
    }

    /**
     * @return void
     */
    public function miniCartAction() {
        $sum = $this->cart->miniCart();
        $linkToCart = $this->settings['linkToCart'];
        $this->view->assign('result', $sum);
        $this->view->assign('linkToCart', $linkToCart);
    }

    /**
     * @return void
     */
    public function cartAction() {
        $items = $this->cart->cart();

        $taxinclusive = $this->settings['taxinclusive'];
        $this->view->assign('taxinclusive', $taxinclusive);

        $paypal = $this->settings['payPal'];
        $this->view->assign('paypal', $paypal);

        $cartcount = count($items);
        if ($cartcount>0) {
            $sumExcl = FALSE;
            $sumExcl = floatval($sumExcl);

            $sumDelivery = FALSE;
            $sumDelivery = floatval($sumDelivery);

            $fullweight = FALSE;
            $fullweight = floatval($fullweight);

            foreach ($items as $dat) {
                $fullpriceExcl = floatval($dat["fullprice"]);
                $sumExcl += $fullpriceExcl;

                $fulldelivery = floatval($dat["fulldelivery"]);

                $fullweight += $fulldelivery;
            }

            $delivercost = '\NeosRulez\ShoppingCart\Domain\Model\Deliverycost';
            $querydelivercost = $this->persistenceManager->createQueryForType($delivercost);
            $deliverycosts = $querydelivercost->execute();

            while ($deliverycost = $deliverycosts->current()) {

                $minweight = floatval($deliverycost->getMinweight());
                $maxweight = floatval($deliverycost->getMaxweight());

                if($fullweight>=$minweight AND $fullweight<=$maxweight) {
                    $deliverc = floatval($deliverycost->getPrice());
                    $delivert = floatval($deliverycost->getTax());
                    break;
                } else {
                    if($fullweight>$minweight AND $fullweight>$maxweight) {
                        $deliverc = floatval($deliverycost->getPrice());
                        $delivert = floatval($deliverycost->getTax());
                    } else {
                        $deliverc = 0;
                        $delivert = 0;
                    }
                }
                $deliverycosts->next();
            }

            $sumDelivery = $deliverc;
            $sumDeliveryTax = $delivert;
            $this->view->assign('fulldelivery', $sumDelivery);
            $this->view->assign('fullweight', $fullweight);
            $this->view->assign('subtotal', $sumExcl);
        } else {
            $sumExcl = 0;
            $sumDelivery = 0;
            $sumDeliveryTax = 0;
        }

        $sumTax = FALSE;
        $sumTax = floatval($sumTax);
        foreach ($items as $datTax) {
            $tax = floatval($datTax["taxvalue"]);
            $sumTax += $tax;
        }

        $taxinclusive = $this->settings['taxinclusive'];

        if ($this->request->hasArgument('delivery')) {
            $delivery = $this->request->getArgument('delivery');
        } else {
            $delivery = $this->settings['standardDelivery'];
        }

        $this->view->assign('delivery', $delivery);

        $deliveries = '\NeosRulez\ShoppingCart\Domain\Model\Delivery';
        $query = $this->persistenceManager->createQueryForType($deliveries);
        $result = $query->matching($query->equals('Persistence_Object_Identifier', $delivery))->execute()->getFirst();
        $deliverycosts = $result->getCosts();

        $deliverycosts = $deliverycosts+$sumDelivery;
        $this->view->assign('deliverycosts', $deliverycosts);

        $sumExcl = $sumExcl+$deliverycosts;


        if($taxinclusive==true) {
            $this->view->assign('fullprice', $sumExcl);
        } else {
            $this->view->assign('fullprice', $sumExcl+$sumTax+$sumDeliveryTax);
        }

        $fulltax = $sumTax+$sumDeliveryTax;
        $this->view->assign('taxvalue', $fulltax);

        $this->view->assign('items', $items);

        $this->view->assign('countries', $this->countryRepository->findAll());
        $this->view->assign('deliveries', $this->deliveryRepository->findAll());

    }

    /**
     * @return void
     */
    public function deleteCartAction() {
        $this->cart->deleteCart();
        $linkToCart = $this->settings['linkToCart'];
        $this->redirectToUri($linkToCart);
    }

    /**
     * @param string $id
     * @return void
     */
    public function removeItemAction($id) {
        $this->cart->removeItem($id);
        $linkToCart = $this->settings['linkToCart'];
        $this->redirectToUri($linkToCart);
    }

    /**
     * @return void
     */
    public function checkoutAction() {

        $timestamp = time();
        $orderEmailAddress = $this->settings['orderEmailAddress'];
        $senderEmailAddress = $this->settings['senderEmailAddress'];
        $confirmationSubject = $this->settings['confirmationSubject'].' ('.$timestamp.')';
        $orderSubject = $this->settings['orderSubject'].' ('.$timestamp.')';
        $mailLogo = $this->settings['mailLogo'];
        $payPalReturnUri = $this->settings['payPalReturnUri'];
        $payPalHostedButtonId = $this->settings['payPalHostedButtonId'];
        $payPalBusiness = $this->settings['payPalBusiness'];

        /* Invoice */
        $company = $this->request->getArgument('company');
        $firstname = $this->request->getArgument('firstname');
        $lastname = $this->request->getArgument('lastname');
        $address = $this->request->getArgument('address');
        $zip = $this->request->getArgument('zip');
        $city = $this->request->getArgument('city');
        $country = $this->request->getArgument('country');
        $phone = $this->request->getArgument('phone');
        $invoiceemail = $this->request->getArgument('email');
        $invoiceaddress = $company.'<br />'.$firstname.' '.$lastname.'<br />'.$address.'<br />'.$zip.' '.$city.'<br />'.$country.'<br />'.$phone.'<br />'.$invoiceemail;

        $payment = $this->request->getArgument('payment');

        $delivery = $this->request->getArgument('delivery');
        $deliveries = '\NeosRulez\ShoppingCart\Domain\Model\Delivery';
        $query = $this->persistenceManager->createQueryForType($deliveries);
        $result = $query->matching($query->equals('Persistence_Object_Identifier', $delivery))->execute()->getFirst();
        $deliverycosts = $result->getCosts();
        $deliveryname = $result->getName();

        /* Delivery */
        if ($this->request->hasArgument('company1')) {
            $company1 = $this->request->getArgument('company1');
        } else {
            $company1 = $this->request->getArgument('company');
        }
        if ($this->request->hasArgument('firstname1')) {
            $firstname1 = $this->request->getArgument('firstname1');
        } else {
            $firstname1 = $this->request->getArgument('firstname');
        }
        if ($this->request->hasArgument('lastname1')) {
            $lastname1 = $this->request->getArgument('lastname1');
        } else {
            $lastname1 = $this->request->getArgument('lastname');
        }
        if ($this->request->hasArgument('address1')) {
            $address1 = $this->request->getArgument('address1');
        } else {
            $address1 = $this->request->getArgument('address');
        }
        if ($this->request->hasArgument('zip1')) {
            $zip1 = $this->request->getArgument('zip1');
        } else {
            $zip1 = $this->request->getArgument('zip');
        }
        if ($this->request->hasArgument('city1')) {
            $city1 = $this->request->getArgument('city1');
        } else {
            $city1 = $this->request->getArgument('city');
        }
        if ($this->request->hasArgument('country1')) {
            $country1 = $this->request->getArgument('country1');
        } else {
            $country1 = $this->request->getArgument('country');
        }

        $countrykeyclass = '\NeosRulez\ShoppingCart\Domain\Model\Country';
        $countrykeyquery = $this->persistenceManager->createQueryForType($countrykeyclass);
        $countrykeyresult = $countrykeyquery->matching($countrykeyquery->equals('country', $country))->execute()->getFirst();
        $countrykey = $countrykeyresult->getCountrykey();

        $deliveryaddress = $company1.'<br />'.$firstname1.' '.$lastname1.'<br />'.$address1.'<br />'.$zip1.' '.$city1.'<br />'.$country1;

        /* Prices */

        /* Confirmation */
        $cart = $this->cart->cart();
        $cartcount = count($cart);
        $mailoutput = "";

        $sumsubtotal = floatval($this->request->getArgument('subtotal'));
        $sumdeliverycosts = floatval($this->request->getArgument('deliverycosts'));
        $sumtaxvalue = floatval($this->request->getArgument('taxvalue'));
        $sumfullprice = floatval($this->request->getArgument('fullprice'));

        for ($i = 0; $i < $cartcount; $i++) {
            $innerarray = $cart[$i];
            $quantity = $innerarray['quantity'];
            $title = $innerarray['articleTitle'];
            $description = $innerarray['articleDescription'];
            $price = $innerarray['articlePrice'];
            $articlenumber = $innerarray['articleNumber'];
            $tax = $innerarray['tax'];
            $taxvalue = $innerarray['taxvalue'];
            $priceraw = $price;
            $subtotal = $priceraw - $taxvalue;

            $mailoutput .= '
           
            <table width="100%" border="0" cellspacing="0" cellpadding="10">
              <tbody>
                <tr>
                  <td width="150" height="30" align="left" valign="middle">'.$quantity.'</td>
                  <td align="left" valign="middle">'.$title.'</td>
                  <td width="150" align="right" valign="middle">'.money_format('%=*^-14#8.2i', $price).'</td>
                  <td width="150" align="right" valign="middle">'.money_format('%=*^-14#8.2i', $price*$quantity).'</td>
                </tr>
              </tbody>
            </table>';

        }


        $mail = '
        
        <style>
	body { font-family: Helvetica, Arial, Sans-Serif; background:#EBEBEB; padding:40px; }
</style>

<div style="width:900px; margin:0 auto;">
	<div style="float:left; width:860px; padding:20px; background:#FFFFFF;">

		<div style="float:left; width:100%;"">
			<div style="float:left; width:50%;">
				<h1>{title} ('.$timestamp.')</h1>
			</div>
			<div style="float:left; width:50%; text-align:right;">
				<img src="'.$mailLogo.'" alt="" />
			</div>
		</div>

		<div style="float:left; width:100%;"">
			<div style="float:left; width:50%;">
				<h3>Rechnungsadresse</h3>
				'.$invoiceaddress.'
			</div>

			<div style="float:left; width:50%;">
				<h3>Lieferadresse</h3>
				'.$deliveryaddress.'
			</div>
		</div>

		<div style="float:left; width:100%; margin-top:20px; margin-bottom:20px;">
			<h3>Versandoptionen &amp; Bezahlung</h3>
			'.$deliveryname.' ('.money_format('%=*^-14#8.2i',$deliverycosts).')<br />
			Zahlungsweise: '.$payment.' 
		</div>

		<div style="float:left; width:100%; margin-top:20px; margin-bottom:20px;">
			
			<table width="100%" border="0" cellspacing="0" cellpadding="10" style="background:#EBEBEB;">
              <tbody>
                <tr>
                  <td width="150" height="30" align="left" valign="middle" style="border-right:1px solid #FFFFFF;">Menge</td>
                  <td align="left" valign="middle" style="border-right:1px solid #FFFFFF;">Beschreibung</td>
                  <td width="150" align="right" valign="middle" style="border-right:1px solid #FFFFFF;">Preis</td>
                  <td width="150" align="right" valign="middle">Gesamtpreis</td>
                </tr>
              </tbody>
            </table>

            '.$mailoutput.'
            
            <table width="100%" border="0" cellspacing="0" cellpadding="10">
              <tbody>
                <tr>
                  <td width="150" height="30" align="left" valign="middle"></td>
                  <td align="left" valign="middle"></td>
                  <td width="150" align="right" valign="middle">Zwischensumme:<br />Versand &amp; Verpackung:<br />Mehrwertsteuer:<br />Gesamtsumme:</td>
                  <td width="150" align="right" valign="middle">'.money_format('%=*^-14#8.2i', $sumsubtotal).'<br />'.money_format('%=*^-14#8.2i', $sumdeliverycosts).'<br />'.money_format('%=*^-14#8.2i', $sumtaxvalue).'<br /><strong>'.money_format('%=*^-14#8.2i', $sumfullprice).'</strong></td>
                </tr>
              </tbody>
            </table>

		</div>

		<div style="float:left; width:100%; margin-top:20px; margin-bottom:20px;">
			-- <br />
			Dies ist ein automatisch generiertes Mail, bitte antworten Sie nicht darauf.
		</div>

	</div>
</div>
        ';

        $confirmationbody = str_replace("{title}", $confirmationSubject, $mail);
        $orderbody = str_replace("{title}", $orderSubject, $mail);

        $confirmation = new \Neos\SwiftMailer\Message();
        $confirmation->setFrom(array($senderEmailAddress))
            ->setTo(array($invoiceemail))
            ->setSubject($confirmationSubject)
            ->setBody($confirmationbody, 'text/html')
            ->send();

        $order = new \Neos\SwiftMailer\Message();
        $order->setFrom(array($senderEmailAddress))
            ->setTo(array($orderEmailAddress))
            ->setSubject($orderSubject)
            ->setBody($orderbody, 'text/html')
            ->send();

        if($payment=="1") {
            $this->view->assign('hosted_button_id', $payPalHostedButtonId);
            $this->view->assign('business', $payPalBusiness);
            $this->view->assign('firstname', $firstname);
            $this->view->assign('lastname', $lastname);
            $this->view->assign('address', $address);
            $this->view->assign('city', $city);
            $this->view->assign('zip', $zip);
            $this->view->assign('invoiceemail', $invoiceemail);
            $this->view->assign('country', $countrykey);
            $this->view->assign('timestamp', $timestamp);
            $this->view->assign('payPalReturnUri', $payPalReturnUri);
            $this->view->assign('sumfullprice', $sumfullprice);
            $this->cart->deleteCart();
        } else {
            $this->cart->deleteCart();
            $linkToCart = $this->settings['linkToCart'];
            $this->redirectToUri($linkToCart);
        }


    }

}
