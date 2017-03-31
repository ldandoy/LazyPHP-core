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
     * Cette fonction appel la fonction setData au l'initialisation
     * de l'objet
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

                switch($association['type']) {
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

        if (isset($data['created_at'])) {
            $this->created_at = $data['created_at'];
        }

        if (isset($data['updated_at'])) {
            $this->updated_at = $data['updated_at'];
        }
    }

    /**
     * Set default properties values
     */
    public function setDefaultProperties()
    {
        foreach ($this->permittedColumns as $k => $v) {
            $this->$v = null;
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

        return $query->execute($permittedData);
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

        return $query->execute($permittedData);
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
     * Get list of associed table(s)
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
     * Valid the object and fill $this->errors with error messages. Should be overrided in child class
     *
     * @return bool
     */
    public function valid()
    {
        $this->errors = array();
        return true;
    }

    /**
     * Valid a file : return true if OK, string with error message
     *
     * @param mixed $file
     * @param string $type '' | 'image' | 'video' | 'music'
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

                case 'music':
                    if (!in_array($ext, array('wav', 'mp3', 'mid', 'ogg'))) {
                        return 'Le fichier doit être un fichier audio (wav, mp3, mid, ogg)';
                    }
                    break;
            }
        }

        return true;
    }
}
