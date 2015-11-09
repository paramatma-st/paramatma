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
    const SQL_INS = 'INSERT INTO pa_config (path, value) VALUES (?, ?)';
    const SQL_SEL = 'SELECT path, value FROM pa_config';
    const SQL_DEL = 'DELETE FROM pa_config';

    //--
    private $_logger;
    private $_db;

    /**
     * Consturctor of class Mysql
     * 
     * @param \Phalcon\Config $db_config_ - \Phalcon\Config object reference with db related data.
     * @param \Paramatma\Logger $logger_ - reference of Logger instance.
     * @throws Exception
     */
    public function __construct(&$db_, &$logger_)
    {
        //-- check
        if (!($db_ instanceof \Phalcon\Db\Adapter\Pdo)) {
            throw new Exception('The parameter \'db_\' is invalid!');
        }

        if (!($logger_ instanceof \Paramatma\Logger )) {
            throw new Exception('The parameter \'logger_\' is invalid!');
        }

        //-- code
        $this->_db     = $db_;
        $this->_logger = $logger_;

        parent::__construct();
        $this->_fetch();
    }

    /**
     * Gets configuration data from Database.
     */
    private function _fetch()
    {
        $method   = __METHOD__;
        $logger   = $this->_logger;
        //--
        $dbResult = $this->_db->fetchAll(self::SQL_SEL, \Phalcon\Db::FETCH_ASSOC);

        $traverse = function($inp_, $out_) use (&$logger, &$method) {
            if (empty($inp_)) {
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

        $traverse($dbResult, $this);
    }

    /**
     * Stores confguration data into Database.
     */
    public function save()
    {
        $method   = __METHOD__;
        $logger   = $this->_logger;
        $dbConn   = $this->_db;
        $dbInsert = self::SQL_INS;

        //--
        try {
            do {
                $dbConn->begin();
                $dbResult = $dbConn->execute(self::SQL_DEL);
                if (!$dbResult) {
                    if ($logger->errorFlag) {
                        $e = $method . '(): could not delete records res:[' . $dbResult . '].';
                        $logger->error($e);
                    }
                    break;
                }

                $traverse = function($config_, $path_) use (&$traverse, &$dbConn, &$dbInsert, &$logger, &$method) {
                    foreach ($config_ as $key => $val) {
                        $npath = ($path_ !== '/' ? $path_ . '/' . $key : $path_ . $key);
                        if ($val instanceof \Phalcon\Config) {
                            $traverse($val, $npath);
                        } else if (is_string($val)) {
                            $dbResult = $dbConn->execute($dbInsert,
                                                         array($npath, $val));
                            if (!$dbResult) {
                                if ($logger->errorFlag) {
                                    $e = $method . '() could not insert records res:[' . $dbResult . '].';
                                    $logger->error($e);
                                }
                                return;
                            }
                        }
                    }
                };

                $traverse($this, '/');

                $dbConn->commit();
            } while (false);
        } catch (\Phalcon\Exception $e) {
            if ($logger->errorFlag) {
                $logger->error($method . '(): ex:[' . $e->getMessage() . '].');
            }
            if (!empty($dbConn)) {
                if ($dbConn->isUnderTransaction()) {
                    $dbConn->rollback();
                }
            }
        }
    }

    /**
     * Merge configuration parameters from $config_ to this object.
     * 
     * @param \Phalcon\Config $config_
     */
    public function merge(&$config_)
    {
        $logger = $this->_logger;

        //--
        if (empty($config_)) {
            if ($logger->errorFlag) {
                $e = __METHOD__ . '(): empty config passed.';
                $logger->error($e);
            }
            return;
        }

        foreach ($config_ as $key => $val) {
            $this[$key] = $val;
        }
    }
}
