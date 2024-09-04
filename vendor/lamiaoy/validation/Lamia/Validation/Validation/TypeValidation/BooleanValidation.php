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

class BooleanValidation extends AbstractTypeValidation
{
    public function validate($name, $value, array $constraints)
    {
        if (is_bool($value) === false) {
            $this->getUtils()->validateNotArrayOrObject($name, $value, 'boolean');
            throw new FieldValidationFailedException($name, ' value should have been boolean but was ' . $value);
        }
    }
}
