<?php
/**
 * Paramatma (http://paramatma.io)
 *
 * @link      http://github.com/paramatma-io/paramatma for the canonical source repository
 * @copyright Copyright (c) 2015 Paramatma team
 * @license   http://opensource.org/licenses/MIT MIT License
 * @package   Paramatma
 */

/**
 * Define root path
 */
define('ROOT_PATH', rtrim(str_replace('\\', '/', realpath('..')), '/') . DIRECTORY_SEPARATOR);

/**
 * Define dir names
 */
define('APP_DIR',         'app');
  define('CACHE_DIR',     'cache');
  define('CONFIGS_DIR',   'configs');
  define('I18N_DIR',      'i18n');
  define('PARAMATMA_DIR', 'library');
  define('MODULES_DIR',   'modules');
  define('RESOURCES_DIR', 'resources');
  define('SCHEMAS_DIR',   'schemas');
  define('THEMES_DIR',    'themes');

define('LOGS_DIR',    'logs');
define('WWW_DIR',  'public');
  define('ASSETS_DIR',  'assets');
define('SCRIPTS_DIR', 'scripts');
define('TEMP_DIR',    'temp');
define('TESTS_DIR',   'tests');
define('VENDOR_DIR',   'vendor');

/**
 * Define main paths
 */
define('APP',         ROOT_PATH . APP_DIR . DIRECTORY_SEPARATOR);
  define('CACHE',     APP . CACHE_DIR .     DIRECTORY_SEPARATOR);
  define('CONFIGS',   APP . CONFIGS_DIR .   DIRECTORY_SEPARATOR);
  define('I18N',      APP . I18N_DIR .      DIRECTORY_SEPARATOR);
  define('PARAMATMA', APP . PARAMATMA_DIR . DIRECTORY_SEPARATOR);
  define('MODULES',   APP . MODULES_DIR .   DIRECTORY_SEPARATOR);
  define('RESOURCES', APP . RESOURCES_DIR . DIRECTORY_SEPARATOR);
  define('SCHEMAS',   APP . SCHEMAS_DIR .   DIRECTORY_SEPARATOR);
  define('THEMES',    APP . THEMES_DIR .    DIRECTORY_SEPARATOR);

define('LOGS',     ROOT_PATH . LOGS_DIR .    DIRECTORY_SEPARATOR);
define('WWW',      ROOT_PATH . WWW_DIR .     DIRECTORY_SEPARATOR);
  define('ASSETS', WWW       . ASSETS_DIR .  DIRECTORY_SEPARATOR);
define('SCRIPTS',  ROOT_PATH . SCRIPTS_DIR . DIRECTORY_SEPARATOR);
define('TEMP',     ROOT_PATH . TEMP_DIR .    DIRECTORY_SEPARATOR);
define('TESTS',    ROOT_PATH . TESTS_DIR .   DIRECTORY_SEPARATOR);
define('VENDOR',   ROOT_PATH . VENDOR_DIR .  DIRECTORY_SEPARATOR);

/**
 * Paramatma common functions library
 */
require(PARAMATMA . 'Functions.php');

/**
 * Instantiate DI object
 */
$di = new \Phalcon\DI();

/**
 * If not a production environment initialize test and debug utilities
 */
if (!production()) {
    $debug = new \Phalcon\Debug();
    $di->set('debug', $debug);
    $debug->listen();
}

/**
 * Setup and register Phalcon loader (autoloading)
 * Using namespaces (PSR-4)
 */
$loader = new \Phalcon\Loader();

$loader->registerNamespaces(array(
    'Phalcon' => VENDOR . 'phalcon/incubator/Library/Phalcon',
    'Paramatma' => PARAMATMA,
    'Paramatma\Common\Controllers' => MODULES . 'common/controllers'
));

$loader->register();
$di->setShared('loader', $loader);

/**
 * Run the App
 */
try {
    $application = new \Paramatma\Mvc\Application($di);
    $application->run();
} catch(\Phalcon\Exception $e) {
    if (production()) {
        /**
         * @todo Тут нужно писать в лог и уведомлять разрабов по СМС
         */
    } else {
        $debug->onUncaughtException($e);
    }
}

/**
 * Print stats
 */
if (!production()) {
    debug_print_stats();
}

