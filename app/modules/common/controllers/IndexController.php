<?php

namespace Paramatma\Common\Controllers;

class IndexController extends \Phalcon\Mvc\Controller
{

    public function initialize()
    {
        // Javascript
        $this->assets->collection('jsFooter')
                ->addJs('jquery-2.1.4.js')
                ->addJs('bootstrap.min.js')
                ->addJs('app.js');

        // Добавляем локальные таблицы стилей
        $this->assets->collection('cssHeader')
                ->addCss('bootstrap.css')
                ->addCss('font-awesome.css')
                ->addCss('app.css');
    }

    public function indexAction()
    {

        $this->config3();
    }

    public function config3()
    {
        $di = $this->getDI();
        $config = $di->get('config');
        //debug_var($config);
        $logger = $di->getShared('logger');
        echo 'INITIAL CONFIG:<br />';
        debug_var($config, false);
        
        echo 'DB_CONFIG:init()<br />';
        $db_config = new \Paramatma\Config\Adapter\Mysql($config->database->db->toArray(), $logger, 'pa_config');
        debug_var($db_config->get(), false);
        
        echo 'DB_CONFIG:write()<br />';
        $config->test = 'test';
        $db_config->set($config);
        debug_var($db_config->get(), false);
        
        debug_var(1);
    }
    
    
    public function config2()
    {
        $method = __METHOD__;
        $arr    = array();
        $idx    = 0;

        $this->view->pick("common/index/index");

        $logger = $this->getDI()->get('logger');

        //-- usage
        if ($logger->customFlag) {
            $logger->info(array(__METHOD__, 'this is INFO message as ARRAY'),
                          '%s(): %s');
            $logger->info(__METHOD__ . '(): this is INFO message as STRING');
        }

        $config = $this->getDI()->get('config');

        echo 'INPUT:<br />';
        debug_var($config, false);
        //var_dump($config);
        //echo '<br /><br />';

        try {
            //-- make db-connection
            $db_config      = array(
                'host'     => 'localhost',
                'port'     => 3306,
                'username' => 'paramatma',
                'password' => 'paramatma',
                'dbname'   => 'paramatma'
            );
            $db_connection  = new \Phalcon\Db\Adapter\Pdo\Mysql($db_config);
            $db_connection->begin();
            $db_del_request = 'DELETE FROM pa_config';
            $db_sel_request = 'SELECT path, value FROM pa_config';

            $db_result = $db_connection->execute($db_del_request);
            echo 'SQL->DELETE: db_result=' . $db_result . '<br />';
            if (!$db_result) {
                $e = __METHOD__ . '() could not delete records res:[' . $db_result . '].';
                $logger->error($e);
                echo $e . '<br />';
                debug_var(1);
                return;
            }

            $traverse = function($config_, $path_) use (&$traverse, &$logger, &$method, &$arr, &$idx, &$db_connection) {
                $db_ins_request = 'INSERT INTO pa_config (path, value) VALUES(?, ?)';
                //--
                if (empty($path_)) {
                    $path_ = '/';
                }
                if (strncmp($path_, '/', 1) !== 0) {
                    $path_ = '/' . $path_;
                }
                //--
                foreach ($config_ as $key => $val) {
                    $npath = ($path_ !== '/' ? $path_ . '/' . $key : $path_ . $key);
                    if ($val instanceof \Phalcon\Config) {
                        $traverse($val, $npath);
                    } else {
                        debug_var($npath . ':[' . $val . ']<br>', false);
                        $db_result   = $db_connection->execute($db_ins_request,
                                                               array($npath, $val));
                        echo 'SQL->INSERT: db_result=' . $db_result . '<br />';
                        //$arr[$idx++] = array($npath, $val);
                        $arr[$npath] = $val;
                    }
                }
            };

            $traverse($config, '/');

            echo '<br />ARRAY:<br />';
            $db_sel_result = $db_connection->fetchAll($db_sel_request,
                                                      \Phalcon\Db::FETCH_ASSOC);
            /* foreach($db_sel_result as $row){ 
              debug_var($row, false);
              } */
            debug_var($db_sel_result, false);
            //debug_var(1);
            //echo 'SQL->SELECT: db_sel_result->fetchArray():<br />';
            //debug_var($db_sel_result->fetchArray());
            //echo 'SQL->SELECT: db_sel_result->fetchAll()=<br />';
            //debug_var($db_sel_result->fetchAll());
            //debug_var(1);
            //$arr_sel       = $db_sel_result->fetchAll();
            //debug_var($arr_sel, false);
            //debug_var(1);

            $revert = function($aInp_, $oOut_) use (&$logger, &$method) {
                if (empty($aInp_)) {
                    $err = $method . '(): invalid array:[' . $aInp_ . ']!';
                    echo $err;
                    $logger->error($err);
                    return;
                }
                //--
                $pathToConf = function($path_, $val_, $obj_) {
                    $last  = &$obj_;
                    //echo 'path:[' . $path_ . ']<br />';
                    $path_ = explode('/', $path_);
                    //var_dump($path_);
                    //--
                    foreach ($path_ as $p) {
                        if (empty($p)) {
                            continue;
                        }
                        //var_dump($last, false);
                        //echo 'Type:' . ($last.$p instanceof \Phalcon\Config) . '<br />';
                        if (!property_exists($last, $p)) {
                            //if(!($last.$p instanceof \Phalcon\Config)){
                            //echo 'p=[' . $p . ']<br />';
                            $last->$p = new \Phalcon\Config();
                        }
                        $last = &$last->$p;
                    }
                    //--
                    $last = $val_;
                    //debug_var($obj_, false);
                };

                //--
                foreach ($aInp_ as $row) {
                    //debug_var($row[0] . ':' . $row[1] . '<br />', false);
                    $pathToConf($row['path'], $row['value'], $oOut_);
                }
            };

            $out_config = new \Phalcon\Config();
            //$revert($arr_sel, $out_config);
            $revert($db_sel_result, $out_config);

            echo '<br />OUTPUT:<br />';
            debug_var($out_config, false);

            $db_connection->commit();
        } catch (Exception $ex) {
            if (!empty($db_connection)) {
                if ($db_connection->isUnderTransaction()) {
                    $db_connection->rollback();
                }
            }
        }

        debug_var(1);
    }

