<?php
/**
 * NOTICE OF LICENSE 
 *
 * This source file is released under commercial license by Lamia Oy. 
 *
 * @copyright  Copyright (c) 2017 Lamia Oy (https://lamia.fi) 
 * @author     Irina Mäkipaja <irina@lamia.fi>
 */

namespace Lamia\Validation\Exception;

use \Exception;

/**
 * Class FieldValidationFailedException
 * @package Verifone\Core\Exception
 * Thrown when validation of a field fails
 */
class FieldValidationFailedException extends Exception
{
    /**
     * FieldValidationFailedException constructor.
     * @param string $name of the field that has failed validation
     * @param string $message appended after "Validation failed, " -text
     */
    public function __construct($name, $message)
    {
        $name = $this->convertToString($name);
        $message = 'Validation failed for field ' . $name . ', ' . $message;
        parent::__construct($message);
    }

    private function convertToString($value)
    {
        if (is_array($value) || is_object($value)) {
            return print_r($value, true);
        }
        return $value;
    }
}
