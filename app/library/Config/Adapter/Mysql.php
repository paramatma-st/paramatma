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

use \Phalcon\Config\Exception;

class Mysql extends \Phalcon\Config
{
    private $_logger;
    private $_table;
    private $_db_config;

    /**
     * Consturctor of class Mysql
     * 
     * @param \Phalcon\Config $db_config_ - \Phalcon\Config object reference with db related data.
     * @param \Logger $logger_ - reference of Logger instance.
     * @param String $table_ - reference of table name whith configuration data.
     * @throws Exception
     */
    public function __construct(&$db_config_, &$logger_, &$table_)
    {
        //-- check
        if (!isset($db_config_)) {
            throw new Exception("The parameter 'db_config_' is required");
        }

        if (!isset($table_)) {
            throw new Exception("You should provide a table name");
        }

        //-- code
        $this->_db_config = $db_config_;
        $this->_logger    = $logger_;
        $this->_table     = $table_;

        parent::__construct($this->get()->toArray());
    }

    /**
     * Gets configuration data from DB.
     * 
     * @return \Phalcon\Config object with configuration data.
     */
    public function get()
    {
        $method         = __METHOD__;
        $logger         = $this->_logger;
        //--
        $db_connection  = new \Phalcon\Db\Adapter\Pdo\Mysql($this->_db_config);
        $db_sel_request = 'SELECT path, value FROM ' . $this->_table;
        $db_sel_result  = $db_connection->fetchAll($db_sel_request,
                                                   \Phalcon\Db::FETCH_ASSOC);

        $traverse = function($inp_, $out_) use (&$logger, &$method) {
            if (empty($inp_)) {
                //$err = $method . '(): invalid array:[' . $inp_ . ']!';
                //$logger->error($err);
                return;
            }
            //--
            $pathToConfig = function($path_, $val_, $obj_) {
                $last  = &$obj_;
                $path_ = explode('/', $path_);
                //--
                foreach ($path_ as $p) {
                    if (empty($p)) {
                        continue;
                    }
                    if (!property_exists($last, $p)) {
                        $last->$p = new \Phalcon\Config();
                    }
                    $last = &$last->$p;
                }
                //--
                $last = $val_;
            };

            //--
            foreach ($inp_ as $row) {
                $pathToConfig($row['path'], $row['value'], $out_);
            }
        };

        $out_config = new \Phalcon\Config();
        $traverse($db_sel_result, $out_config);

        return $out_config;
    }

    /**
     * Stores new data from \Phalcon\Config object into DB. 
     * Old data was deleted before store new one.
     * 
     * @param \Phalcon\Config $config_
     */
    public function set($config_)
    {
        $db_connection = NULL;
        $method = __METHOD__;
        $logger = $this->_logger;
        $table  = $this->_table;

        try {
            $db_connection  = new \Phalcon\Db\Adapter\Pdo\Mysql($this->_db_config);
            $db_connection->begin();
            $db_del_request = 'DELETE FROM ' . $table;

            $db_result = $db_connection->execute($db_del_request);
            if (!$db_result) {
                if ($logger->errorFlag) {
                    $e = __METHOD__ . '() could not delete records res:[' . $db_result . '].';
                    $logger->error($e);
                    echo $e . '<br />';
                }
                return;
            }

            $traverse = function($config_, $path_) use (&$traverse, &$db_connection, &$table) {
                $db_ins_request = 'INSERT INTO ' . $table . ' (path, value) VALUES(?, ?)';
                //--
                foreach ($config_ as $key => $val) {
                    $npath = ($path_ !== '/' ? $path_ . '/' . $key : $path_ . $key);
                    if ($val instanceof \Phalcon\Config) {
                        $traverse($val, $npath);
                    } else {
                        $db_result = $db_connection->execute($db_ins_request,
                                                             array($npath, $val));
                    }
                }
            };

            $traverse($config_, '/');

            $db_connection->commit();
        } catch (\Phalcon\Exception $e) {
            if($logger->errorFlag){
                $logger->error($method . '(): ex:[' . $e->getMessage() . '].');
            }
            if (!empty($db_connection)) {
                if ($db_connection->isUnderTransaction()) {
                    $db_connection->rollback();
                }
            }
        }
    }
}
