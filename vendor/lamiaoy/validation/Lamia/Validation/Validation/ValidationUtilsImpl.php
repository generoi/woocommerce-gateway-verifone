<?php
/**
 * NOTICE OF LICENSE 
 *
 * This source file is released under commercial license by Lamia Oy. 
 *
 * @copyright  Copyright (c) 2017 Lamia Oy (https://lamia.fi) 
 * @author     Irina Mäkipaja <irina@lamia.fi>
 */

namespace Lamia\Validation\Validation;

use Lamia\Validation\Exception\FieldValidationFailedException;
use Lamia\Validation\Validation\Interfaces\ValidationUtils;

class ValidationUtilsImpl implements ValidationUtils
{
    /**
     * @param $name
     * @param $data
     * @throws FieldValidationFailedException
     */
    public function validateNotArrayOrObject($name, $data)
    {
        if (is_array($data)) {
            throw new FieldValidationFailedException($name, "value was an array");
        }
        if (is_object($data)) {
            throw new FieldValidationFailedException($name, "value was an object");
        }
    }

    /**
     * Validate that
     * @param $name
     * @param $lowerLimit int is greater than or equal to 0
     * @param $upperLimit int
     * @throws FieldValidationFailedException
     */
    public function validateLimits($name, $lowerLimit, $upperLimit)
    {
        if (is_int($lowerLimit) === false) {
            $this->validateNotArrayOrObject('Lower limit', $lowerLimit);
            throw new FieldValidationFailedException($name, "lower limit must be integer but was " . $lowerLimit);
        }
        if (is_int($upperLimit) === false) {
            $this->validateNotArrayOrObject('Upper limit', $upperLimit);
            throw new FieldValidationFailedException($name, "upper limit must be integer but was " . $upperLimit);
        }
    }

    public function validateEmptiness($name, $value, $optional)
    {
        if ($value === null || (is_string($value) && $value === '')) {
            return $this->validateEmptyValue($name, $optional);
        }
        return false;
    }

    private function validateEmptyValue($name, $optional)
    {
        if ($optional === false) {
            throw new FieldValidationFailedException($name, "the value is required but is empty.");
        }
        return true;
    }

    /**
     * validate that value is in boundaries
     * @param string $name of the validated field for clearer error messaging
     * @param int $value to be validated to be in given bounds
     * @param int $lowerLimit
     * @param int $upperLimit
     * @throws FieldValidationFailedException
     */
    public function validateInBounds($name, $value, $lowerLimit, $upperLimit)
    {
        $this->validateLimits($name, $lowerLimit, $upperLimit);
        if (!is_int($value)) {
            $this->validateNotArrayOrObject('in bounds value', $value);
            throw new FieldValidationFailedException($name, 'The value used in bounds validations should be int but was ' . $value);
        }
        if ($value < $lowerLimit) {
            throw new FieldValidationFailedException($name, 'limit value is ' . $value . ' but should be over ' . $lowerLimit);
        }
        if ($value > $upperLimit) {
            throw new FieldValidationFailedException($name, 'limit value is ' . $value . ' but should be under ' . $upperLimit);
        }
    }
}
