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

use Lamia\Validation\Exception\FieldValidationFailedException;

/**
 * The general validation interface to be used in project
 * Interface Validation
 * @package Lamia\Validation\Validation\Interfaces
 */
interface Validation
{
    /**
     * Validation constructor.
     * @param TypeValidation $validation to validate the the fields with
     * @param array $configuration with validation configuration of fields
     */
    public function __construct(TypeValidation $validation, array $configuration);

    /**
     * Validates fields gives as parameter
     * @param array $fields to be validated
     * @throws FieldValidationFailedException if the validation fails
     */
    public function validateFields(array $fields);
}
