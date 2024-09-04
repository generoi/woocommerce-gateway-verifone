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
use Lamia\Validation\Validation\TypeValidation\ArrayValidation;
use phpDocumentor\Reflection\Types\Boolean;

class ArrayValidationTest extends ValidationTest
{
    private $validation;
    private $utilsMock;
    private $defaults;

    public function setUp()
    {
        $this->utilsMock = $this->getMockBuilder('\Lamia\Validation\Validation\Interfaces\ValidationUtils')->getMock();
        $this->defaults = $this->getMockBuilder('\Lamia\Validation\Validation\Interfaces\ValidationDefaultValues')->getMock();
        $this->defaults->expects($this->once())->method('getValues')->will($this->returnValue(array('min' => 0, 'optional' => false, 'max' => 10000)));
        $this->validation = new ArrayValidation($this->utilsMock, $this->defaults);
    }

    /**
     * @param $notArray
     * @dataProvider providerTestNotArraysFail
     */
    public function testNotArraysFail($notArray)
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->validation->validate('', $notArray, array());
    }

    public function providerTestNotArraysFail()
    {
        return array(
            array(null),
            array(''),
            array(true),
            array(false),
            array(new Boolean()),
            array('aa'),
            array(1),
        );
    }

    public function testArrayBaseCase()
    {
        $this->validation->validate('', array(), array());
        $this->assertTrue(true);
    }

    public function testEmptinessTrueStopsExecution()
    {
        $this->utilsMock->expects($this->once())
            ->method('validateEmptiness')->with('', null, true)->will($this->returnValue(true));
        $this->validation->validate('', null, array('min' => 1, 'optional' => true));
        $this->assertTrue(true);
    }

    public function testArrayCallsInBoundsWithRightParameters()
    {
        $this->utilsMock->expects($this->once())
            ->method('validateInBounds')->with('aa', 'array length', 3, 2, 3)->will($this->returnValue(true));
        $this->validation->validate('aa', array('aa', 'bb', 'cc'), array('min' => 2, 'max' => 3));
        $this->assertTrue(true);
    }

    public function testArrayCallsInBoundsWithRightParameters2()
    {
        $this->utilsMock->expects($this->once())
            ->method('validateInBounds')->with('aa', 'array length', 3, 0, 10000)->will($this->returnValue(true));
        $this->validation->validate('aa', array('aa', 'bb', 'cc'), array());
        $this->assertTrue(true);
    }
}
