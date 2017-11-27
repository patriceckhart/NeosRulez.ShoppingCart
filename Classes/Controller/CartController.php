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

            //$priceraw = str_replace(',', '.', $price);
            //$subtotal = $priceraw - $taxvalue;

            $priceraw = $price;
            $subtotal = $priceraw - $taxvalue;

            $mailoutput .= '
            <tr>
		        <td>'.$quantity.'</td>
                <td>'.$title.'</td>
                <td>'.$price.'</td>
                <td>'.$price*$quantity.'</td>
            </tr>';

        }


$mail = '

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
    <tr>
      <td align="center" valign="top"><table width="900" border="0" cellspacing="0" cellpadding="0">
        <tbody>
          <tr>
            <td><table width="900" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
					<td width="50%" height="100"><h2>Bestellung</h2></td>
                  <td align="right" valign="middle">Logo</td>
                </tr>
                <tr>
                  <td height="30" align="left" valign="middle"><strong>Rechnungsadresse</strong></td>
                  <td align="left" valign="middle"><strong>Lieferadresse</strong></td>
                </tr>
                <tr>
                  <td align="left" valign="top">Rechnungsadresse</td>
                  <td align="left" valign="top">Rechnungsadresse</td>
                </tr>
                <tr>
                  <td height="20">&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
                <tr>
                  <td height="30" align="left" valign="middle"><strong>Versandart:</strong> Versandart</td>
                  <td><strong>Lieferkosten:</strong> Lieferkosten</td>
                </tr>
                <tr>
                  <td height="50">&nbsp;</td>
                  <td>&nbsp;</td>
                </tr>
              </tbody>
            </table></td>
          </tr>
          <tr>
            <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
                  <td width="150" height="30" align="left" valign="middle"><strong>Menge</strong></td>
                  <td align="left" valign="middle"><strong>Beschreibung</strong></td>
                  <td width="150" align="right" valign="middle"><strong>Einzelpreis</strong></td>
                  <td width="150" align="right" valign="middle"><strong>Preis</strong></td>
                </tr>
              </tbody>
            </table></td>
          </tr>
          <tr>
            <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
                  <td width="150" height="30" align="left" valign="middle">5</td>
                  <td align="left" valign="middle">iPhone</td>
                  <td width="150" align="right" valign="middle">100</td>
                  <td width="150" align="right" valign="middle">500</td>
                </tr>
              </tbody>
            </table>
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                  <tr>
                    <td width="150" height="30" align="left" valign="middle">5</td>
                    <td align="left" valign="middle">iPhone</td>
                    <td width="150" align="right" valign="middle">100</td>
                    <td width="150" align="right" valign="middle">500</td>
                  </tr>
                </tbody>
              </table>
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                  <tr>
                    <td width="150" height="30" align="left" valign="middle">5</td>
                    <td align="left" valign="middle">iPhone</td>
                    <td width="150" align="right" valign="middle">100</td>
                    <td width="150" align="right" valign="middle">500</td>
                  </tr>
                </tbody>
              </table>
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                  <tr>
                    <td width="150" height="30" align="left" valign="middle">5</td>
                    <td align="left" valign="middle">iPhone</td>
                    <td width="150" align="right" valign="middle">100</td>
                    <td width="150" align="right" valign="middle">500</td>
                  </tr>
                </tbody>
              </table>
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                  <tr>
                    <td width="150" height="30" align="left" valign="middle">5</td>
                    <td align="left" valign="middle">iPhone</td>
                    <td width="150" align="right" valign="middle">100</td>
                    <td width="150" align="right" valign="middle">500</td>
                  </tr>
                </tbody>
              </table>
              <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                  <tr>
                    <td width="150" height="30" align="left" valign="middle">5</td>
                    <td align="left" valign="middle">iPhone</td>
                    <td width="150" align="right" valign="middle">100</td>
                    <td width="150" align="right" valign="middle">500</td>
                  </tr>
                </tbody>
              </table></td>
          </tr>
          <tr>
            <td><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
                  <td height="200">Copyright</td>
                </tr>
              </tbody>
            </table></td>
          </tr>
        </tbody>
      </table></td>
    </tr>
  </tbody>
</table>

';


        $mailbody = '
        <table width="900" border="0" cellspacing="0" cellpadding="0">
        <tr>
		<td>Menge</td>
      <td>Beschreibung</td>
      <td>Einzelpreis</td>
      <td>Preis</td>
    </tr>
            <tbody>
                <tr>
                    <td>'.$mailoutput.'</td>
                </tr>
            </tbody>
        </table>
        ';

        $email = "me@patric.at";

        $confirmationbody = '
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody>
                <tr>
                    <td align="center" valign="top">'.$mailbody.'</td>
                </tr>
            </tbody>
        </table>
        ';

        $orderbody = 'Bestellung: <br />'.$mailbody;


        $confirmation = new \Neos\SwiftMailer\Message();
        $confirmation->setFrom(array($senderEmailAddress))
            ->setTo(array($email))
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
        $this->redirectToUri($linkToCart);

    }

}