    public function config1()
    {
        $method = __METHOD__;
        $arr    = array();
        $idx    = 0;

        $this->view->pick("common/index/index");

        $logger = $this->getDI()->get('logger');

        //-- usage
        if ($logger->customFlag) {
            $logger->info(array(__METHOD__, 'this is INFO message as ARRAY'),
                          '%s(): %s');
            $logger->info(__METHOD__ . '(): this is INFO message as STRING');
        }

        $config = $this->getDI()->get('config');

        echo 'INPUT:<br />';
        debug_var($config, false);

        $traverse = function($config_, $path_) use (&$traverse, &$logger, &$method, &$arr, &$idx) {
            if (empty($path_)) {
                $path_ = '/';
            }
            if (strncmp($path_, '/', 1) !== 0) {
                $path_ = '/' . $path_;
            }
            //--
            foreach ($config_ as $key => $val) {
                $npath = ($path_ !== '/' ? $path_ . '/' . $key : $path_ . $key);
                if ($val instanceof \Phalcon\Config) {
                    $traverse($val, $npath);
                } else {
                    debug_var($npath . ':[' . $val . ']<br>', false);
                    //$arr[$idx++] = array($npath, $val);
                    $arr[$npath] = $val;
                }
            }
        };

        $traverse($config, '/');

        echo '<br />ARRAY:<br />';
        debug_var($arr, false);

        $revert = function($aInp_, $oOut_) use (&$logger, &$method) {
            if (empty($aInp_)) {
                $err = $method . '(): invalid array:[' . $aInp_ . ']!';
                echo $err;
                $logger->error($err);
                return;
            }
            //--
            $pathToConf = function($path_, $val_, $obj_) {
                $last  = &$obj_;
                //echo 'path:[' . $path_ . ']<br />';
                $path_ = explode('/', $path_);
                //var_dump($path_);
                //--
                foreach ($path_ as $p) {
                    if (empty($p)) {
                        continue;
                    }
                    //var_dump($last, false);
                    //echo 'Type:' . ($last.$p instanceof \Phalcon\Config) . '<br />';
                    if (!property_exists($last, $p)) {
                        //if(!($last.$p instanceof \Phalcon\Config)){
                        //echo 'p=[' . $p . ']<br />';
                        $last->$p = new \Phalcon\Config();
                    }
                    $last = &$last->$p;
                }
                //--
                $last = $val_;
                //debug_var($obj_, false);
            };

            //--
            foreach ($aInp_ as $p => $v) {
                $pathToConf($p, $v, $oOut_);
            }
        };

        $oOut = new \Phalcon\Config();
        $revert($arr, $oOut);

        echo '<br />OUTPUT:<br />';
        debug_var($oOut);

        debug_var(1);
    }

