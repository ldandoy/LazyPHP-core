<?php

namespace System;

class AttachedFile
{
    /**
     * @var string
     */
    public $url = null;

    /**
     * @var mixed
     */
    public $uploadedFile = null;

    /**
     * @var string
     */
    public $type = '';

    public function __construct($url = null, $uploadedFile = null, $type = '')
    {
        $this->url = $url;
        $this->uploadedFile = $uploadedFile;
    }

    private function hasUploadedFile()
    {
        return isset($this->uploadedFile) && $this->uploadedFile['name'] != '';
    }

    /**
     * Valid a file : return true if OK or a string with error message
     *
     * @return mixed
     */
    public function valid()
    {
        if ($this->hasUploadedFile()) {
            if ($this->uploadedFile['error'] > UPLOAD_ERR_OK) {
                switch ($this->uploadedFile['error']) {
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

            if ($this->type != '') {
                $ext = pathinfo($this->uploadedFile['name'], PATHINFO_EXTENSION);

                switch ($this->type) {
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
        }

        return true;
    }

    public function saveUploadedFile($model, $id, $name)
    {
        if ($this->hasUploadedFile()) {
            $ext = pathinfo($this->uploadedFile['name'], PATHINFO_EXTENSION);

            $url = DS.'uploads'.DS.$model;
            $idStr = (string)($id);
            for ($i = 0; $i < strlen($idStr); $i++) {
                $url .= '/'.$idStr[$i];
            }

            $path = PUBLIC_DIR.$url;
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $path .= DS.$idStr.'_'.$name.'.'.$ext;
            $url .= DS.$idStr.'_'.$name.'.'.$ext;

            if (file_exists($path)) {
                unlink($path);
            }
            move_uploaded_file($this->uploadedFile['tmp_name'], $path);

            $this->url = $url;
        }

        return true;
    }
}
