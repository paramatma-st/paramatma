<?php

/**
 * Paramatma (http://paramatma.io)
 *
 * @link      http://github.com/paramatma-io/paramatma for the canonical source repository
 * @copyright Copyright (c) 2015 Paramatma team
 * @license   http://opensource.org/licenses/MIT MIT License
 * @package   Paramatma
 */

namespace Paramatma;

/**
 * Logs program output. Wrapper on \Phalcon\Logger class
 * 
 * The Logger adds possibility of conditional logging without
 * memory overhead by check corresponding xxxFlag variable before call
 * on of logging method.
 */
class Logger
{
    public $specialFlag;
    public $customFlag;
    public $debugFlag;
    public $infoFlag;
    public $noticeFlag;
    public $warningFlag;
    public $errorFlag;
    public $alertFlag;
    public $criticalFlag;
    public $emergencyFlag;
    //--
    protected $_phalconLogger;

    /**
     * Ctor of Logger class.
     * @param object $phL_ - reference of previously created basic \Phalcon\Logger
     * class.
     */
    function __construct($phL_)
    {
        $this->_phalconLogger = $phL_;
        $this->begin();
    }

    /**
     * Dtor of Logger class.
     */
    function __destruct()
    {
        if($this->isTransaction()){
            $this->commit();
        }
    }
    
    /**
     * Sets logging level of Logger.
     * @param integer $logLevel_ - new logging level value. Can be on of following 
     * values:
     * integer SPECIAL = 9
     * integer CUSTOM = 8
     * integer DEBUG = 7
     * integer INFO = 6
     * integer NOTICE = 5
     * integer WARNING = 4
     * integer ERROR = 3
     * integer ALERT = 2
     * integer CRITICAL = 1
     * integer EMERGENCE = 0
     * integer EMERGENCY = 0
     * 
     */
    public function setLogLevel($logLevel_)
    {
        $this->specialFlag   = ($logLevel_ >= \Phalcon\Logger::SPECIAL);
        $this->customFlag    = ($logLevel_ >= \Phalcon\Logger::CUSTOM);
        $this->debugFlag     = ($logLevel_ >= \Phalcon\Logger::DEBUG);
        $this->infoFlag      = ($logLevel_ >= \Phalcon\Logger::INFO);
        $this->noticeFlag    = ($logLevel_ >= \Phalcon\Logger::NOTICE);
        $this->warningFlag   = ($logLevel_ >= \Phalcon\Logger::WARNING);
        $this->errorFlag     = ($logLevel_ >= \Phalcon\Logger::ERROR);
        $this->alertFlag     = ($logLevel_ >= \Phalcon\Logger::ALERT);
        $this->criticalFlag  = ($logLevel_ >= \Phalcon\Logger::CRITICAL);
        $this->emergencyFlag = ($logLevel_ >= \Phalcon\Logger::EMERGENCY);
        //--
        $this->_phalconLogger->setLogLevel($logLevel_);
    }

    /**
     * Returns logging level of Logger.
     * 
     * @return integer value of current logging level.
     */
    public function getLogLevel()
    {
        return $this->_phalconLogger->getLogLevel();
    }

    /**
     * Closes logging output.
     */
    public function close()
    {
        $this->_phalconLogger->close();
    }

    /**
     * Indicates logging transaction existence.
     * @return boolean
     */
    public function isTransaction()
    {
        return $this->_phalconLogger->isTransaction();
    }

    /**
     * Starts logging transaction.
     */
    public function begin()
    {
        $this->_phalconLogger->begin();
    }

    /**
     * Writes changes and closes logging transaction.
     */
    public function commit()
    {
        $this->_phalconLogger->commit();
    }

    /**
     * Cancels changes and closes logging transaction.
     */
    public function rollback()
    {
        $this->_phalconLogger->rollback();
    }

    /**
     * Writes logs in SPECIAL logging level format.
     * @param string $format_ - format string similar as PHP vsprintf() format.
     * @param array $args_ - data to log to.
     */
    public function special($format_, array $args_)
    {
        $this->log(\Phalcon\Logger::SPECIAL, $format_, $args_);
    }

    /**
     * Writes logs in CUSTOM logging level format.
     * @param string $format_ - format string similar as PHP vsprintf() format.
     * @param array $args_ - data to log to.
     */
    public function custom($format_, array $args_)
    {
        $this->log(\Phalcon\Logger::CUSTOM, $format_, $args_);
    }

    /**
     * Writes logs in DEBUG logging level format.
     * @param string $format_ - format string similar as PHP vsprintf() format.
     * @param array $args_ - data to log to.
     */
    public function debug($format_, array $args_)
    {
        $this->log(\Phalcon\Logger::DEBUG, $format_, $args_);
    }

    /**
     * Writes logs in INFO logging level format.
     * @param string $format_ - format string similar as PHP vsprintf() format.
     * @param array $args_ - data to log to.
     */
    public function info($format_, array $args_)
    {
        $this->log(\Phalcon\Logger::INFO, $format_, $args_);
    }

    /**
     * Writes logs in NOTICE logging level format.
     * @param string $format_ - format string similar as PHP vsprintf() format.
     * @param array $args_ - data to log to.
     */
    public function notice($format_, array $args_)
    {
        $this->log(\Phalcon\Logger::NOTICE, $format_, $args_);
    }

    /**
     * Writes logs in WARINING logging level format.
     * @param string $format_ - format string similar as PHP vsprintf() format.
     * @param array $args_ - data to log to.
     */
    public function warning($format_, array $args_)
    {
        $this->log(\Phalcon\Logger::WARNING, $format_, $args_);
    }

    /**
     * Writes logs in ERROR logging level format.
     * @param string $format_ - format string similar as PHP vsprintf() format.
     * @param array $args_ - data to log to.
     */
    public function error($format_, array $args_)
    {
        $this->log(\Phalcon\Logger::ERROR, $format_, $args_);
    }

    /**
     * Writes logs in ALERT logging level format.
     * @param string $format_ - format string similar as PHP vsprintf() format.
     * @param array $args_ - data to log to.
     */
    public function alert($format_, array $args_)
    {
        $this->log(\Phalcon\Logger::ALERT, $format_, $args_);
    }

    /**
     * Writes logs in CRITICAL logging level format.
     * @param string $format_ - format string similar as PHP vsprintf() format.
     * @param array $args_ - data to log to.
     */
    public function critical($format_, array $args_)
    {
        $this->log(\Phalcon\Logger::CRITICAL, $format_, $args_);
    }

    /**
     * Writes logs in EMERGENCY logging level format.
     * @param string $format_ - format string similar as PHP vsprintf() format.
     * @param array $args_ - data to log to.
     */
    public function emergency($format_, array $args_)
    {
        $this->log(\Phalcon\Logger::EMERGENCY, $format_, $args_);
    }

    /**
     * Writes logs in user specified logging level format.
     * @param integer $type_ - logging level
     * @param string $format_ - format string similar as PHP vsprintf() format.
     * @param array $args_ - data to log to.
     */
    public function log($type_, $format_, array $args_)
    {
        $message = vsprintf($format_, $args_);
        $this->_phalconLogger->log($type_, $message);
    }
}
