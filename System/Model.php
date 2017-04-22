<?php
/**
 * File System\Model.php
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace System;

use System\Config;
use System\Query;
use System\Db;

use System\AttachedFile;

/**
 * Class gérant les Models du site
 *
 * @category System
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Model
{
    public $errors = array();

    public $labelOption = 'label';
    public $valueOption = 'id';

    /**
     * Constructeur
     *
     * Cette fonction appel la fonction setData au l'initialisation de l'objet
     *
     * @param array $data Contient les données à ajouter à l'objet
     *
     * @return void
     */
    public function __construct($data = array())
    {
        $this->setDefaultProperties();
        if (!empty($data)) {
            $this->setData($data);
        } else {
        }
    }

    /**
     * Magic method __get
     * @param string $name
     */
    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->name;
        } else {
            $association = $this->getAssociation($name);
            if ($association !== null) {
                $class = $association['model'];

                switch ($association['type']) {
                    case '1':
                        $this->$name = $class::findById($this->$association['key']);
                        break;
                    case '+':
                    case '*':
                        $this->$name = $class::findAll($association['key'].'='.$this->id);
                        break;
                    default:
                        $this->$name = null;
                        break;
                }
                return $this->$name;
            }

        }

        return null;
    }

    /**
     * Ajout les données dans l'objet
     *
     * Cette fonction est appelé à l'instanciation de la classe pour
     * charger les données dans l'objet
     *
     * @param array $data Contient les données à ajouter àl'objet
     *
     * @return void
     */
    public function setData($data = array())
    {
        if (!is_array($data)) {
            $data = (array)$data;
        }

        if (isset($data['id'])) {
            $this->id = $data['id'];
        }

        $attachedFiles = $this->getAttachedFiles();
        if (isset($this->permittedColumns) && !empty($this->permittedColumns)) {
            foreach ($this->permittedColumns as $column) {
                if (isset($attachedFiles[$column])) {
                    $url = null;
                    $uploadedFile = null;
                    if (isset($data[$column])) {
                        $v = $data[$column];
                        if (is_array($v)) {
                            $url = null;
                            $uploadedFile = $v;
                            if (is_array($uploadedFile)) {
                                $uploadedFile = $uploadedFile[0];
                            }
                        } else if (is_string($v)) {
                            $url = $v;
                            $uploadedFile = null;
                        }
                    }
                    $this->$column = new AttachedFile($url, $uploadedFile);
                } else {
                    if (isset($data[$column])) {
                        $this->$column = $data[$column];
                    }
                }
            }
        }
    }

    /**
     * Set default properties values, special or common fields
     */
    public function setDefaultProperties()
    {
        foreach ($this->permittedColumns as $name) {
            switch ($name) {
                case 'active':
                    $this->$name = 1;
                    break;

                case 'parent':
                    $this->$name = null;
                    break;

                case 'position':
                    $this->$name = 0;
                    break;

                default:
                    $this->$name = null;
                    break;
            }
        }
    }

    private function saveAttachedFiles()
    {
        $data = array();
        $attachedFiles = $this->getAttachedFiles();
        foreach ($attachedFiles as $key => $attachedFile) {
            if (isset($this->$key) && $this->$key != '') {
                $value = &$this->$key;
                if ($value->saveUploadedFile(strtolower(basename(str_replace('\\', '/', get_called_class()))), $this->id, $key)) {
                }
                $data[$key] = $value->url;
            }
        }

        if (!empty($data)) {
            $query = new Query();
            $query->update(array(
                'table' => $this->getTable(),
                'columns' => array_keys($data)
            ));
            $query->where('id = '.$this->id);
            $query->execute($data);
        }
    }

    /**
     * Valid and save the object in database (create or update if id is set)
     *
     * @param mixed $data
     *
     * @return bool
     */
    public function save($data = array())
    {
        $this->setData($data);

        if ($this->valid()) {
            if (isset($this->id)) {
                $res = $this->update((array)$this);
                if (!$res) {
                    return false;
                }
            } else {
                $res = $this->create((array)$this);
                if ($res === false) {
                    return false;
                }
                $this->id = $res;
            }
        } else {
            return false;
        }

        $this->saveAttachedFiles();

        return true;
    }

    /**
     * Create the object in database
     *
     * @param mixed $data
     *
     * @return mixed false on error or the last insert id
     */
    public function create($data = array())
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = $data['created_at'];
        $permittedData = $this->getPermittedData($data);

        $query = new Query();
        $query->insert(array(
            'table' => $this->getTable(),
            'columns' => array_keys($permittedData)
        ));

        $res = $query->execute($permittedData);
        if ($res) {
            $res = $query->lastInsertId();
        }

        return $res;
    }

    /**
     * Update the object in database
     *
     * @param mixed $data
     *
     * @return bool
     */
    public function update($data = array())
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $permittedData = $this->getPermittedData($data);

        $query = new Query();
        $query->update(array(
            'table' => $this->getTable(),
            'columns' => array_keys($permittedData)
        ));
        $query->where('id = '.$this->id);
        $res = $query->execute($permittedData);

        return $res;
    }

    /**
     * Delete the object in database
     *
     * @return bool
     */
    public function delete()
    {
        $query = new Query();
        $query->delete(array('table' => $this->getTable()));
        $query->where('id = :id');
        return $query->execute(array('id' => $this->id));
    }

    /**
     * Get all rows from a table
     *
     * @param mixed $where
     * @param mixed $order
     *
     * @return mixed
     */
    public static function findAll($where = '', $order = '')
    {
        $res = array();
        $class = get_called_class();

        $query = new Query();
        $query->select('*');
        $query->from($class::getTableName());
        $query->where($where);
        $query->order($order);

        $rows = $query->executeAndFetchAll();
        foreach ($rows as $row) {
            $res[] = new $class($row);
        }

        return $res;
    }

    /**
     * Get a record from a table by id
     *
     * @param int $id
     *
     * @return \system\Model
     */
    public static function findById($id = 0)
    {
        $class = get_called_class();

        $query = new Query();
        $query->select('*');
        $query->where('id = :id');
        $query->from($class::getTableName());

        $row = $query->executeAndFetch(array('id' => $id));
        
        $res = new $class($row);

        return $res;
    }

    /**
     * Get the number of record in a table
     *
     * @return int
     */
    public static function count($where = null)
    {
        $class = get_called_class();

        $query = new Query();
        $query->select('COUNT(id) as total');
        $query->from($class::getTableName());
        $query->where($where);

        $row = $query->executeAndFetch();
        
        return (int)$row->total;
    }

    /**
     * Get a record from a table by id
     *
     * @param int $id
     *
     * @return \system\Model
     */
    public static function findBy($key = 'id', $value = 0)
    {
        $class = get_called_class();

        $query = new Query();
        $query->select('*');
        $query->where($key.' = :'.$key);
        $query->from($class::getTableName());

        $row = $query->executeAndFetch(array(''.$key => $value));
        
        $res = new $class($row);
        return $res;
    }

    /**
     * Return the name of the table from the static class calling
     *
     * @return string The name of the table to return
     */
    public static function getTableName()
    {
        $tableName = strtolower(getLastElement(explode('\\', get_called_class()))).'s';
        return $tableName;
    }

    /**
     * Return the name of the table from the class calling
     *
     * @return string The name of the table to return
     */
    public function getTable()
    {
        $class = get_class($this);
        return $class::getTableName();
    }

    /**
     * Get the permitted columns
     *
     * @return mixed
     */
    public function getPermittedColumns()
    {
        return array_merge(
            $this->permittedColumns,
            array('created_at', 'updated_at')
        );
    }

    /**
     * Get data with only the permitted columns
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public function getPermittedData($data = array())
    {
        $permittedData = [];
        $attachedFiles = $this->getAttachedFiles();
        $permittedColumns = $this->getPermittedColumns();
        foreach ($data as $k => $v) {
            if (in_array($k, $permittedColumns) && !isset($attachedFiles[$k])) {
                $permittedData[$k] = $v;
            }
        }
        return $permittedData;
    }

    /**
     * Get validation infos. Should be overrided in child class
     *
     * @return mixed
     *     'type' => 'required' | 'int' | 'float' | 'datetime' | 'date' | 'time' | 'email' | 'password' | 'regex'
     *     'defaultValue' => $defaultValue (if required and not set then take this value with no error)
     *     'filters' => 'filter1,filter2,...' (apply some filters before validation)
     *     'min' => $min (for 'int', 'float')
     *     'max' => $max (for 'int', 'float')
     *     'format' => $datetimeFormat (for 'datetime', 'date', 'time')
     *     'pattern' => $regexPattern (for 'regex')
     *     'error' => $errorMessage
     */
    public function getValidations()
    {
        return array();
    }

    /**
     * Get list of associed tables
     *
     * @return mixed
     */
    public function getAssociations()
    {
        return array();
    }

    /**
     * Get one associed table if exists
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getAssociation($name)
    {
        $associations = $this->getAssociations();
        if (isset($associations[$name])) {
            return $associations[$name];
        } else {
            return null;
        }
    }

    /**
     * Get list of attached files.
     *
     * @return mixed
     */
    public function getAttachedFiles()
    {
        return array();
    }

    /**
     * Get one attached file if exists
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getAttachedFile($name)
    {
        $attachedFiles = $this->getAttachedFiles();
        if (isset($attachedFiles[$name])) {
            return $attachedFiles[$name];
        } else {
            return null;
        }
    }

    /**
     * Valid the object and fill $this->errors with error messages
     *
     * @return bool
     */
    public function valid()
    {
        $this->errors = array();

        // Special or common fields
        $permittedColumns = $this->getPermittedColumns();
        if (!empty($permittedColumns)) {
            if (in_array('active', $permittedColumns) && (!isset($this->active) || $this->active == '')) {
                $this->active = 1;
            }

            if (in_array('parent', $permittedColumns) && (!isset($this->parent) || $this->parent == '')) {
                $this->parent = null;
            }

            if (in_array('position', $permittedColumns) && (!isset($this->position) || $this->position == '')) {
                $this->position = 0;
            }
        }

        // Attached files
        $attachedFiles = $this->getAttachedFiles();
        foreach ($attachedFiles as $key => $attachedFileInfo) {
            $attachedFile = &$this->$key;

            $hasError = false;
            $errorFile = $attachedFile->valid();
            if ($errorFile !== true) {
                $hasError = true;
            }

            if ($hasError) {
                //$attachedFile->url = null;
                $attachedFile->uploadedFile = null;
                $this->errors[$key] = 'Erreur fichier : '.$errorFile;
            }
        }

        $validationList = $this->getValidations();
        foreach ($validationList as $key => $validations) {
            if (!array_key_exists(0, $validations)) {
                $validations = array($validations);
            }

            $required = false;
            foreach ($validations as $validation) {
                if ($validation['type'] == 'required') {
                    $required = true;
                    break;
                }
            }

            foreach ($validations as $validation) {
                $type = $validation['type'];

                $value = isset($this->$key) ? $this->$key : '';

                $filters = isset($validation['filters']) ? $validation['filters'] : array();
                if (!is_array($filters)) {
                    $filters = array($filters);
                }
                if (!empty($filters)) {
                    foreach ($filters as $filter) {
                        switch ($filter) {
                            case 'trim':
                                $value = trim($value);
                                break;
                            
                            case 'uppercase':
                                $value = uppercase($value);
                                break;

                            case 'lowercase':
                                $value = lowercase($value);
                                break;

                            case 'ucfirst':
                                $value = ucfirst($value);
                                break;

                            default:
                                break;
                        }
                    }
                    $this->$key = $value;
                }

                $hasError = false;

                if ($required && $value == '') {
                    if (array_key_exists('defaultValue', $validation)) {
                        $this->$key = $validation['defaultValue'];
                    } else {
                        $this->errors[$key] = $validation['error'];
                    }
                } else if ($value != '') {
                    switch ($type) {
                        case 'int':
                            if (preg_match('/-?[0-9]+/', $value) === false) {
                                $hasError = true;
                            }
                            break;

                        case 'float':
                            if (!is_numeric($value)) {
                                $hasError = true;
                            }
                            break;

                        case 'datetime':
                        case 'date':
                        case 'time':
                            $d = \DateTime::createFromFormat($validation['format'], $value);
                            if (!is_numeric($value)) {
                                $hasError = true;
                            }
                            break;

                        case 'email':
                            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                                $hasError = true;
                            }
                            break;

                        case 'password':
                            if (false) {
                                $hasError = true;
                            }
                            break;

                        case 'regex':
                            if (preg_match($validation['pattern'], $value) === false) {
                                $hasError = true;
                            }
                            break;
                    }
                } else {
                    switch ($type) {
                        case 'int':
                        case 'float':
                        case 'datetime':
                        case 'date':
                        case 'time':
                            $this->$key = null;
                            break;
                    }
                }

                if ($hasError) {
                    $this->errors[$key] = $validation['error'];
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Get category tree
     */
    /*public static function findAllWithChildren()
    {
        return self::getChildren(null, true, 0, false);
    }*/

    /**
     * Get children tree
     *
     * @param int $parent_id
     * @param bool $recursive
     * @param int $level
     * @param bool $flat
     *
     * @return mixed
     */
    public static function getChildren($parent_id = null, $recursive = true, $level = 0, $flat = true, $where = null)
    {
        $class = get_called_class();
        $children_table = $class::getTableName();
        $children = array();

        $query = new Query();
        $query->select('*');
        if ($parent_id === null) {
            $query->where('parent'.' is null');
        } else {
            $query->where('parent = '.$parent_id);
        }
        
        if ($where !== null) {
            $query->where($where);
        }

        $query->order('position');
        $query->from($children_table);

        $res = $query->executeAndFetchAll();

        if ($res !== false) {
            $children = $res;

            foreach ($children as &$child) {
                $child->level = $level;
            }

            if ($recursive) {
                if ($flat) {
                    $i = 0;
                    while ($i < count($children)) {
                        $child = &$children[$i];
                        $child->childCount = 0;
                        $child_children = self::getChildren($child->id, true, $level + 1, true, $where);

                        if (!empty($child_children)) {
                            array_splice($children, $i + 1, 0, $child_children);
                            $i = $i + count($child_children);

                            foreach ($child_children as $child_child) {
                                if ($child_child->parent == $child->id) {
                                    $child->childCount = $child->childCount + 1;
                                }
                            }
                        }
                        $i++;
                    }
                } else {
                    foreach ($children as &$child) {
                        $child_children = self::getChildren($child->id, true, $level + 1, false, 'parent', $children_table);
                        $child->children = $child_children;
                    }
                }
            }
        }

        return $children;
    }

    public static function getFlat($parent_id = null, $where = null)
    {
        return self::getChildren($parent_id, true, 0, true, $where);
    }

    public static function getOptions($parent_id = null)
    {
        $items = self::getFlat($parent_id);

        foreach ($items as $item) {
            $options[$item->id] = array(
                'value' => $item->id,
                'label' => str_repeat('&nbsp;', $item->level * 8).$item->label
            );
        }

        return $options;
    }
}
