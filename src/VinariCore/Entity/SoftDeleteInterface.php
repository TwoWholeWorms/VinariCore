<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore\Entity;

/**
 * Interface to support soft delete
 */
interface SoftDeleteInterface
{

    const STATUS_NOT_DELETED = 0;
    const STATUS_DELETED = 1;

    /**
     * @param int $isDeleted
     * @return $this
     */
    public function setIsDeleted($isDeleted);

    /**
     * @return $this
     */
    public function getIsDeleted();

}
