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


class DefaultValidation extends AbstractValidation
{
    protected function afterValidation($name, $value, array $constraints)
    {
        // Does nothing
    }
    
    protected function getConfigKey($name)
    {
        return $name;
    }
}
