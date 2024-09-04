<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is released under commercial license by Lamia Oy.
 *
 * @copyright  Copyright (c) 2017 Lamia Oy (https://lamia.fi)
 * @author     Irina Mäkipaja <irina@lamia.fi>
 */

namespace Lamia\Validation;


use Lamia\Validation\Exception\ValidationCreationFailedException;
use Lamia\Validation\Validation\TypeValidation\ArrayValidation;
use Lamia\Validation\Validation\TypeValidation\BooleanValidation;
use Lamia\Validation\Validation\TypeValidation\DateValidation;
use Lamia\Validation\Validation\TypeValidation\GeneralTypeValidation;
use Lamia\Validation\Validation\Interfaces\TypeValidation;
use Lamia\Validation\Validation\Interfaces\TypeValidationCollection;
use Lamia\Validation\Validation\Interfaces\ValidationDefaultValues;
use Lamia\Validation\Validation\Interfaces\ValidationUtils;
use Lamia\Validation\Validation\TypeValidation\IntValidation;
use Lamia\Validation\Validation\TypeValidation\StringValidation;
use Lamia\Validation\Validation\ValidationDefaultValuesImpl;

class ValidationContainer
{
    const COLLECTION = 'collection.class';
    const UTILS = 'utils.class';
    const DEFAULT_TYPE = 'defaultType';
    const DEFAULTS_PATH = 'defaultsPath';
    const DONT_USE_DEFAULT_VALIDATIONS = 'dontUseDefaultValidations';
    const PROJECT_VALIDATION = 'validation.class';
    const DEFAULTS_PATH_VALUE = 'vendor/lamia/validation/Lamia/Validation/Configuration/validationDefaults.php';

    private $parameters = array();
    private $implementations = array();
    private $fieldConfiguration;
    
    public function __construct(array $fieldConfiguration, array $parameters = array())
    {
        $this->fieldConfiguration = $fieldConfiguration;
        $this->parameters = $parameters;
        $this->setDefaultParameters();
    }

    private function setDefaultParameters()
    {
        if (!isset($this->parameters[self::DEFAULTS_PATH])) {
            $this->parameters[self::DEFAULTS_PATH] = self::DEFAULTS_PATH_VALUE;
        }
        if (!isset($this->parameters[self::COLLECTION])) {
            $this->parameters[self::COLLECTION] = '\Lamia\Validation\Validation\SimpleTypeValidationCollection';
        }
        if (!isset($this->parameters[self::UTILS])) {
            $this->parameters[self::UTILS] = '\Lamia\Validation\Validation\ValidationUtilsImpl';
        }
        if (!isset($this->parameters[self::DEFAULT_TYPE])) {
            $this->parameters[self::DEFAULT_TYPE] = 'string';
        }
        if (!isset($this->parameters[self::PROJECT_VALIDATION])) {
            $this->parameters[self::PROJECT_VALIDATION] = '\Lamia\Validation\Validation\DefaultValidation';
        }
    }

    public function getValidation()
    {
        $typeValidation = $this->getTypeValidation();
        $projectValidationName = $this->getAndValidateClassName(self::PROJECT_VALIDATION);
        return new $projectValidationName($typeValidation, $this->fieldConfiguration);
    }
    
    private function getTypeValidation()
    {
        $validationCollection = $this->getValidationCollection();
        return new GeneralTypeValidation($validationCollection, $this->parameters[self::DEFAULT_TYPE]);
    }

    public function getDefaults()
    {
        if (isset($this->implementations['defaults'])) {
            return $this->implementations['defaults'];
        }
        $defaults = new ValidationDefaultValuesImpl($this->parameters[self::DEFAULTS_PATH]);
        return $this->implementations['defaults'] = $defaults;
    }

    public function getValidationUtils()
    {
        if (isset($this->implementations[self::UTILS])) {
            return $this->implementations[self::UTILS];
        }
        $utils = $this->getAndValidateClassWithNoParameters(self::UTILS);
        return $this->implementations[self::UTILS] = $utils;
    }

    public function addTypeValidation($name, TypeValidation $typeValidation)
    {
        $collection = $this->getValidationCollection();
        $collection->add($name, $typeValidation);
    }
    
    private function getValidationCollection()
    {
        if (isset($this->implementations[self::COLLECTION])) {
            return $this->implementations[self::COLLECTION];
        }
        return $this->implementations[self::COLLECTION] = $this->getNewValidationCollection();
    }

    private function getNewValidationCollection()
    {
        $collection = $this->getAndValidateClassWithNoParameters(self::COLLECTION);
        $utils = $this->getValidationUtils();
        $defaults = $this->getDefaults();
        if (!isset($this->parameters[self::DONT_USE_DEFAULT_VALIDATIONS])) {
            $this->addDefaultValidations($collection, $utils, $defaults);
        }
        return $collection;
    }

    private function addDefaultValidations(
        TypeValidationCollection $collection,
        ValidationUtils $utils,
        ValidationDefaultValues $defaults
    ) {
        $collection->add('string', new StringValidation($utils, $defaults));
        $collection->add('int', new IntValidation($utils, $defaults));
        $collection->add('array', new ArrayValidation($utils, $defaults));
        $collection->add('date', new DateValidation($utils, $defaults));
        $collection->add('boolean', new BooleanValidation($utils, $defaults));
    }
    
    private function getAndValidateClassWithNoParameters($name)
    {
        $fullName = $this->getAndValidateClassName($name);
        return new $fullName();
    }

    private function getAndValidateClassName($parameter)
    {
        $className = $this->parameters[$parameter];
        if (!class_exists($className)) {
            throw new ValidationCreationFailedException('Given class ' . $className . ' does not exist');
        }
        return $className;
    }
}
