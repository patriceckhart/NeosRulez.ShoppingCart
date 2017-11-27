<?php
namespace NeosRulez\ShoppingCart\Controller;

/*
 * This file is part of the NeosRulez.ShoppingCart package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

class CountryController extends ActionController
{

    /**
     * @Flow\Inject
     * @var \NeosRulez\ShoppingCart\Domain\Repository\CountryRepository
     */
    protected $countryRepository;

    /**
     * @return void
     */
    public function indexAction() {
        $this->view->assign('countries', $this->countryRepository->findAll());
    }

    /**
     * @return void
     */
    public function newAction() {

    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Country $newCountry
     * @return void
     */
    public function createAction($newCountry) {
        $this->countryRepository->add($newCountry);
        $this->redirect('index','country');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Country $country
     * @return void
     */
    public function editAction($country) {
        $this->view->assign('country', $country);
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Country $country
     * @return void
     */
    public function updateAction($country) {
        $this->countryRepository->update($country);
        $this->redirect('index','country');
    }

    /**
     * @param \NeosRulez\ShoppingCart\Domain\Model\Country $country
     * @return void
     */
    public function deleteAction($country) {
        $this->countryRepository->remove($country);
        $this->redirect('index','country');
    }

}
