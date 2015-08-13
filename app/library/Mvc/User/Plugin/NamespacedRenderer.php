<?php
/**
 * Paramatma (http://paramatma.io)
 *
 * @link      http://github.com/paramatma-io/paramatma for the canonical source repository
 * @copyright Copyright (c) 2015 Paramatma team
 * @license   http://opensource.org/licenses/MIT MIT License
 * @package   Paramatma
 */

namespace Paramatma\Mvc\User\Plugin;

/**
 * Class NamespacedRenderer
 * @package Paramatma\Mvc\User\Plugin
 */
class NamespacedRenderer extends \Phalcon\Mvc\User\Plugin
{

    /*
     * Render view
     *
     * Called from {@link }
     *
     * @return void
     */
    public function viewRender($event, $application, $view)
    {
        $dispatcher = $this->dispatcher;

        $controllerName = str_replace('\\', DIRECTORY_SEPARATOR,
            $dispatcher->getNamespaceName()) . DIRECTORY_SEPARATOR . $dispatcher->getControllerName();

        $view->render($controllerName, $dispatcher->getActionName(), $dispatcher->getParams());
    }
}