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
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\CouponRepository
     */
    protected $couponRepository;

    /**
     * @Flow\Inject
     * @var \Neos\Flow\I18n\Service
     */
    protected $i18nService;

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
     * @return string
     */
    public function validateCouponAction() {
        $code = $this->request->getArgument('couponcode');
        $cartfullprice = $this->request->getArgument('cartfullprice');

        $coupons = '\NeosRulez\ShoppingCart\Domain\Model\Coupon';
        $couponsQuery = $this->persistenceManager->createQueryForType($coupons);
        $result = $couponsQuery->matching($couponsQuery->equals('couponcode', $code))->execute()->getFirst();

        if($result) {
            $validity = $result->getValidity();
            if($validity==1) {
                $cval = $result->getCouponvalue();
                $couponValue = $result->getCouponvalue().','.$this->persistenceManager->getIdentifierByObject($result);
            } else {
                $couponValue = 0;
            }
        } else {
            $validity = 0;
            $couponValue = 0;
        }

        if($cartfullprice<$cval) {
            $validity = 0;
            $couponValue = 0;
        }

        print $couponValue;

        exit();
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

        $coupons = $this->settings['coupons'];
        $this->view->assign('coupons', $coupons);

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
        $mailAdditional = $this->settings['mailAdditional'];

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

        $couponValue = $this->request->getArgument('couponvalue');
        if($couponValue>0) {
            $couponValue = $couponValue;
        } else {
            $couponValue = 0;
        }

        #setlocale(LC_MONETARY, 'de_DE');
        #echo money_format('€ %!n', 1.620.56);


        /* Confirmation */
        $cart = $this->cart->cart();
        $cartcount = count($cart);

        $sumsubtotal = floatval($this->request->getArgument('subtotal'));
        $sumdeliverycosts = floatval($this->request->getArgument('deliverycosts'));
        $sumtaxvalue = floatval($this->request->getArgument('taxvalue'));
        $sumfullprice = floatval($this->request->getArgument('fullprice'));

        $articleTemplate = file_get_contents($this->settings['mailArticleTemplate']);
        $articles = "";

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

            $article = str_replace("{quantity}",$quantity,$articleTemplate);
            $article = str_replace("{title}",$title,$article);
            $article = str_replace("{description}",$description,$article);
            $article = str_replace("{articlenumber}",$articlenumber,$article);
            $article = str_replace("{price}",number_format($price, 2, ',', '.'),$article);
            $article = str_replace("{pricequantity}",number_format($price*$quantity, 2, ',', '.'),$article);

            $articles .= $article;

        }

        $invoiceTemplate = file_get_contents($this->settings['mailBodyTemplate']);

        $body = str_replace("{logo}",$mailLogo.$this->i18nService->getConfiguration()->getCurrentLocale(),$invoiceTemplate);
        $body = str_replace("{articles}",$articles,$body);

        $body = str_replace("{company}",$company,$body);
        $body = str_replace("{firstname}",$firstname,$body);
        $body = str_replace("{lastname}",$lastname,$body);
        $body = str_replace("{address}",$address,$body);
        $body = str_replace("{zip}",$zip,$body);
        $body = str_replace("{city}",$city,$body);
        $body = str_replace("{country}",$country,$body);

        $body = str_replace("{company1}",$company1,$body);
        $body = str_replace("{firstname1}",$firstname1,$body);
        $body = str_replace("{lastname1}",$lastname1,$body);
        $body = str_replace("{address1}",$address1,$body);
        $body = str_replace("{zip1}",$zip1,$body);
        $body = str_replace("{city1}",$city1,$body);
        $body = str_replace("{country1}",$country1,$body);

        $body = str_replace("{delivery}",$deliveryname.' ('.number_format($deliverycosts, 2, ',', '.').')',$body);
        $body = str_replace("{payment}",$payment,$body);

        $body = str_replace("{sumsubtotal}",number_format($sumsubtotal, 2, ',', '.'),$body);
        $body = str_replace("{sumdeliverycosts}",number_format($sumdeliverycosts, 2, ',', '.'),$body);
        $body = str_replace("{sumtaxvalue}",number_format($sumtaxvalue, 2, ',', '.'),$body);
        $body = str_replace("{sumfullprice}",number_format($sumfullprice, 2, ',', '.'),$body);

        $body = str_replace("{couponvalue}",number_format($couponValue, 2, ',', '.'),$body);

        $body = str_replace("{maildditional}",$mailAdditional,$body);

        if($couponValue>0) {
            $body = str_replace("<!-- Coupon","",$body);
            $body = str_replace("Coupon -->","",$body);
        }

        $confirmationbody = str_replace("{title}", $confirmationSubject, $body);
        $orderbody = str_replace("{title}", $orderSubject, $body);

        /*$confirmation = new \Neos\SwiftMailer\Message();
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
            ->send();*/

        $file = '/var/www/html/neos/Web/order.html';
        $content = serialize($confirmationbody);
        file_put_contents($file, $content);
        $content = unserialize(file_get_contents($file));

        $file1 = '/var/www/html/neos/Web/conf.html';
        $content1 = serialize($orderbody);
        file_put_contents($file1, $content1);
        $content1 = unserialize(file_get_contents($file1));

        //$coupon = $this->request->getArgument('coupon');
        $couponIdentifier = $this->request->getArgument('couponidentifier');

        $coupons = '\NeosRulez\ShoppingCart\Domain\Model\Coupon';
        $couponsQuery = $this->persistenceManager->createQueryForType($coupons);
        $couponresult = $couponsQuery->matching($couponsQuery->equals('Persistence_Object_Identifier', $couponIdentifier))->execute()->getFirst();

        if($couponresult) {
            $coupontype = $couponresult->getCoupontype();
            $couponcount = $couponresult->getCouponcount();
        } else {
            $coupontype = 0;
        }

        if($coupontype==1) {
            $couponresult->setValidity(0);
            $couponresult->setCouponcount($couponcount+1);
            $this->couponRepository->update($couponresult);
        }

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
            #$this->cart->deleteCart();
        } else {
            #$this->cart->deleteCart();
            $linkToCart = $this->settings['linkToCart'];
            $this->redirectToUri($linkToCart);
        }

    }

}
