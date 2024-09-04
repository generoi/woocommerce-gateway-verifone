<?php
/**
 * NOTICE OF LICENSE 
 *
 * This source file is released under commercial license by Lamia Oy. 
 *
 * @copyright  Copyright (c) 2017 Lamia Oy (https://lamia.fi) 
 * @author     Irina Mäkipaja <irina@lamia.fi>
 */

namespace Lamia\Validation\Exception;

use \Exception;

class ValidationCreationFailedException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message, 0, null);
    }
}
