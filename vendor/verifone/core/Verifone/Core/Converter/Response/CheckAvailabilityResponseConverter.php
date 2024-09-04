<?php
/**
 * NOTICE OF LICENSE 
 *
 * This source file is released under commercial license by Lamia Oy. 
 *
 * @copyright  Copyright (c) 2017 Lamia Oy (https://lamia.fi) 
 * @author     Irina Mäkipaja <irina@lamia.fi>
 */

namespace Verifone\Core\Converter\Response;

use Verifone\Core\DependencyInjection\Transporter\CoreResponse;
use Verifone\Core\DependencyInjection\Transporter\TransportationResponse;

class CheckAvailabilityResponseConverter extends CoreResponseConverter
{
    const AVAILABILITY_FIELD = 'i-f-1-1_availability';

    public function convert(TransportationResponse $response)
    {
        $originalFields = $response->getBody();
        $content = $originalFields[self::AVAILABILITY_FIELD];
        return new CoreResponse(CoreResponseConverter::STATUS_OK, $content);
    }
}
