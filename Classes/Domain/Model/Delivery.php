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
class Delivery
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
    protected $costs;

    /**
     * @return float
     */
    public function getCosts()
    {
        return $this->costs;
    }

    /**
     * @param float $costs
     * @return void
     */
    public function setCosts($costs)
    {
        $this->costs = $costs;
    }

}
