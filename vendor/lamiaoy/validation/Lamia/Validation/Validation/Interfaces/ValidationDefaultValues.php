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
 * Interface ValidationDefaultValues
 * @package Lamia\Validation\Validation\Interfaces
 */
interface ValidationDefaultValues
{
    /**
     * @return array of default values for validation constraints
     */
    public function getValues();
}
