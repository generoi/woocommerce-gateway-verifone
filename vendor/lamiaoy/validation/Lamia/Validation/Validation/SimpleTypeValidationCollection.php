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

use Lamia\Validation\Validation\Interfaces\TypeValidation;
use Lamia\Validation\Validation\Interfaces\TypeValidationCollection;

class SimpleTypeValidationCollection implements TypeValidationCollection
{
    private $validations;
    
    public function __construct()
    {
        $this->validations = array();
    }
    
    public function add($type, TypeValidation $validation)
    {
        $this->validations[$type] = $validation;
    }
    
    public function get($type)
    {
        if (isset($this->validations[$type]) === false) {
            return false;
        }
        return $this->validations[$type];
    }
}
