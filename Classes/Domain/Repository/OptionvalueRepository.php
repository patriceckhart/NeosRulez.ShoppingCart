<?php
namespace NeosRulez\ShoppingCart\Domain\Repository;

/*
 * This file is part of the NeosRulez.ShoppingCart package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class OptionvalueRepository extends Repository
{

    public function findByIdentifier($identifier) {
        $class = '\NeosRulez\ShoppingCart\Domain\Model\Optionvalue';
        $query = $this->persistenceManager->createQueryForType($class);
        $result = $query->matching($query->equals('option', $identifier))->execute();
        return $result;
    }

}
