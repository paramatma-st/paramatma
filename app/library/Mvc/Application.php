<?php

/**
 * Paramatma (http://paramatma.io)
 *
 * @link      http://github.com/paramatma-io/paramatma for the canonical source repository
 * @copyright Copyright (c) 2015 Paramatma team
 * @license   http://opensource.org/licenses/MIT MIT License
 * @package   Paramatma
 */

namespace Paramatma\Mvc;

use \Phalcon\Mvc\Router,
    \Phalcon\Config,
    \Paramatma\Mvc\User\Plugin\NamespacedRenderer;

/**
 * @category   Paramatma
 * @package    Mvc_Application
 */
class Application extends \Phalcon\Mvc\Application
{

    /**
     * App initialization
     *
     * Called from {@link run()}
     * This method used to register DI and init the application
     *
     * @return void
     */
    protected function _init()
    {
        /* $evManager = new \Phalcon\Events\Manager();
          $evManager->attach('application:viewRender', new NamespacedRenderer());
          $this->setEventsManager($evManager);
         */
    }

    /**
     * Register a config
     *
     * Called from {@link run()}
     * This method registers a config
     *
     * @return void
     */
    protected function _registerConfig()
    {
        $di = $this->getDI();

        // Loading application config
        $appConfig = new \Phalcon\Config(include(CONFIGS . 'app.php'));

        // Registering a config
        $di->set('config', $appConfig);
    }

    /**
     * Register a logger
     *
     * Called from {@link run()}
     * This method registers a logger
     *
     * @return void
     */
    protected function _registerLogger()
    {
        $di     = $this->getDI();
        $config = $di->get('config');

        //-- Set Phalcon Logger
        $di->setShared('phalconLogger',
                       function() use($config) {
            $logger = new \Phalcon\Logger\Adapter\File(LOGS . date($config->log->adapter->file->nameFormat) .
                    '.' . $config->log->adapter->file->extension);
            //--           
            $fmtr   = new \Phalcon\Logger\Formatter\Line($config->log->formatter->line->format,
                                                         $config->log->formatter->dateFormat);
            //--
            $logger->setFormatter($fmtr);
            return $logger;
        });

        //-- Set applicative logger
        $di->setShared('logger',
                       function() use($di, $config) {
            $logger = new \Paramatma\Logger($di->get('phalconLogger'));
            $logger->setLogLevel((int) $config->log->level);
            return $logger;
        });
    }

    /**
     * Register a router and load routes
     *
     * Called from {@link run()}
     * This method registers a router
     *
     * @return void
     */
    protected function _registerRouter()
    {
        if (production()) {
            $routes = include(CACHE . 'routes.php');
        } else {
            $routes = new Config();

            // Load and merge all route configs
            foreach (glob(CONFIGS . 'routes/*.ini') as $filename) {
                $tmpRoutes = new \Phalcon\Config\Adapter\Ini($filename);
                $routes->merge($tmpRoutes);
            }

            unset($tmpRoutes);

            $routes = $routes->toArray();

            // Cache compiled routes config as native php array
            file_put_contents(CACHE . 'routes.php',
                              '<?php return ' . var_export($routes, true) . ';');
        }

        $router = new Router(false);
        $router->setUriSource(Router::URI_SOURCE_SERVER_REQUEST_URI);

        foreach ($routes as $modRoutes) {
            foreach ($modRoutes as $route) {
                if (isset($route['paths']['namespace'])) {
                    $route['paths']['namespace'] = 'Paramatma\\' . ucfirst($route['paths']['namespace'] . '\\Controllers');
                }

                if (!isset($route['httpMethods'])) {
                    $route['httpMethods'] = null;
                }

                $router->add(
                        $route['pattern'], $route['paths'],
                        $route['httpMethods']
                );
            }
        }

        //debug_var($routes);
        //Registering a router
        $di = $this->getDI();
        $di->setShared('router', $router);
    }

