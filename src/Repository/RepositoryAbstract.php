<?php
namespace Madj2k\SpencerBrown\Repository;
use Madj2k\SpencerBrown\Utility\GeneralUtility;


/**
 * RepositoryAbstract
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel 2019
 * @package Madj2k_SpencerBrown
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */

abstract class RepositoryAbstract
{

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $model;

    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var array
     */
    protected $settings;


    /**
     * Constructor
     * @throws \ReflectionException
     */
    public function __construct()
    {

        global $SETTINGS;
        $this->settings = &$SETTINGS;

        // set defaults
        $this->table = GeneralUtility::getTableNameFromRepository($this);
        $this->model = GeneralUtility::getModelClassFromRepository($this);

        // init PDO with utf8mb4 for emoticons
        $this->pdo = new \PDO(
            'mysql:host=' . $this->settings['db']['host'] . ';dbname=' . $this->settings['db']['database'] . ';charset=utf8',
            $this->settings['db']['username'],
            $this->settings['db']['password'],
            array(
                \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            )
        );

        // init and update tables
        $this->initStructure();
    }

    /**
     * init database structure
     */
    protected function initStructure ()
    {
        $reflector = new \ReflectionClass(get_class($this));
        $directories = [
            __DIR__,
            dirname($reflector->getFileName())
        ];

        foreach ($directories as $directory) {
            
            // init and update tables
            $structurePath = $directory . '/../../Database/Structure/';
            $dataPath = $directory . '/../../Database/Data/';
            $updatePath = $directory . '/../../Database/Updates/';
   
            // build structure
            $blockUpdates = [];
            if (
                is_dir($structurePath)
                && (! file_exists($structurePath . $this->table . '.lock'))
                && (file_exists($structurePath . $this->table . '.sql'))
            ){
                $databaseQuery = file_get_contents($structurePath . $this->table . '.sql');
                if ($this->pdo->exec($databaseQuery) !== false) {
                    touch($structurePath . $this->table . '.lock');
                    $blockUpdates[$this->table] = 1; // do not update after init!
                }
            }

            // do updates
            if (is_dir($updatePath)) {
                $updateFiles = glob($updatePath . $this->table . '*.sql', GLOB_BRACE);
                foreach ($updateFiles as $file) {
    
                    if (! is_file($file)) {
                        continue;
                    }
    
                    $fileName = pathinfo($file,PATHINFO_FILENAME);
                    if (
                        (! file_exists($updatePath . $fileName . '.lock'))
                        && ! (isset($blockUpdates[$this->table]))
                    ){
                        $databaseQuery = file_get_contents($file);
                        if ($this->pdo->exec($databaseQuery) !== false) {
                            touch($updatePath . $fileName . '.lock');
                        }
                    } else if (isset($blockUpdates[$this->table])) {
                        touch($updatePath . $fileName . '.lock');
                    }
                }
            }
            
            // insert data
            if(
                (is_dir($dataPath))
                && (! file_exists($dataPath . $this->table . '.lock'))
                && (file_exists($dataPath .  $this->table . '.sql'))
            ){
                $databaseQuery = file_get_contents($dataPath .  $this->table . '.sql');
                if ($this->pdo->exec($databaseQuery) !== false) {
                    touch($dataPath . $this->table . '.lock');
                }
            }
        }
        
        exit();
    }


    /**
     * Magic function for default queries
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     *  @throws \Madj2k\SpencerBrown\Repository\RepositoryException
     */
    public function __call(string $method, array $arguments)
    {
        $whereArguments = [];
        $whereClause = '1 = 1';
        $checkDeleted = true;
        $fetchMethod = '_findAll';
        $select = '*';

        if (strpos($method, 'findOneBy') === 0) {
            if (! $arguments[0]) {
                throw new RepositoryException(sprintf('Method %s expects one parameter as filter criterium.', $method));
            }

            $property = substr($method, 9);
            $fetchMethod = '_findOne';
            $whereArguments = array($arguments[0]);
            $whereClause = GeneralUtility::camelCaseToUnderscore($property) . ' = ?';
            if (isset($arguments[1])) {
                $checkDeleted = boolval($arguments[1]);
            }

        } elseif (strpos($method, 'findBy') === 0) {
            if (! $arguments[0]) {
                throw new RepositoryException(sprintf('Method %s expects one parameter as filter criterium.', $method));
            }

            $property = (substr($method, 6));
            $whereArguments = array($arguments[0]);
            $whereClause = GeneralUtility::camelCaseToUnderscore($property) . ' = ?';
            if (isset($arguments[1])) {
                $checkDeleted = boolval($arguments[1]);
            }

        } elseif (strpos($method, 'findAll') === 0) {
            // nothing to do

            if (isset($arguments[0])) {
                $checkDeleted = boolval($arguments[0]);
            }

        } elseif (strpos($method, 'countAll') === 0) {
            $select = 'COUNT(uid)';
            $fetchMethod = '_countAll';

        } elseif (strpos($method, 'countBy') === 0) {
            if (! $arguments[0]) {
                throw new RepositoryException(sprintf('Method %s expects one parameter as filter criterium.', $method));
            }

            $select = 'COUNT(uid)';
            $fetchMethod = '_countAll';
            $property = (substr($method, 7));
            $whereArguments = array($arguments[0]);
            $whereClause = GeneralUtility::camelCaseToUnderscore($property) . ' = ?';
            if (isset($arguments[1])) {
                $checkDeleted = boolval($arguments[1]);
            }

        } else {
            throw new RepositoryException(sprintf('The %s repository does not have a method %s.', get_called_class(), $method));
        }

        $sql = 'SELECT ' . $select . ' FROM ' . $this->table . ' WHERE ' . $whereClause;
        return $this->$fetchMethod($sql, $whereArguments, $checkDeleted);
    }


