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
use Lamia\Validation\Validation\TypeValidation\DateValidation;

class DateValidationTest extends ValidationTest
{
    private $validation;
    private $utilsMock;
    
    public function setUp()
    {
        $this->utilsMock = $this->getMockBuilder('\Lamia\Validation\Validation\Interfaces\ValidationUtils')->getMock();
        $defaults = $this->getMockBuilder('\Lamia\Validation\Validation\Interfaces\ValidationDefaultValues')->getMock();
        $defaults->expects($this->once())->method('getValues')->will($this->returnValue(array('min' => 0, 'optional' => false, 'max' => 10000)));
        $this->validation = new DateValidation($this->utilsMock, $defaults);
    }

    /**
     * @param $date
     * @param $format
     * @dataProvider providerTestHappyCases
     */
    public function testHappyCases($date, $format)
    {
        $this->validation->validate('', $date, array('format' => $format));
        $this->assertTrue(true);
    }
    
    public function providerTestHappyCases()
    {
        return array(
            array('2016-01-01 01:01:01', 'Y-m-d H:i:s'),
            array('2010-12-11', 'Y-m-d'),
        );
    }

    /**
     * @param $format
     * @dataProvider providerTestInvalidFormatNotStringOrEmpty
     */
    public function testInvalidFormatNotStringOrEmpty($format)
    {
        $this->utilsMock->expects($this->once())->method('validateNotArrayOrObject')->with('aa', $format, 'format');
        $this->expectException(FieldValidationFailedException::class);
        $this->validation->validate('aa', '2016', array('format' => $format));
    }

    public function providerTestInvalidFormatNotStringOrEmpty()
    {
        return array(
            array(''),
            array(true),
            array(false),
            array(1),
        );
    }

    /**
     * @dataProvider providerTestInvalidFormatNotStringOrEmpty
     */
    public function testDateIsNotStringOrIsEmpty($date)
    {
        $this->utilsMock->expects($this->once())->method('validateNotArrayOrObject')->with('aa', $date, 'date');
        $this->expectException(FieldValidationFailedException::class);
        $this->validation->validate('aa', $date, array('format' => 'Y-m-d'));
    }

    /**
     * @param $date
     * @param $format
     * @dataProvider providerTestDateStringButNotValidFormat
     */
    public function testDateStringButNotValidFormat($date, $format)
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->validation->validate('', $date, array('format' => $format));
    }

    public function providerTestDateStringButNotValidFormat()
    {
        return array(
            array('2016-01-01 01:01:1', 'Y-m-d H:i:s'),
            array('2016-01-01 011:01:01', 'Y-m-d H:i:s'),
            array('2016-01:01 01:01:01', 'Y-m-d H:i:s'),
            array('2016-01-0101:01:01', 'Y-m-d H:i:s'),
            array('2016-01-01 01:01', 'Y-m-d H:i:s'),
            array('201a-01-01 01:01:01', 'Y-m-d H:i:s'),
        );
    }
}
