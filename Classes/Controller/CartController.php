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

        $cartcount = count($items);
        if ($cartcount>0) {
            $sumExcl = FALSE;
            $sumExcl = floatval($sumExcl);
            foreach ($items as $dat) {
                $fullpriceExcl = floatval($dat["fullprice"]);
                $sumExcl += $fullpriceExcl;
            }
            $this->view->assign('subtotal', $sumExcl);
        } else {
            $sumExcl = 0;
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
        $this->view->assign('deliverycosts', $deliverycosts);

        $sumExcl = $sumExcl+$deliverycosts;


        if($taxinclusive==true) {
            $this->view->assign('fullprice', $sumExcl);
        } else {
            $this->view->assign('fullprice', $sumExcl);
        }

        $this->view->assign('taxvalue', $sumTax);

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

        $orderEmailAddress = $this->settings['orderEmailAddress'];
        $senderEmailAddress = $this->settings['senderEmailAddress'];
        $confirmationSubject = $this->settings['confirmationSubject'];
        $orderSubject = $this->settings['orderSubject'];
        $paypal = $this->settings['paypalIntegration'];

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
        $invoiceaddress = $company.'<br />'.$firstname.'<br />'.$lastname.'<br />'.$address.'<br />'.$zip.'<br />'.$city.'<br />'.$country.'<br />'.$phone.'<br />'.$invoiceemail;

        /* Delivery */
        $company1 = $this->request->getArgument('company1');
        $firstname1 = $this->request->getArgument('firstname1');
        $lastname1 = $this->request->getArgument('lastname1');
        $address1 = $this->request->getArgument('address1');
        $zip1 = $this->request->getArgument('zip1');
        $city1 = $this->request->getArgument('city1');
        $country1 = $this->request->getArgument('country1');
        $deliveryaddress = $company1.'<br />'.$firstname1.'<br />'.$lastname1.'<br />'.$address1.'<br />'.$zip1.'<br />'.$city1.'<br />'.$country1;

        /* Prices */

        /* Confirmation */
        $cart = $this->cart->cart();
        $cartcount = count($cart);
        $mailoutput = "";

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
				<h1>{title}</h1>
			</div>
			<div style="float:left; width:50%; text-align:right;">
				Logo
			</div>
		</div>

		<div style="float:left; width:100%;"">
			<div style="float:left; width:50%;">
				<h3>Rechnungsadresse</h3>
				Lorem ipsum
			</div>

			<div style="float:left; width:50%;">
				<h3>Lieferadresse</h3>
				Lorem ipsum
			</div>
		</div>

		<div style="float:left; width:100%; margin-top:20px; margin-bottom:20px;">
			<h3>Versandoptionen</h3>
			Lorem ipsum
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

        $this->cart->deleteCart();
        $linkToCart = $this->settings['linkToCart'];

        if ($paypal==true) {

            echo '
<form name="payform" action="https://www.paypal.com/cgi-bin/webscr" method="post">

	<input type="hidden" name="cmd" value="_xclick">
	<input type="hidden" name="hosted_button_id" value="LTRDGNR38ZH6A">
	<input type="hidden" name="business" value="payments@landmarkt.at">  
	
	<input type="hidden" name="tax" value="0">
	<input type="hidden" name="quantity" value="1">
	<input type="hidden" name="no_note" value="1">
	<input type="hidden" name="address_override" value="1">
	<input type="hidden" name="first_name" value="'.$firstname.'">  
	<input type="hidden" name="last_name" value="'.$lastname.'">  
	<input type="hidden" name="address1" value="'.$address.'">  
	<input type="hidden" name="city" value="'.$city.'">  
	<input type="hidden" name="state" value="-">  
	<input type="hidden" name="zip" value="'.$zip.'">  
	<input type="hidden" name="email" value="'.$invoiceemail.'"> 
	<input type="hidden" name="country" value="'.$country.'">
	
	<input type="hidden" name="item_name" value="-">
	<input type="hidden" name="item_number" value="-">
	
	<input type="hidden" name="return" value="http://www.eggnog.at/bestellen/erfolgreich.html">
	<input type="hidden" name="currency_code" value="EUR">
	
	<input type="hidden" name="amount" id="amount" value="-">
		
		<div style="text-align:center; margin-top:200px; font-family:Arial;">
<strong>Einen kurzen Augenblick. Sie werden zur Zahlung weitergeleitet.</strong><br /><br />
Sollte Ihr Browser die automatische Weiterleitung nicht unterstützen, klicken Sie bitte auf die folgende Schaltfläche<br /><br />
<input type="image" name="submit" border="0" src="https://www.paypal.com/de_DE/i/btn/btn_paynow_LG.gif" alt="PayPal - The safer, easier way to pay online">
</div>
</form>
';

        } else {
            $this->redirectToUri($linkToCart);
        }


    }

}
