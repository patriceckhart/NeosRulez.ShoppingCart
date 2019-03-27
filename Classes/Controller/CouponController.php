<?php
namespace NeosRulez\ShoppingCart\Controller;

/*
 * This file is part of the NeosRulez.ShoppingCart package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

class CouponController extends ActionController
{

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\CouponRepository
     */
    protected $couponRepository;

    protected function initializeCreateAction() {
        $this->arguments['newCoupon']->getPropertyMappingConfiguration()->forProperty('couponvalue')->setTypeConverterOption(
            'Neos\Flow\Property\TypeConverter\FloatConverter', 'locale', TRUE
        );
    }

    protected function initializeUpdateAction() {
        $this->arguments['coupon']->getPropertyMappingConfiguration()->forProperty('couponvalue')->setTypeConverterOption(
            'Neos\Flow\Property\TypeConverter\FloatConverter', 'locale', TRUE
        );
    }

    /**
     * @return void
     */
    public function indexAction() {
        $this->view->assign('coupons', $this->couponRepository->findAll());
    }

    /**
     * @return void
     */
    public function newAction() {

    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Coupon $newCoupon
     * @return void
     */
    public function createAction($newCoupon) {
        $unique = $newCoupon->getCoupontype();
        if(!$unique) {
            $newCoupon->setCoupontype(0);
        }
        $this->couponRepository->add($newCoupon);
        $this->redirect('index','coupon');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Coupon $coupon
     * @return void
     */
    public function editAction($coupon) {
        $this->view->assign('coupon', $coupon);
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Coupon $coupon
     * @return void
     */
    public function updateAction($coupon) {
        $unique = $coupon->getCoupontype();
        if(!$unique) {
            $coupon->setCoupontype(0);
        }
        $this->couponRepository->update($coupon);
        $this->redirect('index','coupon');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Coupon $coupon
     * @return void
     */
    public function toggleAction($coupon) {
        $enable = $this->request->getArgument('enable');
        if($enable==1) {
            $coupon->setValidity(1);
        } else {
            $coupon->setValidity(0);
        }
        $this->couponRepository->update($coupon);
        $this->persistenceManager->persistAll();
        $this->redirect('index','coupon');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Coupon $coupon
     * @return void
     */
    public function deleteAction($coupon) {
        $this->couponRepository->remove($coupon);
        $this->redirect('index','coupon');
    }


}
