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

    public static function getThemeOptions()
    {
        $themeOptions = array();

        // if ($handle = opendir(APP_DIR.DS.'widgets')) {
        //     while (false !== ($entry = readdir($handle))) {
        //         if(!is_dir(APP_DIR.DS.'widgets'.DS.$entry)) {
        //             require APP_DIR.DS.'widgets'.DS.$entry;
        //         }
        //     }
        // }

        // return $themeOptions;
        return array(
            0 => array(
                'value' => 'default',
                'label' => 'Thème par défault'
            ),
            1 => array(
                'value' => 'dark',
                'label' => 'Thème dark'
            )
        );
    }
}
