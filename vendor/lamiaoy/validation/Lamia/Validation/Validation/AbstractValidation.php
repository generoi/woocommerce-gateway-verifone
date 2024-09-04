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
use Lamia\Validation\Validation\Interfaces\TypeValidation;
use Lamia\Validation\Validation\Interfaces\Validation;

abstract class AbstractValidation implements Validation
{
    private $validation;
    private $configuration;

    public function __construct(TypeValidation $validation, array $configuration)
    {
        $this->validation = $validation;
        $this->configuration = $configuration;
    }

    public function validateFields(array $fields)
    {
        foreach ($fields as $name => $value) {
            $this->validateField($name, $value);
        }
    }

    private function validateField($name, $value)
    {
        if (isset($this->configuration[$name]) === false) {
            // if project has a special way of converting some keys like countables etc, try to get that one
            $name = $this->tryGetSpecialKey($name);
        }
        $constraints = $this->configuration[$name];
        $this->validation->validate($name, $value, $constraints);
        $this->afterValidation($name, $value, $constraints);
    }

    private function tryGetSpecialKey($name)
    {
        $name = $this->getConfigKey($name);
        if (isset($this->configuration[$name]) === false) {
            throw new FieldValidationFailedException($name, 'could not find field name in configuration');
        }
        return $name;
    }

    abstract protected function afterValidation($name, $value, array $constraints);

    abstract protected function getConfigKey($name);
}
