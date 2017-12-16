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
class Country
{

    /**
     * @var string
     */
    protected $country;

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return void
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @var string
     */
    protected $countrykey;

    /**
     * @return string
     */
    public function getCountrykey()
    {
        return $this->countrykey;
    }

    /**
     * @param string $countrykey
     * @return void
     */
    public function setCountrykey($countrykey)
    {
        $this->countrykey = $countrykey;
    }

}
