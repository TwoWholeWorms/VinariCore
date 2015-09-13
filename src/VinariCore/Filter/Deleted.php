<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore\Filter;

use Doctrine\ORM\Query\Filter\SQLFilter;
use Doctrine\ORM\Mapping\ClassMetadata;
use VinariCore\Entity\SoftDeleteInterface;


class Deleted extends SQLFilter
{

    /**
     * Returns an SQL fragment to insert into a query to allow for soft-deleted entites
     *
     * @return string The SQL condition to insert into the query, or an empty string if it's not required
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {

        // If the entity doesn't have the soft-delete interface, return an empty string
        if (!in_array("Vinari\\Entity\\SoftDeleteInterface", $targetEntity->reflClass->getInterfaceNames())) {
            return "";
        }

        return $targetTableAlias . '.is_deleted = ' . SoftDeleteInterface::STATUS_NOT_DELETED;

    }

}
