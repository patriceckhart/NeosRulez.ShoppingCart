<?php
namespace NeosRulez\ShoppingCart\Controller;

/*
 * This file is part of the NeosCommerce.Cart package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

class OptionController extends ActionController
{

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\OptionRepository
     */
    protected $optionRepository;

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\OptionvalueRepository
     */
    protected $optionvalueRepository;


    /**
     * @return void
     */
    public function indexAction() {
        $this->view->assign('options', $this->optionRepository->findAll());
        $this->view->assign('optionvalues', $this->optionvalueRepository->findAll());
    }

    /**
     * @return void
     */
    public function newAction() {

    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Option $option
     * @return void
     */
    public function newvalueAction($option) {
        $this->view->assign('option', $option);
        $this->view->assign('optionname', $option->getName());
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Optionvalue $newOptionvalue
     * @return void
     */
    public function createoptionvalueAction($newOptionvalue) {
        $this->optionvalueRepository->add($newOptionvalue);
        $this->redirect('index','option');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Option $newOption
     * @return void
     */
    public function createAction($newOption) {
        $this->optionRepository->add($newOption);
        $this->redirect('index','option');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Optionvalue $optionvalue
     * @return void
     */
    public function editoptionvalueAction($optionvalue) {
        $this->view->assign('optionvalue', $optionvalue);
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Option $option
     * @return void
     */
    public function editAction($option) {
        $this->view->assign('option', $option);
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Optionvalue $optionvalue
     * @return void
     */
    public function updateoptionvalueAction($optionvalue) {
        $this->optionvalueRepository->update($optionvalue);
        $this->redirect('index','option');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Option $option
     * @return void
     */
    public function updateAction($option) {
        $this->optionRepository->update($option);
        $this->redirect('index','option');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Optionvalue $optionvalue
     * @return void
     */
    public function deleteoptionvalueAction($optionvalue) {
        $this->optionvalueRepository->remove($optionvalue);
        $this->redirect('index','option');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Option $option
     * @return void
     */
    public function deleteAction($option) {
        $this->optionRepository->remove($option);
        $this->redirect('index','option');
    }

}
