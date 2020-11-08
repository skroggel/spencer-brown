<?php
namespace Madj2k\SpencerBrown\Model;

/**
 * Log
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel 2019
 * @package Madj2k_SpencerBrown
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Log extends ModelAbstract
{

    /**
     * @var int
     */
    protected $level = 0;

    /**
     * @var string
     */
    protected $class = '';
    
    /**
     * @var string
     */
    protected $method = '';

    /**
     * @var string
     */
    protected $apiCall = '';

    /**
     * @var string
     */
    protected $comment = '';

    
    /**
     * Gets level
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }


    /**
     * Sets level
     *
     * @param int $level
     * @return $this
     */
    public function setLevel($level)
    {
        $this->level = intval($level);
        return $this;
    }

    /**
     * Gets class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }


    /**
     * Sets class
     *
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }



    /**
     * Gets method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }


    /**
     * Sets method
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }


    /**
     * Gets apiCall
     *
     * @return string
     */
    public function getApiCall()
    {
        return $this->apiCall;
    }


    /**
     * Sets apiCall
     *
     * @param string $apiCall
     * @return $this
     */
    public function setApiCall($apiCall)
    {
        $this->apiCall = $apiCall;
        return $this;
    }


    /**
     * Gets comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }


    /**
     * Sets comment
     *
     * @param string $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

}