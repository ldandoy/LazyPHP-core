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

    public function __construct($url = null, $uploadedFile = null)
    {
        $this->url = $url;
        $this->uploadedFile = $uploadedFile;
    }

    public function saveUploadedFile($model, $id, $name)
    {
        if (isset($this->uploadedFile) && $this->uploadedFile['name'] != '') {
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
    }
}
