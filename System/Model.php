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
                        return $this->$name;
                        break;
                    case '+':
                    case '*':
                        // $table = $class::getTableName();
                        // $query = new Query();
                        // $query->select('*');
                        // $query->from($table);
                        break;
                    default:
                        break;
                }
            }

            $attachedFile = $this->getAttachedFile($name);
            if ($attachedFile !== null) {
                $this->$name = $attachedFile;
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

        if (isset($this->permittedColumns) && !empty($this->permittedColumns)) {
            foreach ($this->permittedColumns as $k => $v) {
                if (isset($data[$v])) {
                    $this->$v = $data[$v];
                }
            }
        }

        $attachedFiles = $this->getAttachedFiles();
        foreach ($attachedFiles as $key => $attachedFile) {
            if (isset($data[$key])) {
                $this->$key = $data[$key];
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

    private function getAttachedFilePath($name)
    {
        $attachedFile = $this->getAttachedFile($name);
        if ($attachedFile !== null) {
            $path = PUBLIC_DIR.DS.'uploads'.DS.strtolower(basename(str_replace('\\', '/', get_called_class())));
            $idStr = (string)($this->id);
            for ($i = 0; $i < strlen($idStr); $i++) {
                $path .= '/'.$idStr[$i];
            }

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $path .= DS.$idStr.'_'.$name;

            return $path;
        }
    }

    public function saveAttachedFiles()
    {
        $attachedFiles = $this->getAttachedFiles();
        foreach ($attachedFiles as $key => $attachedFile) {
            if (isset($this->$key)) {
                $uploadedFile = $this->$key;
                $uploadedFile = $uploadedFile[0];
                if ($uploadedFile['name'] != '') {
                    $ext = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
                    $path = $this->getAttachedFilePath($key).'.'.$ext;
                    if (file_exists($path)) {
                        unlink($path);
                    }
                    move_uploaded_file($uploadedFile['tmp_name'], $path);
                }
            }
        }
    }

    /**
     * Create the object in database
     *
     * @param mixed $data
     *
     * @return bool
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
            $this->id = $query->lastInsertId();
            $this->saveAttachedFiles();
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

        if ($res) {
            $this->saveAttachedFiles();
        }

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
        $query->showSql();
        return $query->execute(array('id' => $this->id));
    }

    /**
     * Get all rows from a table
     *
     * @return mixed
     */
    public static function findAll()
    {
        $res = array();
        $class = get_called_class();

        $query = new Query();
        $query->select('*');
        $query->from($class::getTableName());
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
        /*if (isset($res->parent) && !empty($res->parent)) {
            foreach ($res->parent as $k_parent => $v_parent) {
                $parentClass = 'app\\models\\'.$k_parent;
                $parent = $parentClass::findById($v_parent);
                $res->$k_parent = $parent;
            }
        }*/

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
        foreach ($data as $k => $v) {
            if (in_array($k, $this->getPermittedColumns())) {
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
            if (isset($permittedColumns['active']) && (!isset($this->active) || $this->active == '')) {
                $this->active = 1;
            }

            if (isset($permittedColumns['parent']) && (!isset($this->parent) || $this->parent == '')) {
                $this->parent = null;
            }

            if (isset($permittedColumns['position']) && (!isset($this->position) || $this->position == '')) {
                $this->position = 0;
            }
        }

        // Attached files
        $attachedFiles = $this->getAttachedFiles();
        foreach ($attachedFiles as $key => $attachedFile) {
            if (isset($this->$key)) {
                $uploadedFile = $this->$key;
                $uploadedFile = $uploadedFile[0];
                if ($uploadedFile['name'] != '') {
                    $hasError = false;
                    $type = isset($attachedFile['type']) ? $attachedFile['type'] : 'file';
                    switch ($type) {
                        case 'file':
                            $errorFile = $this->validFile($uploadedFile);
                            if ($errorFile !== true) {
                                $hasError = true;
                            }
                            break;

                        case 'image':
                        case 'video':
                        case 'audio':
                            $errorFile = $this->validFile($uploadedFile, $type);
                            if ($errorFile !== true) {
                                $hasError = true;
                            }
                            break;
                    }

                    if ($hasError) {
                        $this->errors[$key] = 'Erreur fichier : '.$errorFile;
                    }
                }
            }
        }

        $validationList = $this->getValidations();
        foreach ($validationList as $key => $validations) {
            if (!array_key_exists(0, $validations)) {
                $validations = array($validations);
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

                            default:
                                break;
                        }
                    }
                    $this->$key = $value;
                }

                $hasError = false;

                if ($type == 'required' && $value == '') {
                    if (array_key_exists('defaultValue', $validation)) {
                        $this->$key = $validation['defaultValue'];
                    } else {
                        $this->errors[$key] = $validation['error'];
                    }
                } else {
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
                }

                if ($hasError) {
                    $this->errors[$key] = $validation['error'];
                }
            }
        }

        return empty($this->errors);
    }

    /**
     * Valid a file : return true if OK, string with error message
     *
     * @param mixed $file
     * @param string $type '' | 'image' | 'video' | 'audio'
     *
     * @return mixed
     */
    public function validFile($file, $type = '')
    {
        if ($file['error'] > UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
                    break;

                case UPLOAD_ERR_FORM_SIZE:
                    return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                    break;

                case UPLOAD_ERR_PARTIAL:
                    return 'The uploaded file was only partially uploaded';
                    break;

                case UPLOAD_ERR_NO_FILE:
                    return 'No file was uploaded';
                    break;

                case UPLOAD_ERR_NO_TMP_DIR:
                    return 'Missing a temporary folder';
                    break;

                case UPLOAD_ERR_CANT_WRITE:
                    return 'Failed to write file to disk';
                    break;

                case UPLOAD_ERR_EXTENSION:
                    return 'A PHP extension stopped the file upload';
                    break;

                default:
                    return 'Unknown error';
                    break;
            }
        }

        if ($type != '') {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);

            switch ($type) {
                case 'image':
                    if (!in_array($ext, array('jpg', 'jpeg', 'png', 'gif'))) {
                        return 'Le fichier doit être une image (jpg, jpeg, png, gif)';
                    }
                    break;

                case 'video':
                    if (!in_array($ext, array('avi', 'mpg', 'mpeg', 'mp4', 'mkv'))) {
                        return 'Le fichier doit être une video (avi, mpg, mpeg, mp4, mkv)';
                    }
                    break;

                case 'audio':
                    if (!in_array($ext, array('wav', 'mp3', 'mid', 'ogg'))) {
                        return 'Le fichier doit être un fichier audio (wav, mp3, mid, ogg)';
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * Get category tree
     */
    public static function findAllWithChildren()
    {
        return self::getChildren(null, true, 0, false);
    }

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
    public static function getChildren($parent_id = null, $recursive = true, $level = 0, $flat = false)
    {
        $class = get_called_class();

        $children = array();

        $query = new Query();
        $query->select('*');
        if ($parent_id === null) {
            $query->where('parent is null');
        } else {
            $query->where('parent = '.$parent_id);
        }
        $query->order('position');
        $query->from($class::getTableName());
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
                        $child_children = self::getChildren($child->id, true, $level + 1, true);

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
                        $child_children = self::getChildren($child->id, true, $level + 1, false);
                        $child->children = $child_children;
                    }
                }
            }
        }

        return $children;
    }

    public static function getFlat()
    {
        return self::getChildren(null, true, 0, true);
    }
}
