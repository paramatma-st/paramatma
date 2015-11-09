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
        $this->checkConfig();
    }

    public function checkConfig()
    {
        $di     = $this->getDI();
        $config = $di->get('config');
        $logger = $di->getShared('logger');
        $db     = $di->get('db');
        //--
        echo 'INITIAL APP_CONFIG:<br />';
        debug_var($config, false);

        //--
        $dbconfig = new \Paramatma\Config\Adapter\Mysql($db, $logger);
        //--
        echo 'INITIAL DB_CONFIG:<br />';
        debug_var($dbconfig, false);

        //--
        $dbconfig->merge($config);
        //unset($config);
        //--
        echo 'MERGED DB_CONFIG:<br />';
        debug_var($dbconfig, false);
        $dbconfig->save();

        debug_var(1);
    }
}
