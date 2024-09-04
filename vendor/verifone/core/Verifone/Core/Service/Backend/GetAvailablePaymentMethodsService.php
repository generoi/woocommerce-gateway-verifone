<?php
/**
 * NOTICE OF LICENSE 
 *
 * This source file is released under commercial license by Lamia Oy. 
 *
 * @copyright  Copyright (c) 2017 Lamia Oy (https://lamia.fi) 
 * @author     Irina Mäkipaja <irina@lamia.fi>
 */

namespace Verifone\Core\Service\Backend;

use Verifone\Core\Configuration\FieldConfigImpl;
use Verifone\Core\Converter\Response\ResponseConverter;
use Verifone\Core\DependencyInjection\Configuration\Backend\GetAvailablePaymentMethodsConfiguration;
use Verifone\Core\DependencyInjection\CryptUtils\CryptUtil;
use Verifone\Core\Storage\Storage;

/**
 * Class GetAvailablePaymentMethodsService
 * @package Verifone\Core\Service\Backend
 * 
 * A service for getting a list of available payment methods
 */
final class GetAvailablePaymentMethodsService extends AbstractBackendService
{
    const OPERATION_VALUE = 'list-payment-methods';

    /**
     * GetAvailablePaymentMethodsService constructor.
     * @param Storage $storage
     * @param GetAvailablePaymentMethodsConfiguration $configuration
     * @param CryptUtil $crypto
     * @param ResponseConverter $responseConverter
     */
    public function __construct(
        Storage $storage,
        GetAvailablePaymentMethodsConfiguration $configuration,
        CryptUtil $crypto,
        ResponseConverter $responseConverter
    ) {
        parent::__construct($storage, $configuration, $crypto, $responseConverter);
        $this->addToStorage(FieldConfigImpl::CONFIG_CURRENCY, $configuration->getCurrency());
        $this->addToStorage(FieldConfigImpl::OPERATION, self::OPERATION_VALUE);
    }
}
