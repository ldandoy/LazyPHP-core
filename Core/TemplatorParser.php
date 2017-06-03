<?php

namespace Core;

class TemplatorParser
{
    /**
     * @var string
     */
    private $html = '';

    /**
     * @var in
     */
    private $length = 0;

    /**
     * @var int
     */
    private $offset = 0;

    public function parse($html = '')
    {
        $this->html = $html;
        $this->length = strlen($this->html);
        $this->offset = 0;

        return array(
            'tag' => $this->parseTagName(),
            'attributes' => $this->parseAttributes()
        );
    }

    /**
     * @param string $char
     * @return boolean
     */
    private function isWhiteSpace($char)
    {
        return $char === ' ' ||
                $char === "\t" ||
                $char === "\n" ||
                $char === "\v" ||
                $char === "\f" ||
                $char === "\r";
    }

    private function skipWhiteSpace()
    {
        while ($this->offset < $this->length && $this->isWhiteSpace($this->html[$this->offset]) === true) {
            $this->offset++;
        }
    }

    private function parseTagName()
    {
        $this->skipWhiteSpace();
        $a = $this->offset;
        while ($this->offset < $this->length) {
            $c = $this->html[$this->offset];
            if ($this->isWhiteSpace($c) === true || $c === '/' || $c === '>') {
                break;
            }
            $this->offset++;
        }
        return strtolower(substr($this->html, $a, $this->offset - $a));
    }

    /**
     * @return string
     */
    private function parseAttributes()
    {
        $attributes = array();
        $this->skipWhiteSpace();
        $inAttrName = true;
        $inAttrValue = false;
        $inQuote = '';
        $attrName = '';
        $attrValue = '';
        while ($this->offset < $this->length) {
            $c = $this->html[$this->offset];
            if (($c === '/' || $c === '>') && $inQuote === '') {
                if ($inAttrName === true && $attrName !== '') {
                    $attributes[$attrName] = '';
                } else if ($inAttrValue === true && $inQuote === '') {
                    $attributes[$attrName] = $attrValue;
                }
                break;
            }
            if ($this->isWhiteSpace($c) && $inAttrValue === true && $inQuote === '') {
                $attributes[$attrName] = $attrValue;
                $inAttrName = true;
                $inAttrValue = false;
                $inQuote = '';
                $attrName = '';
                $attrValue = '';
                $this->skipWhiteSpace();
            } else if ($this->isWhiteSpace($c) && $inAttrName === true) {
                $this->skipWhiteSpace();
            } else if ($c === '=' && $inAttrName === true) {
                $inAttrName = false;
                $inAttrValue = true;
                $this->offset++;
                $this->skipWhiteSpace();
            } else if ($c === '"' || $c === '\'') {
                if ($inAttrValue === true && $inQuote === '') {
                    $inQuote = $c;
                } else if ($inQuote !== '') {
                    if ($inQuote === $c) {
                        $inQuote = '';
                    } else if ($inAttrValue === true) {
                        $attrValue .= $c;
                    }
                }
                $this->offset++;
            } else {
                if ($inAttrName === true) {
                    $attrName.=$c;
                } else if ($inAttrValue === true) {
                    $attrValue.=$c;
                }
                $this->offset++;
            }
        }
        return $attributes;
    }
}
