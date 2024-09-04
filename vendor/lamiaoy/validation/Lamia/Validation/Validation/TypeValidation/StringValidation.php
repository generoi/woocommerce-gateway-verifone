<?php
/**
 * NOTICE OF LICENSE 
 *
 * This source file is released under commercial license by Lamia Oy. 
 *
 * @copyright  Copyright (c) 2017 Lamia Oy (https://lamia.fi) 
 * @author     Irina Mäkipaja <irina@lamia.fi>
 */

namespace Lamia\Validation\Validation\TypeValidation;

use Lamia\Validation\Exception\FieldValidationFailedException;

class StringValidation extends AbstractTypeValidation
{
    public function validate($name, $value, array $constraints)
    {
        $min = isset($constraints[self::MIN]) ? $constraints[self::MIN] : $this->getDefaults()[self::MIN];
        $max = isset($constraints[self::MAX]) ? $constraints[self::MAX] : $this->getDefaults()[self::MAX];
        $optional = isset($constraints[self::OPTIONAL]) ? $constraints[self::OPTIONAL] : $this->getDefaults()[self::OPTIONAL];
        $numeric = isset($constraints[self::NUMERIC]) ? $constraints[self::NUMERIC] : $this->getDefaults()[self::NUMERIC];
        $values = isset($constraints[self::VALUES]) ? $constraints[self::VALUES] : $this->getDefaults()[self::VALUES];
        return $this->validateString($name, $value, $min, $max, $optional, $numeric, $values);
    }

    /**
     * Validates a string field according to given parameters
     * @param string $name of the field to be validated 
     * @param $value mixed to be validated in string form
     * @param $lowerLimit int the lower limit of the length of the data. Must be greater than or equal 1
     * @param $upperLimit int the upper limit of the length of the data
     * @param $optional bool true if an empty string is also allowed, defaults to false
     * @param $numeric bool true if the string must only contain numeric information, defaults to true
     * @param $possibleValues array of possible values for string, defaults to null
     * @return string data, in cut format if cut.
     * @throws FieldValidationFailedException if validation failed for some reason
     */
    public function validateString(
        $name,
        $value,
        $lowerLimit,
        $upperLimit,
        $optional = false,
        $numeric = false,
        $possibleValues = null
    ) {
        if ($this->getUtils()->validateEmptiness($name, $value, $optional)) {
            return;
        }
        $this->validateIsString($name, $value);
        $this->getUtils()->validateInBounds($name, mb_strlen($value), $lowerLimit, $upperLimit);
        $this->validateNumeric($name, $value, $numeric);
        $this->validateValue($name, $value, $possibleValues);
    }

    /**
     * validate that data is of type string
     * @param $name
     * @param $value
     * @throws FieldValidationFailedException
     */
    private function validateIsString($name, $value)
    {
        if (!is_string($value)) {
            $this->getUtils()->validateNotArrayOrObject($name, $value, 'string');
            throw new FieldValidationFailedException($name, $value . " is not a string");
        }
    }
    
    private function validateNumeric($name, $value, $numeric)
    {
        if ($numeric === true && !is_numeric($value)) {
            throw new FieldValidationFailedException($name, "should be numeric, but is " . $value);
        }
    }

    private function validateValue($name, $value,  $possibleValues)
    {
        if (is_array($possibleValues) && in_array($value, $possibleValues) === false) {
            throw new FieldValidationFailedException(
                $name, 'The value ' . $value . ' should be in possible values but was not.'
            );
        }
    }
}
