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
use Lamia\Validation\Validation\TypeValidation\StringValidation;

class StringValidationTest extends ValidationTest
{
    private $validation;
    private $utilsMock;

    public function setUp()
    {
        $this->utilsMock = $this->getMockBuilder('\Lamia\Validation\Validation\Interfaces\ValidationUtils')->getMock();
        $defaults = $this->getMockBuilder('\Lamia\Validation\Validation\Interfaces\ValidationDefaultValues')->getMock();
        $defaults->expects($this->once())->method('getValues')->will($this->returnValue(array('min' => 0, 'optional' => false, 'max' => 10000, 'numeric' => false, 'values' => null)));
        $this->validation = new StringValidation($this->utilsMock, $defaults);
    }

    public function testValidateBaseCase()
    {
        $this->validation->validate('', 'value', array());
        $this->assertTrue(true);
    }

    /**
     * @param $value
     * @dataProvider providerTestValidateFailCases
     */
    public function testValidateFailCases($value)
    {
        $this->utilsMock->expects($this->once())->method('validateNotArrayOrObject')->with('aa', $value, 'string');
        $this->utilsMock->expects($this->once())->method('validateEmptiness')->with('aa', $value, false);
        $this->expectException(FieldValidationFailedException::class);
        $this->validation->validate('aa', $value, array());
    }

    public function providerTestValidateFailCases()
    {
        return array(
            array(true),
            array(false),
            array(null),
            array(123),
        );
    }

    public function testLimitsCheckCalledWithRightParameters()
    {
        $this->utilsMock->expects($this->once())->method('validateInBounds')->with('aa', 4, 1, 5);
        $this->validation->validate('aa', 'aaaa', array('min' => 1, 'max' => 5));
        $this->assertTrue(true);
    }

    public function testLimitsCheckCalledWithRightParameters2()
    {
        $this->utilsMock->expects($this->once())->method('validateInBounds')->with('aa', 5, 1, 5);
        $this->validation->validate('aa', 'äääää', array('min' => 1, 'max' => 5));
        $this->assertTrue(true);
    }


    public function testFailsWhenNumericTrueButValueNotNumeric()
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->validation->validate('', '123a', array('numeric' => true));
    }

    public function testWorksWhenNumericAndValueNumeric()
    {
        $this->validation->validate('', '123', array('numeric' => true));
        $this->assertTrue(true);
    }

    public function testNotInPossibleValues()
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->validation->validate('', '123', array('values' => array()));
    }

    public function testInPossibleValuesWorks()
    {
        $this->validation->validate('', '123', array('values' => array('123', 'ab', 'cd')));
        $this->assertTrue(true);
    }
}