    /**
     * Register autoloader
     *
     * Called from {@link run()}
     * This method registers the services to be used by the application
     *
     * @throws \Phalcon\Exception
     * @return void
     */
    protected function _registerServices()
    {
        $di = $this->getDI();

        // Loading application config
        $appConfig = $di->get('config');

        // Registering a dispatcher
        $di->set('dispatcher',
                 function() {
            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            //$dispatcher->setDefaultNamespace('Paramatma\Common\Controllers\\');
            return $dispatcher;
        });

        // Registering a Http\Response
        $di->set('response',
                 function() {
            return new \Phalcon\Http\Response();
        });

        // Registering a Http\Request
        $di->set('request',
                 function() {
            return new \Phalcon\Http\Request();
        });

        // Initializing and registering databases
        foreach ($appConfig->database as $id => $database) {
            switch (strtolower($database->adapter)) {
                case 'mysql':
                    $di->set($id,
                             function() use ($database) {
                        return new \Phalcon\Db\Adapter\Pdo\Mysql($database->toArray());
                    });
                    break;
                case 'pgsql':
                    $di->set($id,
                             function($database) {
                        return new \Phalcon\Db\Adapter\Pdo\Postgresql($database->toArray());
                    });
                    break;
                case 'sqlite':
                    $di->set($id,
                             function($database) {
                        return new \Phalcon\Db\Adapter\Pdo\Sqlite($database->toArray());
                    });
                    break;
                case 'oci':
                    $di->set($id,
                             function($database) {
                        return new \Phalcon\Db\Adapter\Pdo\Oracle($database->toArray());
                    });
                    break;
            }
        }

        // Initializing and registering session
        switch (strtolower($appConfig->session->adapter)) {
            case 'file':
                $di->setShared($id,
                               function() {
                    return new \Phalcon\Session\Adapter\Files();
                });
                break;
            case 'memcached':
                $di->setShared($id,
                               function() use ($di, $appConfig) {
                    return new \Phalcon\Session\Adapter\Libmemcached($appConfig->session->toArray());
                });
                break;
            case 'mysql':
                $di->setShared($id,
                               function() use ($di, $appConfig) {
                    return new \Phalcon\Session\Adapter\Database($appConfig->session->toArray());
                });
                break;
            case 'mongo':
                throw new \Phalcon\Exception('Not implemented');
                break;
            case 'redis':
                throw new \Phalcon\Exception('Not implemented');
                break;
        }

        /**
         * Detect SSL requests and change a session name

          if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on' ||
          isset($headers['X-Forwarded-Proto']) && $headers['X-Forwarded-Proto']=='https' ||
          $_SERVER['SERVER_PORT'] == 443) {
          ini_set('session.name', ini_get('session.name') . 'SSL');
          }
         */
        /**
         * @todo load app config form DB here
         */
        $di->set('view',
                 function() use ($appConfig) {
            // Создание обработчика событий
            $eventsManager = new \Phalcon\Events\Manager();

            // Назначение слушателя для событий типа "view"
            $eventsManager->attach("view",
                                   function($event, $view) {
                echo $event->getType() . ' - ' . $view->getControllerName() . '/' . $view->getActionName() .
                ' ' . $view->getCurrentRenderLevel() . PHP_EOL;
            });

            $view = new \Phalcon\Mvc\View();

            $view->setViewsDir(THEMES . $appConfig->theme . '/views/');
            $view->setLayoutsDir(THEMES . $appConfig->theme . '/views/layouts/');
            $view->setMainView('default');

            // Назначение обработчика событий для компонента представления
            $view->setEventsManager($eventsManager);

            return $view;
        }, true);

        // Registering the Models-Metadata
        $di->set('modelsMetadata',
                 function() {
            return new \Phalcon\Mvc\Model\Metadata\Memory();
        });

        // Registering the Models Manager
        $di->set('modelsManager',
                 function() {
            return new \Phalcon\Mvc\Model\Manager();
        });

        // Registering Escaper
        $di->set('escaper',
                 function() {
            return new \Phalcon\Escaper();
        });

        // Registering MVC URL
        $di->set('url',
                 function() {
            return new \Phalcon\Mvc\Url();
        });

        // Initializing and registering Assets manager
        $assets = new \Paramatma\Assets\Manager();

        $assets->useUniqueJoinedFileName();

        $assets->collection('cssHeader')//->setPrefix(THEMES_DIR . '/' . $appConfig->theme . '/css/')
                ->setSourcePath(WWW . THEMES_DIR . '/' . $appConfig->theme . '/css/')
                ->join(true)
                ->addFilter(new \Phalcon\Assets\Filters\Cssmin());

        $assets->collection('jsHeader');
        /*    ->setSourcePath(WWW . THEMES_DIR . '/' . $appConfig->theme . '/js/')
          ->join(true)
          ->addFilter(new \Phalcon\Assets\Filters\Jsmin()); */

        $assets->collection('jsFooter')
                ->setSourcePath(WWW . THEMES_DIR . '/' . $appConfig->theme . '/js/')
                ->join(true)
                ->addFilter(new \Phalcon\Assets\Filters\Jsmin());

        //Registering Assets manager
        $di->set('assets', $assets);
    }

    /**
     * Run Forrest run!
     *
     * @return void
     */
    public function run()
    {
        $this->_init();
        $this->_registerConfig();
        $this->_registerLogger();
        $this->_registerRouter();
        $this->_registerServices();

        echo $this->handle()->getContent();
    }
}