    public function config0()
    {
        $method = __METHOD__;
        $arr    = array();
        $idx    = 0;

        $this->view->pick("common/index/index");

        $logger = $this->getDI()->get('logger');

        //-- usage
        if ($logger->customFlag) {
            $logger->info(array(__METHOD__, 'this is INFO message as ARRAY'),
                          '%s(): %s');
            $logger->info(__METHOD__ . '(): this is INFO message as STRING');
        }

        $config = $this->getDI()->get('config');

        echo 'INPUT:<br />';
        debug_var($config, false);

        $traverse = function($config_, $path_) use (&$traverse, &$logger, &$method, &$arr, &$idx) {
            if (empty($path_)) {
                $path_ = '/';
            }
            if (strncmp($path_, '/', 1) !== 0) {
                $path_ = '/' . $path_;
            }
            //--
            foreach ($config_ as $key => $val) {
                $npath = ($path_ !== '/' ? $path_ . '/' . $key : $path_ . $key);
                if ($val instanceof \Phalcon\Config) {
                    $traverse($val, $npath);
                } else {
                    debug_var($npath . ':[' . $val . ']<br>', false);
                    $arr[$idx++] = array($npath, $val);
                }
            }
        };

        $traverse($config, '/');

        echo '<br />ARRAY:<br />';
        debug_var($arr);

        $revert = function($aInp_, $oOut_) use (&$logger, &$method) {
            if (empty($aInp_)) {
                $err = $method . '(): invalid array:[' . $aInp_ . ']!';
                echo $err;
                $logger->error($err);
                return;
            }
            //--
            $pathToConf = function($path_, $val_, $obj_) {
                $last  = &$obj_;
                //echo 'path:[' . $path_ . ']<br />';
                $path_ = explode('/', $path_);
                //var_dump($path_);
                //--
                foreach ($path_ as $p) {
                    if (empty($p)) {
                        continue;
                    }
                    //var_dump($last, false);
                    //echo 'Type:' . ($last.$p instanceof \Phalcon\Config) . '<br />';
                    if (!property_exists($last, $p)) {
                        //if(!($last.$p instanceof \Phalcon\Config)){
                        //echo 'p=[' . $p . ']<br />';
                        $last->$p = new \Phalcon\Config();
                    }
                    $last = &$last->$p;
                }
                //--
                $last = $val_;
                //debug_var($obj_, false);
            };

            //--
            foreach ($aInp_ as $row) {
                //debug_var($row[0] . ':' . $row[1] . '<br />', false);
                $pathToConf($row[0], $row[1], $oOut_);
            }
        };

        $oOut = new \Phalcon\Config();
        $revert($arr, $oOut);

        echo '<br />OUTPUT:<br />';
        debug_var($oOut);

        debug_var(1);
    }
}
