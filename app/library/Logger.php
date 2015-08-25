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
 * any logging method.
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
     * 
     * @param object $phLogger_ reference of previously created basic \Phalcon\Logger
     * class.
     */
    function __construct($phLogger_)
    {
        $this->_phalconLogger = $phLogger_;
        $this->begin();
    }

    /**
     * Dtor of Logger class.
     */
    function __destruct()
    {
        $this->commit();
    }

    /**
     * Sets logging level of Logger.
     * 
     * @param integer $logLevel_ new logging level value. 
     * Can be one of following 
     * values:
     *   integer \Phalcon\Logger::SPECIAL = 9
     *   integer \Phalcon\Logger::CUSTOM = 8
     *   integer \Phalcon\Logger::DEBUG = 7
     *   integer \Phalcon\Logger::INFO = 6
     *   integer \Phalcon\Logger::NOTICE = 5
     *   integer \Phalcon\Logger::WARNING = 4
     *   integer \Phalcon\Logger::ERROR = 3
     *   integer \Phalcon\Logger::ALERT = 2
     *   integer \Phalcon\Logger::CRITICAL = 1
     *   integer \Phalcon\Logger::EMERGENCE = 0
     *   integer \Phalcon\Logger::EMERGENCY = 0
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
     * 
     * @param mixed $message_ data to log to.
     * @param string $format_ format string similar as PHP vsprintf() format.
     */
    public function special($message_, $format_ = null)
    {
        $this->log(\Phalcon\Logger::SPECIAL, $message_, $format_);
    }

    /**
     * Writes logs in CUSTOM logging level format.
     * 
     * @param mixed $message_ data to log to.
     * @param string $format_ format string similar as PHP vsprintf() format.
     */
    public function custom($message_, $format_ = null)
    {
        $this->log(\Phalcon\Logger::CUSTOM, $message_, $format_);
    }

    /**
     * Writes logs in DEBUG logging level format.
     * 
     * @param mixed $message_ data to log to.
     * @param string $format_ format string similar as PHP vsprintf() format.
     */
    public function debug($message_, $format_ = null)
    {
        $this->log(\Phalcon\Logger::DEBUG, $message_, $format_);
    }

    /**
     * Writes logs in INFO logging level format.
     * 
     * @param mixed $message_ data to log to.
     * @param string $format_ format string similar as PHP vsprintf() format.
     */
    public function info($message_, $format_ = null)
    {
        $this->log(\Phalcon\Logger::INFO, $message_, $format_);
    }

    /**
     * Writes logs in NOTICE logging level format.
     * 
     * @param mixed $message_ data to log to.
     * @param string $format_ format string similar as PHP vsprintf() format.
     */
    public function notice($message_, $format_ = null)
    {
        $this->log(\Phalcon\Logger::NOTICE, $message_, $format_);
    }

    /**
     * Writes logs in WARINING logging level format.
     * 
     * @param mixed $message_ data to log to.
     * @param string $format_ format string similar as PHP vsprintf() format.
     */
    public function warning($message_, $format_ = null)
    {
        $this->log(\Phalcon\Logger::WARNING, $message_, $format_);
    }

    /**
     * Writes logs in ERROR logging level format.
     * 
     * @param mixed $message_ data to log to.
     * @param string $format_ format string similar as PHP vsprintf() format.
     */
    public function error($message_, $format_ = null)
    {
        $this->log(\Phalcon\Logger::ERROR, $message_, $format_);
    }

    /**
     * Writes logs in ALERT logging level format.
     * 
     * @param mixed $message_ data to log to.
     * @param string $format_ format string similar as PHP vsprintf() format.
     */
    public function alert($message_, $format_ = null)
    {
        $this->log(\Phalcon\Logger::ALERT, $message_, $format_);
    }

    /**
     * Writes logs in CRITICAL logging level format.
     * 
     * @param mixed $message_ data to log to.
     * @param string $format_ format string similar as PHP vsprintf() format.
     */
    public function critical($message_, $format_ = null)
    {
        $this->log(\Phalcon\Logger::CRITICAL, $message_, $format_);
    }

    /**
     * Writes logs in EMERGENCY logging level format.
     * 
     * @param mixed $message_ data to log to.
     * @param string $format_ format string similar as PHP vsprintf() format.
     */
    public function emergency($message_, $format_ = null)
    {
        $this->log(\Phalcon\Logger::EMERGENCY, $message_, $format_);
    }

    /**
     * Writes logs in user specified logging level format.
     * 
     * @param integer $type_ logging level
     * @param mixed $message_ data to log to.
     * @param string $format_ format string similar as PHP vsprintf() format.
     */
    public function log($type_, $message_, $format_ = null)
    {
        if ((is_array($message_) || is_object($message_)) && !empty($message_) && !empty($format_)) {
            $message_ = vsprintf($format_, (array) $message_);
        }
        $this->_phalconLogger->log($type_, $message_);
    }
}
