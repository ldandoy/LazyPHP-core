<?php

namespace System;

use System\TemplatorParser;

use Helper\Bootstrap;
use Helper\Html;
use Helper\Form;

class Templator
{
    /**
     * @param mixed $attributes
     * @param mixed $params
     */
    private function getModelValueForInput($attributes, $params)
    {
        if (isset($attributes['model'])) {
            $model = $attributes['model'];
            if (strpos($model, '.') !== false) {
                $a = explode('.', $model, 2);
                if (isset($params[$a[0]])) {
                    $obj = $params[$a[0]];
                    $key = $a[1];

                    $value = isset($obj->$key) ? $obj->$key : '';
                    $error =  isset($obj->errors[$key]) ? $obj->errors[$key] : '';
                } else {
                    return null;
                }
            } else {
                $value = isset($params[$model]) ? $params[$model] : '';
                $error = isset($params['errors'][$model]) ? $params['errors'][$model] : '';
            }
            return array(
                'value' => $value,
                'error' => $error
            );
        } else {
            return null;
        }
    }

    /**
     * @param mixed $attributes
     * @param mixed $params
     */
    private function getOptionsForInput($attributes, $params)
    {
        if (isset($attributes['options'])) {
            $options = $attributes['options'];
            if ($options[0] == '[') {
                $a = explode(';', trim($options, '[]'));
                $options = array();
                foreach ($a as $v) {
                    $b = explode(':', $v);
                    $options[] = array('label' => $b[0], 'value' => $b[1]);
                }
                return $options;
            } else if (isset($params[$options])) {
                return $params[$options];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param string $html
     * @param mixed $params
     */
    public function parse($html, $params = array())
    {
        $matchesFunctions = array();
        preg_match_all("/{% *([^}{]*) *%}/", $html, $matchesFunctions, PREG_SET_ORDER);

        if (!empty($matchesFunctions)) {
            $parser = new TemplatorParser();

            foreach ($matchesFunctions as $v) {
                $data = $parser->parse($v[1]);

                $tag = $data['tag'];
                $attributes = $data['attributes'];

                if (strpos($tag, 'input_') === 0) {
                    $model = $this->getModelValueForInput($attributes, $params);
                    if ($model !== null) {
                        $attributes['model'] = $model;
                    } else {
                        unset($attributes['model']);
                    }
                }

                if ($tag == 'form_open') {
                    if (isset($attributes['action']) && isset($params[$attributes['action']])) {
                        $attributes['action'] = $params[$attributes['action']];
                    } else {
                        unset($attributes['action']);
                    }
                }

                if ($tag == 'input_select' || $tag == 'input_radiogroup' || $tag == 'input_checkboxgroup') {
                    $options = $this->getOptionsForInput($attributes, $params);
                    if ($options !== null) {
                        $attributes['options'] = $options;
                    } else {
                        unset($attributes['options']);
                    }
                }

                switch ($tag) {
                    case 'link':
                        $replace = Html::link($attributes);
                        break;

                    case 'button':
                        $replace = Bootstrap::button($attributes);
                        break;

                    case 'image':
                        $replace = Html::image($attributes);
                        break;

                    case 'title':
                        $replace = Html::title($attributes);
                        break;

                    case 'articles_list':
                        $attributes['articles'] = isset($params['articles']) ? $params['articles'] : null;
                        
                        $replace = Html::articleslist($attributes);
                        break;

                    case 'table':
                        $datasetKey = isset($attributes['dataset']) ? $attributes['dataset'] : '';
                        if ($datasetKey != '' && isset($params[$datasetKey])) {
                            $attributes['dataset'] = $params[$datasetKey];
                        } else {
                            unset($attributes['dataset']);
                        }

                        $replace = Html::table($attributes);
                        break;

                    case 'form_open':
                        $replace = Form::open($attributes);
                        break;

                    case 'form_close':
                        $replace = Form::close($attributes);
                        break;

                    case 'input_text':
                        $replace = Form::text($attributes);
                        break;

                    case 'input_password':
                        $replace = Form::password($attributes);
                        break;

                    case 'input_textarea':
                        $replace = Form::textarea($attributes);
                        break;

                    case 'input_select':
                        $replace = Form::select($attributes);
                        break;

                    case 'input_checkbox':
                        $replace = Form::checkbox($attributes);
                        break;

                    case 'input_checkboxgroup':
                        $replace = Form::checkboxgroup($attributes);
                        break;

                    case 'input_radiogroup':
                        $replace = Form::radiogroup($attributes);
                        break;

                    case 'input_file':
                        $replace = Form::file($attributes);
                        break;

                    case 'input_image':
                        $replace = Form::image($attributes);
                        break;

                    case 'input_video':
                        $replace = Form::video($attributes);
                        break;

                    case 'input_music':
                        $replace = Form::music($attributes);
                        break;

                    case 'input_submit':
                        $replace = Form::submit($attributes);
                        break;

                    default:
                        $replace = '';
                }

                $html = str_replace($v[0], $replace, $html);
            }
        }

        $matchesVar = array();
        preg_match_all("/{{ *([^}{ ]*) *}}/", $html, $matchesVar, PREG_SET_ORDER);

        if (!empty($matchesVar)) {
            foreach ($matchesVar as $v) {
                $model = $v[1];
                if (strpos($model, '.') !== false) {
                    $a = explode('.', $model, 2);
                    if (isset($params[$a[0]])) {
                        $obj = $params[$a[0]];
                        $key = $a[1];
                        $replace = isset($obj->$key) ? $obj->$key : '';
                    } else {
                        $replace = '';
                    }
                } else if (isset($params[$model])) {
                    $replace = $params[$model];
                } else {
                    $replace = '';
                }
                $html = str_replace($v[0], $replace, $html);
            }
        }

        return $html;
    }
}
