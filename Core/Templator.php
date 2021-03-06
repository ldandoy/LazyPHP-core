<?php

namespace Core;

use Core\TemplatorParser;

use Helper\Bootstrap;
use Helper\Html;
use Helper\Form;

use Widget\widgets\Widget;

class Templator
{
    /**
     * Get an object's property value or call an object's method
     * @param mixed $obj
     * @param string $key
     *
     * @return mixed
     */
    private function getValue($obj, $key)
    {
        if (isset($obj->$key)) {
            return $obj->$key;
        } else if (method_exists($obj, $key)) {
            return call_user_func(array($obj, $key));
        } else {
            return '';
        }
    }

    /**
     * @param mixed $attributes
     *
     * @return mixed
     */
    private function processAttributes($attributes, $params)
    {
        $processedAttributes = array();
        foreach ($attributes as $name => $attribute) {
            $matchesVar = array();
            preg_match_all('/\\$ *([^\\$ ]*) *\\$/', $attribute, $matchesVar, PREG_SET_ORDER);
            if (!empty($matchesVar)) {
                foreach ($matchesVar as $v) {
                    $model = $v[1];
                    if (strpos($model, '.') !== false) {
                        $a = explode('.', $model, 2);
                        if (isset($params[$a[0]])) {
                            $obj = $params[$a[0]];
                            $key = $a[1];
                            $replace = $this->getValue($obj, $key);
                        } else {
                            $replace = '';
                        }
                    } else if (isset($params[$model])) {
                        $replace = $params[$model];
                    } else {
                        $replace = '';
                    }
                    $attribute = str_replace($v[0], $replace, $attribute);
                }
            }
            $processedAttributes[$name] = $attribute;
        }
        return $processedAttributes;
    }

    /**
     * @param mixed $attributes
     * @param mixed $params
     *
     * @return mixed
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
                    $value = $this->getValue($obj, $key);
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
     *
     * @return mixed
     */
    private function getOptionsForInput($attributes, $params)
    {
        if (isset($attributes['options'])) {
            $options = $attributes['options'];
            // If we have direct value (["ceci est a": a, "Ceci est b": b])
            if ($options[0] == '[') {
                $a = explode(';', trim($options, '[]'));
                $options = array();
                if (!empty($a)) {
                    foreach ($a as $k => $v) {
                        if ($v != '') {
                            $b = explode(':', $v);
                            $options[] = array('label' => $b[0], 'value' => isset($b[1]) ? $b[1] : $k);
                        }
                    }
                }
                return $options;
            } else if (isset($params[$options])) {
                $optionsList = array();
                foreach ($params[$options] as $value) {
                    $optionsList[] = array('label' => $value['label'], 'value' => $value['value']);
                }
                return $optionsList;
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
     *
     * @return string
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
                $attributes = $this->processAttributes($data['attributes'], $params);

                preg_match_all('/{{ *([^}{ ]*) *}}/', $html, $matchesVar, PREG_SET_ORDER);


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

                if ($tag == 'widget') {
                    $attributes['_controller'] = $params['_controller'];
                }

                $replace = '';

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

                    case 'input_hidden':
                        $replace = Form::hidden($attributes);
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

                    case 'input_audio':
                        $replace = Form::audio($attributes);
                        break;

                    case 'input_upload':
                        $replace = Form::upload($attributes);
                        break;

                    case 'input_media':
                        $replace = Form::media($attributes);
                        break;

                    case 'input_datetime':
                        $replace = Form::datetime($attributes);
                        break;

                    case 'input_magicsuggest':
                        $attributes['options'] = isset($params[$attributes['options']]) ? $params[$attributes['options']] : array();
                        $replace = Form::magicsuggest($attributes);
                        break;

                    case 'input_submit':
                        $replace = Form::submit($attributes);
                        break;

                    case 'widget':
                        $type = isset($attributes['type']) ? $attributes['type'] : '';
                        if ($type != '') {
                            $replace = Widget::getWidgetHtml($type, $attributes);
                        }
                        break;

                    default:
                        break;
                }

                $html = str_replace($v[0], $replace, $html);
            }
        }

        $matchesVar = array();
        preg_match_all('/{{ *([^}{ ]*) *}}/', $html, $matchesVar, PREG_SET_ORDER);

        if (!empty($matchesVar)) {
            foreach ($matchesVar as $v) {
                $model = $v[1];
                if (strpos($model, '.') !== false) {
                    $a = explode('.', $model, 2);
                    if (isset($params[$a[0]])) {
                        $obj = $params[$a[0]];
                        $key = $a[1];
                        $replace = $this->getValue($obj, $key);
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
