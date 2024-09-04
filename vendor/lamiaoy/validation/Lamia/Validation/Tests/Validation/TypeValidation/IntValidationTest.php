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
use Lamia\Validation\Validation\TypeValidation\IntValidation;

class IntValidationTest extends ValidationTest
{
    private $validation;
    private $utilsMock;

    public function setUp()
    {
        $this->utilsMock = $this->getMockBuilder('\Lamia\Validation\Validation\Interfaces\ValidationUtils')->getMock();
        $defaults = $this->getMockBuilder('\Lamia\Validation\Validation\Interfaces\ValidationDefaultValues')->getMock();
        $defaults->expects($this->once())->method('getValues')->will($this->returnValue(array('min' => 0, 'optional' => false, 'max' => 10000)));
        $this->validation = new IntValidation($this->utilsMock, $defaults);
    }

    /**
     * @param $value
     * @dataProvider providerTestHappyCases
     */
    public function testHappyCases($value)
    {
        $this->validation->validate('', $value, array('aa' => 'a'));
        $this->assertTrue(true);
    }

    public function providerTestHappyCases()
    {
        return array(
            array(0),
            array(1000),
        );
    }

    public function testLimitsCheckCalledWithRightParameters()
    {
        $this->utilsMock->expects($this->once())->method('validateInBounds')->with('aa', 'int value', 1, 1, 2);
        $this->validation->validate('aa', 1, array('min' => 1, 'max' => 2));
        $this->assertTrue(true);
    }

    /**
     * @param $value
     * @dataProvider providerTestSadCases
     */
    public function testSadCases($value)
    {
        $this->utilsMock->expects($this->once())->method('validateNotArrayOrObject')->with('aa', $value, 'int');
        $this->expectException(FieldValidationFailedException::class);
        $this->validation->validate('aa', $value, array());
    }


    public function providerTestSadCases()
    {
        return array(
            array(''),
            array(true),
            array(false),
            array('1'),
            array(null),
        );
    }
}
