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

use Lamia\Validation\Validation\Interfaces\TypeValidation;
use Lamia\Validation\Validation\Interfaces\ValidationDefaultValues;
use Lamia\Validation\Validation\Interfaces\ValidationUtils;

abstract class AbstractTypeValidation implements TypeValidation
{
    const MIN = 'min';
    const MAX = 'max';
    const FORMAT = 'format';
    const OPTIONAL = 'optional';
    const NUMERIC = 'numeric';
    const VALUES = 'values';
    
    private $utils;
    private $defaults;
    
    public function __construct(ValidationUtils $utils, ValidationDefaultValues $defaults)
    {
        $this->defaults = $defaults->getValues();
        $this->utils = $utils;
    }
    
    protected function getDefaults()
    {
        return $this->defaults;
    }
    
    protected function getUtils()
    {
        return $this->utils;
    }
}
