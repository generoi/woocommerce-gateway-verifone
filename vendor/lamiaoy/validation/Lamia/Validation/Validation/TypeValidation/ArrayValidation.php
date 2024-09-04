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

class ArrayValidation extends AbstractTypeValidation
{
    public function validate($name, $value, array $constraints)
    {
        $min = isset($constraints[self::MIN]) ? $constraints[self::MIN] : $this->getDefaults()[self::MIN];
        $max = isset($constraints[self::MAX]) ? $constraints[self::MAX] : $this->getDefaults()[self::MAX];
        $optional = isset($constraints[self::OPTIONAL]) ? $constraints[self::OPTIONAL] : $this->getDefaults()[self::OPTIONAL];
        
        if ($this->getUtils()->validateEmptiness($name, $value, $optional)) {
            return;
        }
        if (is_array($value) === false) {
            throw new FieldValidationFailedException($name, ' must be valid array');
        }
        $this->getUtils()->validateInBounds($name, 'array length', count($value), $min, $max);
    }
}
