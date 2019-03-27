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
class Coupon
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
    protected $couponvalue;

    /**
     * @return float
     */
    public function getCouponvalue()
    {
        return $this->couponvalue;
    }

    /**
     * @param float $couponvalue
     * @return void
     */
    public function setCouponvalue($couponvalue)
    {
        $this->couponvalue = $couponvalue;
    }

    /**
     * @var string
     */
    protected $couponcode;

    /**
     * @return string
     */
    public function getCouponcode()
    {
        return $this->couponcode;
    }

    /**
     * @param string $couponcode
     * @return void
     */
    public function setCouponcode($couponcode)
    {
        $this->couponcode = $couponcode;
    }

    /**
     * @var integer
     */
    protected $validity;

    /**
     * @return integer
     */
    public function getValidity()
    {
        return $this->validity;
    }

    /**
     * @param integer $validity
     * @return void
     */
    public function setValidity($validity)
    {
        $this->validity = $validity;
    }

    /**
     * @var integer
     */
    protected $coupontype;

    /**
     * @return integer
     */
    public function getCoupontype()
    {
        return $this->coupontype;
    }

    /**
     * @param integer $coupontype
     * @return void
     */
    public function setCoupontype($coupontype)
    {
        $this->coupontype = $coupontype;
    }

    /**
     * @var integer
     */
    protected $couponcount;

    /**
     * @return integer
     */
    public function getCouponcount()
    {
        return $this->couponcount;
    }

    /**
     * @param integer $couponcount
     * @return void
     */
    public function setCouponcount($couponcount)
    {
        $this->couponcount = $couponcount;
    }

}
