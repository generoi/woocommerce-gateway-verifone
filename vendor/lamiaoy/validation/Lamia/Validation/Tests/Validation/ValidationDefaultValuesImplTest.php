<?php
/**
 * NOTICE OF LICENSE 
 *
 * This source file is released under commercial license by Lamia Oy. 
 *
 * @copyright  Copyright (c) 2017 Lamia Oy (https://lamia.fi) 
 * @author     Irina Mäkipaja <irina@lamia.fi>
 */

namespace Lamia\Validation\Tests\Validation;

use Lamia\Validation\Validation\ValidationDefaultValuesImpl;

class ValidationDefaultValuesImplTest extends ValidationTest
{
    public function testGetters()
    {
        $defaults = new ValidationDefaultValuesImpl('Lamia/Validation/Tests/Validation/testValidationDefaults.php');
        $this->assertEquals(6, count($defaults->getValues()));
    }
}
