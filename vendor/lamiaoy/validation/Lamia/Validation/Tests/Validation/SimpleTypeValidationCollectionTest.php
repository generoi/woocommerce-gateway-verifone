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

use Lamia\Validation\Validation\SimpleTypeValidationCollection;

class SimpleTypeValidationCollectionTest extends ValidationTest
{
    public function testAddingAndGetting()
    {
        $validationMock = $this->getMockBuilder('\Lamia\Validation\Validation\Interfaces\TypeValidation')->getMock();
        $collection = new SimpleTypeValidationCollection();
        $collection->add('string', $validationMock);
        $collection->add('int', $validationMock);
        $collection->add('asdf', $validationMock);

        $this->assertEquals($validationMock, $collection->get('string'));
        $this->assertEquals($validationMock, $collection->get('int'));
        $this->assertEquals($validationMock, $collection->get('asdf'));
    }

    public function testGettingUnAdded()
    {
        $collection = new SimpleTypeValidationCollection();
        $this->assertFalse($collection->get('string'));

        $validationMock = $this->getMockBuilder('\Lamia\Validation\Validation\Interfaces\TypeValidation')->getMock();
        $collection->add('string', $validationMock);
        $this->assertFalse($collection->get('int'));
    }
}
