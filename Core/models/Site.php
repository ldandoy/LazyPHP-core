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
}
