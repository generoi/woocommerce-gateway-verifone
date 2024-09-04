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

use Lamia\Validation\Validation\Interfaces\ValidationDefaultValues;

class ValidationDefaultValuesImpl implements ValidationDefaultValues
{
    private $defaults = array(
        "min" => 0,
        "max" => 10000,
        "format" => 'Y-m-d H:i:s',
        "optional" => false,
        "numeric" => false,
        "values" => null
    );

    public function getValues()
    {
        return $this->defaults;
    }
}
