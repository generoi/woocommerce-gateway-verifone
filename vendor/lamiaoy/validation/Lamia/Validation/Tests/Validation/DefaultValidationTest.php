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
use Lamia\Validation\Validation\DefaultValidation;

class DefaultValidationTest extends ValidationTest
{
    private $validation;
    private $typeValidationMock;

    public function setUp()
    {
        $this->typeValidationMock = $this->getMockBuilder('\Lamia\Validation\Validation\Interfaces\TypeValidation')->getMock();
        $this->validation = new DefaultValidation($this->typeValidationMock, array('aaa' => array(), 'bbb' => array(), 'ccc' => array()));
    }

    public function testAllGetValidated()
    {
        $this->typeValidationMock->expects($this->exactly(3))->method('validate');
        $this->validation->validateFields(array('aaa' => 'aa', 'bbb' => 'bb', 'ccc' => 'cc'));
    }

    public function testInvalidNameThrowsException()
    {
        $this->expectException(FieldValidationFailedException::class);
        $this->validation->validateFields(array('eee' => 'aa'));
    }
}
