<?php

namespace Paramatma\Common\Controllers;

class IndexController extends \Phalcon\Mvc\Controller {

    public function initialize() {
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

	public function indexAction() {       
        $this->view->pick("common/index/index");
        
        $logger = $this->getDI()->get('logger');
        
        //-- usage
        if($logger->customFlag){
            $logger->info(array(__METHOD__, 'this is INFO message as ARRAY'), '%s(): %s');
            $logger->info(__METHOD__ . '(): this is INFO message as STRING');
        }
	}
}