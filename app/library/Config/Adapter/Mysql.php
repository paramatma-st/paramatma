<?php
/**
 * Paramatma (http://paramatma.io)
 *
 * @link      http://github.com/paramatma-io/paramatma for the canonical source repository
 * @copyright Copyright (c) 2015 Paramatma team
 * @license   http://opensource.org/licenses/MIT MIT License
 * @package   Paramatma
 */

namespace Paramatma\Config\Adapter;

use Phalcon\Config;
use Phalcon\Config\Exception;

/**
 * Phalcon\Config\Adapter\Mysql
 * Reads config from MySql DB table and convert it to Phalcon\Config objects.
 */
class Mysql extends Config implements \ArrayAccess
{

    /**
     * Class constructor.
     *
     * @param  \Phalcon\Db\Adapter\Mysql $db
     * @throws \Phalcon\Config\Exception
     */
    public function __construct($db, $table)
    {
        if (!isset($db)) {
            throw new Exception("The parameter 'db' is required");
        }

        if (!isset($table)) {
            throw new Exception("You should provide a table name");
        }

        parent::__construct($result);
    }
}
