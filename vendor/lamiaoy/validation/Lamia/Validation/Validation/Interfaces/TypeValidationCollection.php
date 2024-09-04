<?php
/**
 * NOTICE OF LICENSE 
 *
 * This source file is released under commercial license by Lamia Oy. 
 *
 * @copyright  Copyright (c) 2017 Lamia Oy (https://lamia.fi) 
 * @author     Irina Mäkipaja <irina@lamia.fi>
 */

namespace Lamia\Validation\Validation\Interfaces;

/**
 * A collection of TypeValidatiosn
 * Interface TypeValidationCollection
 * @package Lamia\Validation\Validation\Interfaces
 */
interface TypeValidationCollection
{
    /**
     * @param string $type name to be mapped to TypeValidation (typically used as a type name in field conf)
     * @param TypeValidation $validation
     */
    public function add($type, TypeValidation $validation);

    /**
     * @param string $type name
     * @return TypeValidation corresponding to given $type
     */
    public function get($type);
}
