<?php
/**
 * NOTICE OF LICENSE 
 *
 * This source file is released under commercial license by Lamia Oy. 
 *
 * @copyright  Copyright (c) 2017 Lamia Oy (https://lamia.fi) 
 * @author     Irina Mäkipaja <irina@lamia.fi>
 */

namespace Lamia\Validation\Validation\Interfaces;

use Lamia\Validation\Exception\FieldValidationFailedException;

/**
 * Interface TypeValidation
 * @package Lamia\Validation\Validation\Interfaces
 * The basic validation unit
 */
interface TypeValidation
{
    /**
     * validates a given field against given constraints 
     * @param string $name of the field
     * @param mixed $value to be validated
     * @param array $constraints of validation constraints (for example min => 1 etc)
     * @throws FieldValidationFailedException if the validation failed
     */
    public function validate($name, $value, array $constraints);
}
