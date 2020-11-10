<?php
namespace Madj2k\SpencerBrown\Model;
use Madj2k\SpencerBrown\Utility\GeneralUtility;

/**
 * ModelAbstract
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel 2019
 * @package Madj2k_SpencerBrown
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class ModelAbstract
{

    /**
     * @var int
     */
    protected $uid = 0;


    /**
     * @var int
     */
    protected $createTimestamp = 0;


    /**
     * @var int
     */
    protected $changeTimestamp = 0;


    /**
     * @var bool
     */
    protected $deleted = false;


    /**
     * @var array
     */
    protected $_mapping = [];


    /**
     * @var array
     */
    protected $_initProperties = [];


    /**
     * Constructor
     *
     * @param array|object $data
     */
    public function __construct($data = [])
    {
        $this->_injectData($data);
    }


    /**
     * init data
     *
     * @param array|object $data
     */
    protected function _injectData($data = [])
    {

        // get properties and their initial values
        $existingProperties = get_object_vars($this);
        if (! count($this->_initProperties)) {
            foreach ($existingProperties as $existingProperty => $initialValue) {
                if (strpos($existingProperty, '_') === 0) {
                    continue;
                }
                $this->_initProperties[$existingProperty] = $initialValue;
            }
        }

        // get data from given objects
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        // set given data to model properties and adjust initial values accordingly
        if (
            (is_array($data))
            && (count($data) > 0)
        ) {

            // set given data to model properties and adjust initial values accordingly
            foreach ($data as $key => $value) {
                $property = GeneralUtility::underscoreToCamelCase($key);
                $setter = 'set' . ucfirst($property);
                if (method_exists($this, $setter)) {
                    $this->$setter($value);
                    $this->_initProperties[$property] = $this->{$property};
                }
            }

        }
    }


    /**
     * import data
     *
     * @param array|object $data
     * @return $this
     */
    public function importData($data = [])
    {

        // get data from given objects
        if (is_object($data)) {
            $data = get_object_vars($data);
        }

        // set given data to model properties and adjust initial values accordingly
        if (
            (is_array($data))
            && (count($data) > 0)
        ) {

            // import data from third party source and do field mapping
            // do not reset initial values in this case
            if (is_array($this->_mapping)) {

                foreach ($this->_mapping as $source => $target) {
                    $property = GeneralUtility::underscoreToCamelCase($target);
                    $setter = 'set' . ucfirst($property);
                    if (
                        (array_key_exists($source, $data))
                        && (method_exists($this, $setter))
                    ){
                        $this->$setter($data[$source]);
                    }
                }
            }
        }

        return $this;
    }


    /**
     * Gets uid
     *
     * @return int
     */
    public function getUid()
    {
        return $this->uid;
    }


    /**
     * Sets uid
     *
     * @param int $uid
     * @return $this
     */
    public function setUid(int $uid)
    {
        $this->uid = intval($uid);
        return $this;
    }

    
    
    /**
     * Gets createTimestamp
     *
     * @return int
     */
    public function getCreateTimestamp()
    {
        return $this->createTimestamp;
    }


    /**
     * Sets createTimestamp
     *
     * @param int $timestamp
     * @return $this
     */
    public function setCreateTimestamp(int $timestamp)
    {
        $this->createTimestamp = intval($timestamp);
        return $this;
    }


    /**
     * Gets ChangeTimestamp
     *
     * @return int
     */
    public function getChangeTimestamp()
    {
        return $this->changeTimestamp;
    }


    /**
     * Sets ChangeTimestamp
     *
     * @param int $timestamp
     * @return $this
     */
    public function setChangeTimestamp(int $timestamp)
    {
        $this->changeTimestamp = intval($timestamp);
        return $this;
    }


    /**
     * Gets deleted
     *
     * @return bool
     */
    public function getDeleted()
    {
        return $this->deleted;
    }


    /**
     * Sets deleted
     *
     * @param bool $deleted
     * @return $this
     */
    public function setDeleted($deleted)
    {
        $this->deleted = boolval($deleted);
        return $this;
    }



    /**
     * Returns all modified values since creation of the object
     * e.g. relevant for database updates
     *
     * @return array
     */
    public function _getChangedProperties ()
    {

        $changedProperties = [];
        $allProperties = get_object_vars($this);
        foreach ($allProperties  as $property => $value) {

            // skip internal properties and the uid
            // the uid should never change!
            if (
                (strpos($property, '_') === 0)
                || ($property == 'uid')
            ){
                continue;
            }

            // if object is loaded from database we only take the changed properties
            $underScoredProperty = GeneralUtility::camelCaseToUnderscore($property);
            if (! $this->_isNew()) {
                if ($this->_initProperties[$property] !== $value) {
                    $changedProperties[$underScoredProperty] = $value;
                }

            // else we have to take all properties
            } else {
                $changedProperties[$underScoredProperty] = $value;
            }
        }

        return $changedProperties;
    }



    /**
     * check if model has been saved or not
     *
     * @return bool
     */
    public function _isNew ()
    {
        return !(bool) $this->getUid();
    }
}