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

use \Exception;
use Lamia\Validation\Exception\FieldValidationFailedException;
use Lamia\Validation\Validation\Interfaces\TypeValidation;
use Lamia\Validation\Validation\Interfaces\TypeValidationCollection;

class GeneralTypeValidation implements TypeValidation
{
    const TYPE = 'type';

    private $validations;
    private $defaultValidation;
    
    public function __construct(TypeValidationCollection $validations, $defaultValidation)
    {
        if ($validations->get($defaultValidation) === false) {
            throw new Exception(
                'Creating GeneralValidation failed because given ValidationCollection does not contain given default validation of type ' . $defaultValidation
            );
        }
        $this->defaultValidation = $defaultValidation;
        $this->validations = $validations;
    }

    public function validate($name, $value, array $constraints)
    {
        $type = isset($constraints[self::TYPE]) ? $constraints[self::TYPE] : $this->defaultValidation;

        $validation = $this->validations->get($type);
        if ($validation === false) {
            throw new FieldValidationFailedException($name, "can't find validation implementation for type " . $type);
        }

        $validation->validate($name, $value, $constraints);
    }
}
