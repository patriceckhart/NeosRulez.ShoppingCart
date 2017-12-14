<?php
namespace NeosRulez\ShoppingCart\Domain\Model;

/*
 * This file is part of the NeosRulez.ShoppingCart package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class Deliverycost
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @var float
     */
    protected $minweight;

    /**
     * @return float
     */
    public function getMinweight()
    {
        return $this->minweight;
    }

    /**
     * @param float $minweight
     * @return void
     */
    public function setMinweight($minweight)
    {
        $this->minweight = $minweight;
    }

    /**
     * @var float
     */
    protected $maxweight;

    /**
     * @return float
     */
    public function getMaxweight()
    {
        return $this->maxweight;
    }

    /**
     * @param float $maxweight
     * @return void
     */
    public function setMaxweight($maxweight)
    {
        $this->maxweight = $maxweight;
    }

    /**
     * @var integer
     */
    protected $tax;

    /**
     * @return integer
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * @param integer $tax
     * @return void
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
    }

    /**
     * @var float
     */
    protected $price;

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return void
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }
    
}
