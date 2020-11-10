<?php
namespace Madj2k\SpencerBrown\Utility;

/**
 * LogUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel 2019
 * @package Madj2k_SpencerBrown
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class LogUtility
{

    /**
     * @const int
     */
    const LOG_DEBUG = 0;

    /**
     * @const int
     */
    const LOG_INFO = 1;

    /**
     * @const int
     */
    const LOG_WARNING = 2;

    /**
     * @const int
     */
    const LOG_ERROR = 3;


    /**
     * @var \Madj2k\SpencerBrown\Repository\LogRepository
     */
    protected $logRepository;


    /**
     * @var bool
     */
    protected $echoMessages = false;


    /**
     * @var array
     */
    protected $settings = [];


    /**
     * Constructor
     *
     * @param bool $echoMessages
     * @throws \ReflectionException
     */
    public function __construct($echoMessages = false)
    {

        global $SETTINGS;
        $this->settings = &$SETTINGS;

        // set defaults
        $this->logRepository = new \Madj2k\SpencerBrown\Repository\LogRepository();
        $this->echoMessages = boolval($echoMessages);
    }


    /**
     * Logs actions
     *
     * @param $level
     * @param $message
     * @param $apiCall
     * @throws \Madj2k\SpencerBrown\Repository\RepositoryException
     */
    public function log ($level = LOG_DEBUG, $message = '', $apiCall = '')
    {
        if (! in_array($level, range(0,4))){
            $level = self::LOG_DEBUG;
        }

        if (intval($this->settings['log_level']) <= $level) {

            $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS,2);
            $method = isset($dbt[1]['function']) ? $dbt[1]['function'] : null;
            $class = isset($dbt[1]['class']) ? $dbt[1]['class'] : (isset($dbt[0]['file']) ? $dbt[0]['file'] : null);

            /** @var \Madj2k\SpencerBrown\Model\Log $log */
            $log = new \Madj2k\SpencerBrown\Model\Log();
            $log->setLevel($level)
                ->setClass($class)
                ->setMethod($method)
                ->setComment($message)
                ->setApiCall($apiCall);

            $this->logRepository->insert($log);

            if ($this->echoMessages) {
                echo $message . "\n";
            }
        }
    }
}