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
class Option
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
     * @var string
     */
    protected $fename;

    /**
     * @return string
     */
    public function getFename()
    {
        return $this->fename;
    }

    /**
     * @param string $fename
     * @return void
     */
    public function setFename($fename)
    {
        $this->fename = $fename;
    }

    

}
