<?php

/**
 * VinariCore
 *
 * @link      https://github.com/TwoWholeWorms/VinariCore
 * @copyright Copyright (c) 2015 Vinari Ltd. (http://vinari.co.uk)
 * @license   BSD-3-Clause
 */

namespace VinariCore\Session\SaveHandler;

use Zend\Db\TableGateway\TableGateway;
use Zend\Session\SaveHandler\DbTableGateway as BaseDbTableGateway;

/**
 * DB Table Gateway session save handler
 */
class DbTableGateway extends BaseDbTableGateway
{

    /**
     * Destroy session — Override to check the existing session is in the DB, as HHVM errors otherwise
     *
     * @param  string $id
     * @return bool
     */
    public function destroy($id)
    {
        $rows = $this->tableGateway->select(array(
            $this->options->getIdColumn()   => $id,
            $this->options->getNameColumn() => $this->sessionName,
        ));

        if ($row = $rows->current()) {
            return parent::destroy($id);
        } else {
            return true;
        }
    }

}
