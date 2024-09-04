<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is released under commercial license by Lamia Oy.
 *
 * @copyright Copyright (c) 2017 Lamia Oy (https://lamia.fi)
 * @author    Szymon Nosal <simon@lamia.fi>
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class WC_Verifone_Data
{

    const BASKET_LIMIT = 48;

    public static function collectData($orderId, $paymentMethod = null, $savePaymentMethod = null)
    {
        /** @var WC_Order $order */
        $order = new WC_Order($orderId);

        $data = self::collectOrderData($order, $paymentMethod, $savePaymentMethod);

        $products = self::_collectProductsData($order, $paymentMethod, $savePaymentMethod);
        $customer = self::collectCustomerData($order);
        $address = self::collectBillingAddressData($order);
        $shippingAddress = self::collectShippingAddressData($order);

        if (self::_isCombineBasketItems()) {
            $product = $products[0];
            $data['total_excl_amount'] = $product['net_amount'];
            $data['total_incl_amount'] = $product['gross_amount'];
            $data['total_tax'] = $product['gross_amount'] - $product['net_amount'];
        }

        $data['products'] = $products;
        $data['customer'] = $customer;
        $data['address'] = $address;
        $data['shipping_address'] = $shippingAddress;

        return $data;
    }

    /** CUSTOMER */
    /**
     * @param WC_Order|null $order
     * @return array|null
     */
    public static function collectCustomerData(WC_Order $order = null)
    {
        if ($order === null) {

            if (!get_current_user_id()) {
                return null;
            }

            $customer = new WC_Customer(get_current_user_id());

            $customerData = [
                'firstname' => $customer->get_billing_first_name(),
                'lastname' => $customer->get_billing_last_name(),
                'phone' => $customer->get_billing_phone(),
                'email' => $customer->get_billing_email(),
                'address' => self::collectCustomerAddress($customer)
            ];
            $customerId = $customer->get_id();

        } else {
            $customerData = [
                'firstname' => $order->get_billing_first_name(),
                'lastname' => $order->get_billing_last_name(),
                'phone' => $order->get_billing_phone(),
                'email' => $order->get_billing_email()
            ];
            $customerId = $order->get_customer_id();
        }

        if (empty($customerData['firstname'])) {
            $customerData['firstname'] = '?';
        }
        if (empty($customerData['lastname'])) {
            $customerData['lastname'] = '?';
        }
        if (empty($customerData['phone'])) {
            $customerData['phone'] = '?';
        }

        // set external customer id if available
        $config = WC_Verifone_Config::getInstance();
        if (!empty($config->getExternalCustomerIdField())) {
            $externalData = get_user_meta($customerId, $config->getExternalCustomerIdField(), true);
            if (!empty($externalData)) {
                $customerData['external_id'] = $externalData;
            }
        }

        return $customerData;
    }

    public static function collectBillingAddressData(WC_Order $order)
    {
        $addressData = [
            'address_1' => $order->get_billing_address_1(),
            'address_2' => $order->get_billing_address_2(),
            'city' => $order->get_billing_city(),
            'postcode' => $order->get_billing_postcode(),
            'country' => $order->get_billing_country(),
            'firstname' => $order->get_billing_first_name(),
            'lastname' => $order->get_billing_last_name(),
            'telephone' => $order->get_billing_phone(),
            'email' => $order->get_billing_email(),
        ];

        $addressData = self::fillEmptyAddressData($addressData);

        $addressData['country_code'] = WC_Verifone_System::convertCountryCode2Numeric($addressData['country']);

        return $addressData;
    }

    public static function collectShippingAddressData(WC_Order $order)
    {

        if (!$order->has_shipping_address()) {
            return null;
        }

        $addressData = [
            'address_1' => $order->get_shipping_address_1(),
            'address_2' => $order->get_shipping_address_2(),
            'city' => $order->get_shipping_city(),
            'postcode' => $order->get_shipping_postcode(),
            'country' => $order->get_shipping_country(),
            'firstname' => $order->get_shipping_first_name(),
            'lastname' => $order->get_shipping_last_name()
        ];

        $addressData = self::fillEmptyAddressData($addressData);

        $addressData['country_code'] = WC_Verifone_System::convertCountryCode2Numeric($addressData['country']);

        return $addressData;
    }

    private static function fillEmptyAddressData($addressData)
    {
        if (empty($addressData['address_1'])) {
            $addressData['address_1'] = '?';
        }
        if (empty($addressData['city'])) {
            $addressData['city'] = '?';
        }
        if (empty($addressData['postcode'])) {
            $addressData['postcode'] = '?';
        }
        if (empty($addressData['firstname'])) {
            $addressData['firstname'] = '?';
        }
        if (empty($addressData['lastname'])) {
            $addressData['lastname'] = '?';
        }

        return $addressData;
    }

    public static function collectCustomerAddress(WC_Customer $customer)
    {

        if (
            $customer->get_billing_first_name() &&
            $customer->get_billing_last_name() &&
            $customer->get_billing_country() &&
            $customer->get_billing_postcode() &&
            $customer->get_billing_city() &&
            $customer->get_billing_address_1()
        ) {
            $addressData = [
                'address_1' => $customer->get_billing_address_1(),
                'address_2' => $customer->get_billing_address_2(),
                'city' => $customer->get_billing_city(),
                'postcode' => $customer->get_billing_postcode(),
                'country' => $customer->get_billing_country(),
                'firstname' => $customer->get_billing_first_name(),
                'lastname' => $customer->get_billing_last_name()
            ];

            $addressData = self::fillEmptyAddressData($addressData);

            $addressData['country_code'] = WC_Verifone_System::convertCountryCode2Numeric($addressData['country']);

            return $addressData;
        }

        return null;
    }

    /** ORDER */

    protected static $_paymentMethod = null;
    protected static $_savePaymentMethod = null;

    /**
     * @param WC_Order $order
     * @param null $paymentMethod
     * @param null $savePaymentMethod
     * @return array
     */
    public static function collectOrderData(WC_Order $order, $paymentMethod = null, $savePaymentMethod = null)
    {
        $config = WC_Verifone_Config::getInstance();
        self::$_paymentMethod = $paymentMethod;
        self::$_savePaymentMethod = $savePaymentMethod;

        /** @var WC_DateTime $dateCreated */
        $dateCreated = $order->get_date_created();

        if ($dateCreated) {
            $dateTime = $dateCreated->date_i18n('Y-m-d H:i:s');
        } else {
            $dateTime = gmdate('Y-m-d H:i:s');
        }

        $paymentMethodId = null;

        // get Data for saved payment method.
        // Saved payment method contains letters and number, but payment method contains just numbers and "-" char
        if (!empty(self::$_paymentMethod) && !ctype_alpha(self::$_paymentMethod) && strpos(self::$_paymentMethod, '-') === false) {

            $tokens = WC_Payment_Tokens::get_customer_tokens(get_current_user_id());

            $token = null;

            /** @var WC_Payment_Token_CC $item */
            foreach ($tokens as $item) {
                if ($item->get_token() == self::$_paymentMethod) {
                    $token = $item;
                }
            }

            // if for some reason token is wrong, then display all methods
            if ($token == null) {
                self::$_paymentMethod = '';
            } else {
                self::$_paymentMethod = $token->get_card_type();
                $paymentMethodId = $token->get_token();
            }

        }

        if (self::$_paymentMethod == WC_Verifone_PaymentMethods::TYPE_ALL) {
            self::$_paymentMethod = '';
        }

        if (self::$_savePaymentMethod == 'on' || self::$_savePaymentMethod == true) {
            self::$_savePaymentMethod = true;
        } else {
            self::$_savePaymentMethod = false;
        }

        $data = [
            'currency_code' => WC_Verifone_System::getCurrencyNumber(),
            'total_incl_amount' => self::_getOrderGrossAmount($order),
            'total_excl_amount' => self::_getOrderNetAmount($order),
            'total_tax' => self::_getOrderTaxAmount($order),
            'order_id' => $order->get_id(),
            'ext_order_id' => $order->get_meta('ext_order_id'),
            'time' => $dateTime,
            'locale' => WC_Verifone_System::getCustomerLocale(),
            'payment_method' => self::$_paymentMethod,
            'save_payment_method' => self::$_savePaymentMethod
        ];

        if ($paymentMethodId) {
            $data['payment_method_id'] = $paymentMethodId;
        }

        return $data;
    }

    /**
     * @param WC_Order $order
     * @param null $paymentMethod
     * @param null $savePaymentMethod
     * @return array
     */
    protected static function _collectProductsData(WC_Order $order, $paymentMethod = null, $savePaymentMethod = null)
    {
        $products = [];

        if (!self::_isSendBasketItems()) {
            return $products;
        }

        self::$_paymentMethod = $paymentMethod;
        self::$_savePaymentMethod = $savePaymentMethod;

        $orderItems = $order->get_items();

        // Group by tax if more products than limit
        if (count($orderItems) >= self::BASKET_LIMIT) {
            $orderItems = self::_groupItemsByTax($orderItems);
        }

        $itemsTax = $itemsNetPrice = $itemsGrossPrice = 0;

        /**
         * @var $orderItem WC_Order_Item_Product
         */
        foreach ($orderItems as $orderItem) {
            $product = self::_getBasketItemData($orderItem);

            if (!self::_isCombineBasketItems()) {
                $products[] = $product;
            }

            $itemsTax += $product['gross_amount'] - $product['net_amount'];
            $itemsNetPrice += $product['net_amount'];
            $itemsGrossPrice += $product['gross_amount'];
        }

        $shippingData = self::_getShippingData($order);

        if (!is_null($shippingData)) {

            if (!self::_isCombineBasketItems()) {
                $products[] = $shippingData;
            }

            $itemsTax += $shippingData['gross_amount'] - $shippingData['net_amount'];
            $itemsNetPrice += $shippingData['net_amount'];
            $itemsGrossPrice += $shippingData['gross_amount'];

        }

        // add discount product
        $discountAmount = self::_getOrderGrossAmount($order) - $itemsGrossPrice;
        if (abs($discountAmount) >= 1) {
            $discountProduct = self::_getBasketDiscountData(self::_getOrderGrossAmount($order), $itemsGrossPrice,
                self::_getOrderNetAmount($order), $itemsNetPrice);

            $itemsNetPrice += $discountProduct['net_amount'];
            $itemsGrossPrice += $discountProduct['gross_amount'];

            if (!self::_isCombineBasketItems()) {
                $products[] = $discountProduct;
            }
        }


        // combine basket items
        if (self::_isCombineBasketItems()) {
            $products[] = self::_getCombinedBasketItemsData($order, $itemsGrossPrice, $itemsNetPrice, $itemsTax);
        }

        return $products;
    }

    /**
     * @param WC_Order_Item_Product $orderItem
     *
     * @return array
     */
    protected static function _getBasketItemData(WC_Order_Item_Product $orderItem)
    {

        $itemCount = $orderItem->get_quantity();

        $totalAmount = $orderItem->get_subtotal();
        $totalTax = $orderItem->get_subtotal_tax();

        $itemTaxPercentage = self::_calculateTaxPercentage($totalAmount, $totalTax);
        $itemGross = (float)$totalAmount + (float)$totalTax;

        $itemNet = $itemGross / (1 + ($itemTaxPercentage / 100)) / $itemCount;
        $totalNetAmount = round($itemNet, 2) * $itemCount;

        return [
            'name' => $orderItem->get_name(),
            'unit_count' => $itemCount,
            'unit_cost' => round($itemNet, 2) * 100,
            'net_amount' => $totalNetAmount * 100,
            'gross_amount' => round($itemGross, 2) * 100,
            'tax_percentage' => round($itemTaxPercentage, 2) * 100,
            'discount_percentage' => 0
        ];
    }

    protected static function _getShippingData(WC_Order $order)
    {
        $shippingAmount = (float)$order->get_shipping_total();
        if ($shippingAmount) {

            $itemNet = $order->get_shipping_total();
            $itemTax = $order->get_shipping_tax();

            $itemTaxPercentage = self::_calculateTaxPercentage($itemNet, $itemTax);
            $itemGross = (float)$itemNet + (float)$itemTax;

            $name = __('Shipping via', WC_VERIFONE_DOMAIN) . ' ' . ucwords($order->get_shipping_method());

            return [
                'name' => $name,
                'unit_count' => 1,
                'unit_cost' => round($itemNet, 2) * 100,
                'net_amount' => round($itemNet, 2) * 100,
                'gross_amount' => round($itemGross, 2) * 100,
                'tax_percentage' => round($itemTaxPercentage, 2) * 100,
                'discount_percentage' => 0
            ];
        }

        return null;
    }

    protected static function _getCombinedBasketItemsData(WC_Order $order, $itemsGrossPrice, $itemsNetPrice, $itemsTax = 0)
    {
        $config = WC_Verifone_Config::getInstance();

        // Must be "Tilaus %ORDERNUMBER%" according to docs
        if ($config->getPaymentPageLanguage() === 'fi_FI') {
            $itemName = 'Tilaus ' . $order->get_id();
        } else {
            $itemName = 'Order ' . $order->get_id();
        }

        $grossAmount = $itemsGrossPrice;
        $netAmount = $itemsNetPrice;
        $taxPercentage = round($itemsTax / $netAmount, 2);

        // recalculation
        // Sometimes calculation is wrong. For example:
        // System rejects this; mostly issue is that net amount is 6725; 6725*1,24=8339, while gross amount is sent as 8440.
        // net amount/unit-cost should be 6726; 6726*1,24=8340,24 (8340 rounded), and vat amount 1614.

        // In this case we need calculate net amount. For example:
        // 8340/100 = 83.40 -> 83.40/1.24 = 16.14 -> 1614.
        $netAmount = round($grossAmount / 100 / (1 + $taxPercentage), 2) * 100;

        return [
            'name' => $itemName,
            'unit_count' => 1,
            'unit_cost' => $netAmount,
            'net_amount' => $netAmount,
            'gross_amount' => $grossAmount,
            'tax_percentage' => $taxPercentage * 100 * 100,
            'discount_percentage' => 0
        ];
    }

    protected static function _groupItemsByTax($items)
    {

        /** @var WC_Order_Item_Product[] $result */
        $result = [];

        /** @var WC_Order_Item_Product $orderItem */
        foreach ($items as $orderItem) {
            $itemNet = round($orderItem->get_subtotal(), 2);
            $itemTax = round($orderItem->get_subtotal_tax(), 2);

            $tax = (string)round(self::_calculateTaxPercentage($itemNet, $itemTax), 2);

            if (!isset($result[$tax])) {

                $mergedItem = new WC_Order_Item_Product();
                $mergedItem->set_name(sprintf(__('Multiple items - tax %s', WC_VERIFONE_DOMAIN), $tax));
                $mergedItem->set_subtotal(0);
                $mergedItem->set_subtotal_tax(0);
                $mergedItem->set_quantity(1);

                $result[$tax] = $mergedItem;
            }

            $result[$tax]->set_subtotal((float)$result[$tax]->get_subtotal() + (float)$orderItem->get_subtotal());
            $result[$tax]->set_subtotal_tax((float)$result[$tax]->get_subtotal_tax() + (float)$orderItem->get_subtotal_tax());
        }

        return $result;
    }

    protected static function _getBasketDiscountData($orderGrossAmount, $itemsGrossPrice, $orderNetAmount, $itemsNetPrice)
    {

        // calculation for tax return for example 24%, so we have 24/100 => 0.24 but we need int value so *100.
        // But Verifone require round with precision = 2, so again we need *100. 100*100 = 10000
        $tax = round(
                (round($orderGrossAmount - $itemsGrossPrice, 0) - round($orderNetAmount - $itemsNetPrice, 0)
                ) / round($orderNetAmount - $itemsNetPrice, 0), 2) * 10000;

        return [
            'name' => __('Discount', WC_VERIFONE_DOMAIN),
            'unit_count' => 1,
            'unit_cost' => round($orderNetAmount - $itemsNetPrice, 0),
            'net_amount' => round($orderNetAmount - $itemsNetPrice, 0),
            'gross_amount' => round($orderGrossAmount - $itemsGrossPrice, 0),
            'tax_percentage' => round($orderNetAmount - $itemsNetPrice, 0) ? $tax : 0,
            'discount_percentage' => 0
        ];
    }

    protected static function _calculateTaxPercentage($price, $tax)
    {
        return $price ? round($tax * 100 / $price, 1) : $price;
    }

    protected static function _getOrderGrossAmount(WC_Order $order)
    {
        return round($order->get_total(), 2) * 100;
    }

    protected static function _getOrderNetAmount(WC_Order $order)
    {
        return self::_getOrderGrossAmount($order) - self::_getOrderTaxAmount($order);
    }

    protected static function _getOrderTaxAmount(WC_Order $order)
    {
        return round($order->get_total_tax(), 2) * 100;
    }

    protected static function _isSendBasketItems()
    {
        return self::_isSendBasketItemsForAll()
            || (self::_isSendBasketItemsForInvoice() && self::_isMethodTypeInvoice());
    }

    /**
     * Verifone has an option to group basket items into 1 combined item.
     * Currently only possible to enable this for invoice payment methods.
     *
     * @return bool
     */
    protected static function _isCombineBasketItems()
    {
        return (self::_isCombineInvoiceBasketItems() && self::_isMethodTypeInvoice());
    }

    /**
     * If true, basket items are sent for all orders and all payment methods.
     *
     * @return bool
     */
    protected static function _isSendBasketItemsForAll()
    {
        $config = WC_Verifone_Config::getInstance();
        $basketItemSending = $config->getBasketItemSending();

        return $basketItemSending == WC_Verifone_System::BASKET_ITEMS_SEND_FOR_ALL;
    }

    /**
     * If true, basket items are sent for orders made with invoice payment methods.
     *
     * @return bool
     */
    protected static function _isSendBasketItemsForInvoice()
    {
        $config = WC_Verifone_Config::getInstance();
        $basketItemSending = $config->getBasketItemSending();;

        return $basketItemSending == WC_Verifone_System::BASKET_ITEMS_SEND_FOR_INVOICE;
    }

    protected static function _isCombineInvoiceBasketItems()
    {
        $config = WC_Verifone_Config::getInstance();
        return $config->isCombineInvoiceBasketItems();
    }

    protected static function _isMethodTypeInvoice()
    {

        $paymentMethodCode = self::$_paymentMethod;

        if (!$paymentMethodCode) {
            return false;
        }

        $method = WC_Verifone_PaymentMethods::getPaymentMethodByCode($paymentMethodCode);

        if (is_null($method)) {
            return false;
        }

        return $method['type'] == WC_Verifone_PaymentMethods::TYPE_INVOICE;
    }
}