    /**
     * count all
     *
     * @param string $sql
     * @param array $arguments
     * @param bool $checkDeleted
     * @return array|null
     * @throws \Madj2k\SpencerBrown\Repository\RepositoryException
     */
    public function _countAll (string $sql, array $arguments = [], $checkDeleted = true)
    {

        if ($checkDeleted) {
            $sql = str_replace('where', 'where ' . $this->table . '.deleted = ? and', strtolower($sql));
            array_unshift($arguments, 0);
        }

        $sth = $this->pdo->prepare($sql);
        if ($sth->execute($arguments)) {
            if ($resultDb = $sth->fetchAll(\PDO::FETCH_ASSOC)) {
                $result = array_values($resultDb[0]);
                return intval($result[0]);
            };

            return null;

        } else {
            throw new RepositoryException($sth->errorInfo()[2]);
        }
    }


    /**
     * Find all
     *
     * @param string $sql
     * @param array $arguments
     * * @param bool $checkDeleted
     * @return array|null
     * @throws \Madj2k\SpencerBrown\Repository\RepositoryException
     */
    public function _findAll (string $sql, array $arguments = [], $checkDeleted = true)
    {
        if ($checkDeleted) {
            $sql = str_replace('where', 'where ' . $this->table . '.deleted = ? and', strtolower($sql));
            array_unshift($arguments, 0);
        }

        $sth = $this->pdo->prepare($sql);
        if ($sth->execute($arguments)) {
            if ($resultDb = $sth->fetchAll(\PDO::FETCH_ASSOC)) {
                $result = [];
                foreach($resultDb as $column) {
                    $result[] = new $this->model($column);
                }
                return $result;
            };

            return null;

        } else {
            throw new RepositoryException($sth->errorInfo()[2]);
        }
    }


    /**
     *
     * @param string $sql
     * @param array $arguments
     * @param bool $checkDeleted
     * @return \Madj2k\SpencerBrown\Model\ModelAbstract|null
     * @throws \Madj2k\SpencerBrown\Repository\RepositoryException
     */
    public function _findOne (string $sql, array $arguments = [], $checkDeleted = true)
    {

        $sql .= ' LIMIT 1';

        if ($checkDeleted) {
            $sql = str_replace('where', 'where ' . $this->table . '.deleted = ? and', strtolower($sql));
            array_unshift($arguments, 0);
        }

        $sth = $this->pdo->prepare($sql);
        if ($sth->execute($arguments)) {
            if ($resultDb = $sth->fetch(\PDO::FETCH_ASSOC)) {

                return new $this->model($resultDb);

            };

            return null;

        } else {
            throw new RepositoryException($sth->errorInfo()[2]);
        }

    }



    /**
     * insert
     *
     * @param \Madj2k\SpencerBrown\Model\ModelAbstract $model
     * @return bool
     * @throws \Madj2k\SpencerBrown\Repository\RepositoryException
     */
    public function insert(\Madj2k\SpencerBrown\Model\ModelAbstract $model)
    {
        if (! $model instanceof $this->model) {
            throw new RepositoryException('Given object not handled by this repository.');
        }

        $insertProperties = $model->_getChangedProperties();
        if (count($insertProperties) > 0) {

            // set defaults
            $insertProperties['create_timestamp'] = $insertProperties['change_timestamp'] = time();

            $columns = implode(',', array_keys($insertProperties));
            $placeholder = implode(',', array_fill(0, count($insertProperties), '?'));
            $values = array_values($insertProperties);

            // fix for boolean conversion (false is converted to empty string)
            $values = array_map(
                function ($value) {
                    return is_bool($value) ? (int) $value : $value;
                },
                $values
            );

            $sql = 'INSERT INTO ' . $this->table . ' (' . $columns . ') VALUES (' . $placeholder . ')';
            $sth = $this->pdo->prepare($sql);

            if ($result = $sth->execute($values)) {
                $model->setUid($this->pdo->lastInsertId());
                return $result;
            } else {
                $error = $sth->errorInfo();
                throw new RepositoryException($error[2] . ' on execution of "' . $sth->queryString . '" with params ' .  print_r($insertProperties, true));
            }
        }

        return false;
    }


    /**
     * update
     *
     * @param \Madj2k\SpencerBrown\Model\ModelAbstract $model
     * @return bool
     * @throws \Madj2k\SpencerBrown\Repository\RepositoryException
     */
    public function update(\Madj2k\SpencerBrown\Model\ModelAbstract $model)
    {
        if (! $model instanceof $this->model) {
            throw new RepositoryException('Given object not handled by this repository.');
        }

        if ($model->_isNew()) {
            throw new RepositoryException('Given object is not persisted and therefore can not be updated.');
        }

        $updateProperties = $model->_getChangedProperties();
        if (count($updateProperties) > 0) {

            // set defaults
            $updateProperties['change_timestamp'] = time();

            $columns = implode(' = ?,', array_keys($updateProperties)) . '= ?';
            $values = array_values($updateProperties);
            $values[] = $model->getUid();

            // fix for boolean conversion (false is converted to empty string)
            $values = array_map(
                function ($value) {
                    return is_bool($value) ? (int) $value : $value;
                },
                $values
            );

            $sql = 'UPDATE ' . $this->table . ' SET ' . $columns . ' WHERE uid = ?';
            $sth = $this->pdo->prepare($sql);

            if ($result = $sth->execute($values)) {
                return $result;
            } else {
                $error = $sth->errorInfo();
                throw new RepositoryException($error[2] . ' on execution of "' . $sth->queryString . '" with params ' .  print_r($updateProperties, true));
            }
        }

        return false;
    }



}