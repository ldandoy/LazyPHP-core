<?php

namespace Core\models;

use Core\Model;

class Site extends Model
{
    protected $permittedColumns = array(
        'label',
        'host',
        'description',
        'brand_logo',
        'active',
        'logo_access_user',
        'logo_access_admin',
        'home_page',
        'facebook',
        'twitter',
        'pinterest',
        'googleplus',
        'theme'
    );

    public function getAttachedFiles()
    {
        return array_merge(
            parent::getAttachedFiles(),
            array(
                'brand_logo' => array(
                    'type' => 'image'
                )
            )
        );
    }

    public function getThemeOptions()
    {
        $themeOptions = array();

        $themeDirs = array(
            '',
            $this->id
        );

        foreach ($themeDirs as $themeDir) {
            $dir = CSS_DIR.DS.'theme'.DS.$themeDir;
            if (file_exists($dir) && is_dir($dir)) {
                if ($handle = opendir($dir)) {
                    while (false !== ($entry = readdir($handle))) {
                        if ($entry != '.' && $entry != '..' && !is_dir($dir.'/'.$entry)) {
                            $value = str_replace('.css', '', $entry);
                            $label = $value;

                            $value = ltrim($themeDir.'/', '/').$value;

                            $themeOptions[$value] = array(
                                'value' => $value,
                                'label' => $label
                            );
                        }
                    }
                }
            }
        }

        return $themeOptions;
    }
}
