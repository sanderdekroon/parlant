<?php

namespace Sanderdekroon\Parlant\Adapter;

class Query
{

    protected $grammar;
    protected $supportedMethods = [];
    
    public function __construct()
    {
        $this->grammar = new Methods; // @todo
    }


    public function isTranslatable($name)
    {
        return $this->grammar->isSupported($name);
    }


    public function translate($name, $arguments)
    {
        $value = $this->sanitizeValue($this->grammar->getValidationType($name), reset($arguments));

        return [
            $this->grammar->getTranslatedQueryKey($name),
            $value,
        ];
    }


    protected function sanitizeValue($type, $value)
    {
        switch ($type) {
            case 'string':
                // return sanitize_text_field($value);
                return trim($value);
            case 'integer':
                return (int) $value;
            case 'array':
                return $this->sanitizeArray($value);
            
            default:
                return $value;
        }
    }


    protected function sanitizeArray($array)
    {
        if (!is_array($array)) {
            $array = [$array];
        }

        $sanitized = [];
        foreach ($array as $key => $value) {
            if (!is_scalar($value)) {
                $sanitized[$key] = $value;
                continue;
            }

            $sanitized[$key] = $this->sanitizeValue(gettype($value), $value);
        }

        return $sanitized;
    }
}
