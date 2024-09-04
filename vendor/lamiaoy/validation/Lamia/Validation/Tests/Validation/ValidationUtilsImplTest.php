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

use Lamia\Validation\Exception\FieldValidationFailedException;
use Lamia\Validation\Validation\ValidationUtilsImpl;
use phpDocumentor\Reflection\Types\Boolean;

class ValidationUtilsImplTest extends ValidationTest
{
    private $utils;

    public function setUp()
    {
        $this->utils = new ValidationUtilsImpl();
    }

    /**
     * @param $value
     * @dataProvider providerTestValidateNotArrayOrObjectException
     */
    public function testValidateNotArrayOrObjectException($value)
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->utils->validateNotArrayOrObject('', $value);
    }

    /**
     * @param $value
     * @dataProvider providerTestValidateNotArrayOrObjectException
     */
    public function testValdiateEmptinessNameArrayOrObjectFails($value)
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->utils->validateEmptiness($value, '', false);
    }

    public function providerTestValidateNotArrayOrObjectException()
    {
        return array(
            array(new Boolean()),
            array(array('1', '22'))
        );
    }

    /**
     * @param $value
     * @dataProvider providerTestValidateNotArrayOrObjectNoException
     */
    public function testValidateNotArrayOrObjectNoException($value)
    {
        $this->utils->validateNotArrayOrObject('', $value);
        $this->assertTrue(true);
    }

    public function providerTestValidateNotArrayOrObjectNoException()
    {
        return array(
            array(123),
            array(true),
            array(false),
            array(null),
            array(''),
            array('123'),
        );
    }

    public function testValidateLimitsPass()
    {
        $this->utils->validateLimits('', 1, 1);
        $this->assertTrue(true);
    }

    /**
     * @param $upper
     * @dataProvider providerTestValidateLimitsFail
     */
    public function testValidateLimitsUpperFail($upper)
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->utils->validateLimits('', 1, $upper);
    }

    /**
     * @param $lower
     * @dataProvider providerTestValidateLimitsFail
     */
    public function testValidateLimitsLowerFail($lower)
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->utils->validateLimits('', $lower, 1);
    }

    public function providerTestValidateLimitsFail()
    {
        return array(
            array(null),
            array(true),
            array(false),
            array(''),
            array('aa'),
            array(array()),
            array(new Boolean()),
        );
    }

    /**
     * @dataProvider providerTestValidateEmptiness
     */
    public function testValidateEmptinessPass($value)
    {
        $this->utils->validateEmptiness('', $value, true);
        $this->utils->validateEmptiness('', $value, false);
        $this->assertTrue(true);
    }

    public function providerTestValidateEmptiness()
    {
        return array(
            array(true),
            array(false),
            array('aa'),
            array(array()),
            array(new Boolean()),
            array(123),
        );
    }

    public function testValidateEmptinessPassNull()
    {
        $this->utils->validateEmptiness('', null, true);
        $this->assertTrue(true);
    }

    public function testValidateEmptinessStringPass()
    {
        $this->utils->validateEmptiness('', '', true);
        $this->assertTrue(true);
    }

    public function testValidateEmptinessFail()
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->utils->validateEmptiness('', null, false);
    }

    public function testValidateEmptinessStringFail()
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->utils->validateEmptiness('', '', false);
    }

    public function testValidateInBoundsPasses()
    {
        $this->utils->validateInBounds('aa', 2, 2, 2);
        $this->utils->validateInBounds('aa', 1, 0, 2);
        $this->assertTrue(true);
    }

    /**
     * @param $value
     * @dataProvider providerTestValidateLimitsFail
     */
    public function testValidateInBoundsValueNotIntException($value)
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->utils->validateInBounds('aa', $value, 1, 1);
    }

    /**
     * @param $value
     * @dataProvider providerTestValidateLimitsFail
     */
    public function testValidateInBoundsLowerLimitNotIntException($value)
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->utils->validateInBounds('aa', 1, $value, 1);
    }

    /**
     * @param $value
     * @dataProvider providerTestValidateLimitsFail
     */
    public function testValidateInBoundsUpperLimitNotIntException($value)
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->utils->validateInBounds('aa', 'aa', 1, 1, $value);
    }

    public function testValidateInBoundsValueUnderLowerLimitException()
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->utils->validateInBounds('aa', 0, 1, 3);
    }

    public function testValidateInBoundsValueOverUpperLimitException()
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->utils->validateInBounds('aa', 4, 1, 3);
    }
}
