<?php
namespace NeosRulez\ShoppingCart\Controller;

/*
 * This file is part of the NeosRulez.ShoppingCart package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

class DeliveryController extends ActionController
{

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\DeliveryRepository
     */
    protected $deliveryRepository;

    protected function initializeCreateAction() {
        $this->arguments['newDelivery']->getPropertyMappingConfiguration()->forProperty('costs')->setTypeConverterOption(
            'Neos\Flow\Property\TypeConverter\FloatConverter', 'locale', TRUE
        );
    }

    protected function initializeUpdateAction() {
        $this->arguments['delivery']->getPropertyMappingConfiguration()->forProperty('costs')->setTypeConverterOption(
            'Neos\Flow\Property\TypeConverter\FloatConverter', 'locale', TRUE
        );
    }

    /**
     * @return void
     */
    public function indexAction() {
        $this->view->assign('deliveries', $this->deliveryRepository->findAll());
    }

    /**
     * @return void
     */
    public function newAction() {

    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Delivery $newDelivery
     * @return void
     */
    public function createAction($newDelivery) {
        $this->deliveryRepository->add($newDelivery);
        $this->redirect('index','delivery');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Delivery $delivery
     * @return void
     */
    public function editAction($delivery) {
        $this->view->assign('delivery', $delivery);
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Delivery $delivery
     * @return void
     */
    public function updateAction($delivery) {
        $this->deliveryRepository->update($delivery);
        $this->redirect('index','delivery');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Delivery $delivery
     * @return void
     */
    public function deleteAction($delivery) {
        $this->deliveryRepository->remove($delivery);
        $this->redirect('index','delivery');
    }

}
