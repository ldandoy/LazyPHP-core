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
        'root_path',
        'facebook',
        'twitter',
        'pinterest',
        'googleplus',
        'theme'
    );

    public static function getOptions($parent_id = null)
    {
        $options = array(
            0 => array(
                'value' => '',
                'label' => '---'
            )
        );

        $sites = self::findAll();

        foreach ($sites as $site) {
            $options[$site->id] = array(
                'value' => $site->id,
                'label' => $site->label
            );
        }

        return $options;
    }
}
