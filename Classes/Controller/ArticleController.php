<?php
namespace NeosRulez\ShoppingCart\Controller;

/*
 * This file is part of the NeosRulez.ShoppingCart package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;

class ArticleController extends ActionController
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
     * @var array
     */
    protected $settings;

    /**
     * Inject the settings
     *
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings) {
        $this->settings = $settings;
    }


    /**
     * @return void
     */
    public function indexAction() {

        $this->view->assign('articleTitle',$this->request->getInternalArgument('__articleTitle'));
        $this->view->assign('articlePrice',$this->request->getInternalArgument('__articlePrice'));
        $this->view->assign('articleOldPrice',$this->request->getInternalArgument('__articleOldPrice'));
        $this->view->assign('articleNumber',$this->request->getInternalArgument('__articleNumber'));
        $this->view->assign('tax',$this->request->getInternalArgument('__tax'));
        $stock = $this->request->getInternalArgument('__stock');
        $this->view->assign('stock',(int)$stock);
        $this->view->assign('image',$this->request->getInternalArgument('__image'));
        $this->view->assign('assets',$this->request->getInternalArgument('__assets'));
        $this->view->assign('delivery',$this->request->getInternalArgument('__delivery'));
        $this->view->assign('taxinclusive',$this->request->getInternalArgument('__taxinclusive'));

        $linktoCart = $this->settings['linkToCart'];
        $this->view->assign('linkToCart',$linktoCart);

        // var_dump($this->settings['linkToCart']);

        $options = $this->request->getInternalArgument('__options');

        $this->view->assign('nodeuri',(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
        if($options) {
            $optionArray = array();
            foreach($options as $option) {
                $optionName = $this->optionRepository->findByIdentifier($option);
                $optionName = $optionName->getName();
                $optionValueArray = array();
                $optionvalues = $this->optionvalueRepository->findByIdentifier($option);
                foreach($optionvalues as $optionvalue) {
                    $optionvalueName = $optionvalue->getLabel();
                    $optionvalueValue = $optionvalue->getValue();
                    array_push($optionValueArray, array('label' => $optionvalueName, 'value' => $optionvalueValue));
                }
                array_push($optionArray, array('optionname' => $optionName, 'optionvalues' => $optionValueArray));
            }
            $this->view->assign('optionarray',$optionArray);
        }

    }

    /**
     * @return void
     */
    public function singleIndexAction() {
        $this->indexAction();
    }

}
