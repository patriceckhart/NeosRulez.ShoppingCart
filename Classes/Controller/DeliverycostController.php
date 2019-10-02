<?php
namespace NeosRulez\ShoppingCart\Controller;

/*
 * This file is part of the NeosRulez.ShoppingCart package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

class DeliverycostController extends ActionController
{

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\DeliverycostRepository
     */
    protected $deliverycostRepository;

    protected function initializeCreateAction() {
        $this->arguments['newDeliverycost']->getPropertyMappingConfiguration()->forProperty('price')->setTypeConverterOption(
            'Neos\Flow\Property\TypeConverter\FloatConverter', 'locale', TRUE
        );
        $this->arguments['newDeliverycost']->getPropertyMappingConfiguration()->forProperty('minweight')->setTypeConverterOption(
            'Neos\Flow\Property\TypeConverter\FloatConverter', 'locale', TRUE
        );
        $this->arguments['newDeliverycost']->getPropertyMappingConfiguration()->forProperty('maxweight')->setTypeConverterOption(
            'Neos\Flow\Property\TypeConverter\FloatConverter', 'locale', TRUE
        );
    }

    protected function initializeUpdateAction() {
        $this->arguments['deliverycost']->getPropertyMappingConfiguration()->forProperty('price')->setTypeConverterOption(
            'Neos\Flow\Property\TypeConverter\FloatConverter', 'locale', TRUE
        );
        $this->arguments['deliverycost']->getPropertyMappingConfiguration()->forProperty('minweight')->setTypeConverterOption(
            'Neos\Flow\Property\TypeConverter\FloatConverter', 'locale', TRUE
        );
        $this->arguments['deliverycost']->getPropertyMappingConfiguration()->forProperty('maxweight')->setTypeConverterOption(
            'Neos\Flow\Property\TypeConverter\FloatConverter', 'locale', TRUE
        );
    }

    /**
     * @return void
     */
    public function indexAction() {
        $delivercost = '\NeosRulez\ShoppingCart\Domain\Model\Deliverycost';
        $querydelivercost = $this->persistenceManager->createQueryForType($delivercost);
        $deliverycosts = $querydelivercost->setOrderings(array('price' => \Neos\Flow\Persistence\QueryInterface::ORDER_ASCENDING))->execute();
        $this->view->assign('deliverycosts', $deliverycosts);
    }

    /**
     * @return void
     */
    public function newAction() {

    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Deliverycost $newDeliverycost
     * @return void
     */
    public function createAction($newDeliverycost) {
        $this->deliverycostRepository->add($newDeliverycost);
        $this->redirect('index','deliverycost');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Deliverycost $deliverycost
     * @return void
     */
    public function editAction($deliverycost) {
        $this->view->assign('deliverycost', $deliverycost);
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Deliverycost $deliverycost
     * @return void
     */
    public function updateAction($deliverycost) {
        $this->deliverycostRepository->update($deliverycost);
        $this->redirect('index','deliverycost');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Deliverycost $deliverycost
     * @return void
     */
    public function deleteAction($deliverycost) {
        $this->deliverycostRepository->remove($deliverycost);
        $this->redirect('index','deliverycost');
    }

}
