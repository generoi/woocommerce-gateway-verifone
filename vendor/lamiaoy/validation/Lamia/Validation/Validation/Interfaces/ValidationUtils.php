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
 * General validation utils that can be used by many classes
 * Interface ValidationUtils
 * @package Lamia\Validation\Validation\Interfaces
 */
interface ValidationUtils
{
    /**
     * Validate that the data is not array or object
     * @param string $name of the field
     * @param mixed $data to be validated
     * @throws  FieldValidationFailedException if the validation failed
     */
    function validateNotArrayOrObject($name, $data);

    /**
     * Validates that the limits are valid
     * @param string $name of the validated field
     * @param $lowerLimit
     * @param $upperLimit
     * @throws  FieldValidationFailedException if the validation failed
     */
    function validateLimits($name, $lowerLimit, $upperLimit);

    /**
     * Validates if the value is empty. Fails if is and is not optional field
     * @param string $name of the field to be validated
     * @param mixed $value to be validated
     * @param bool $optional if the value can be empty or not
     * @throws  FieldValidationFailedException if the validation failed
     */
    function validateEmptiness($name, $value, $optional);

    /**
     * Validates that the given value is inside given limits
     * @param string $name of the field to be validated
     * @param int $value to be validated
     * @param int $lowerLimit
     * @param int $upperLimit
     * @throws  FieldValidationFailedException if the validation failed
     */
    function validateInBounds($name, $value, $lowerLimit, $upperLimit);
}
