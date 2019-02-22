<?php

namespace Crifw\Campaignrabbit;
if (!defined('ABSPATH')) exit;

class WcFunctions
{
    /**
     * Get order object
     * @param $order_id
     * @return bool|\WC_Order|null
     */
    function getOrder($order_id)
    {
        if (function_exists('wc_get_order')) {
            return wc_get_order($order_id);
        }
        return NULL;
    }

    /**
     * Get total number or orders
     * @return int
     */
    function getTotalOrdersCount()
    {
        $total_orders = 0;
        $order_statuses = $this->getOrderStatuses();
        if (!empty($order_statuses)) {
            foreach ($order_statuses as $key => $order_status) {
                $total_orders += $this->getOrderCountByStatus($key);
            }
        }
        return $total_orders;
    }

    /**
     * Get total orders by its order status
     * @param $status
     * @return int
     */
    function getOrderCountByStatus($status)
    {
        if (empty($status))
            return 0;
        if (substr($status, 0, 3) == "wc-")
            $status = substr($status, 3);
        if (function_exists('wc_orders_count')) {
            return wc_orders_count($status);
        }
        return 0;
    }

    /**
     * Gel all order statuses
     * @return array
     */
    function getOrderStatuses()
    {
        if (function_exists('wc_get_order_statuses')) {
            return wc_get_order_statuses();
        }
        return array();
    }

    /**
     * Function to get orders by condition
     * @param $args
     * @return \stdClass|\WC_Order[]|null
     */
    function getOrders($args)
    {
        if (function_exists('wc_get_orders')) {
            return wc_get_orders($args);
        }
        return NULL;
    }

    /**
     * Get the user details from Order
     * @param $order
     * @return null
     */
    function getUser($order)
    {
        if (!empty($order) && method_exists($order, 'get_user')) {
            return $order->get_user();
        }
        return NULL;
    }

    function getOrderItems($order)
    {
        if (empty($order))
            return array();
        if (method_exists($order, 'get_items')) {
            return $order->get_items();
        }
        return array();
    }

    function getMeta($object)
    {
        if (empty($object))
            return array();
        if (method_exists($object, 'get_data')) {
            return $object->get_data();
        }
        return array();
    }

    function getItemName($item)
    {
        if (empty($item))
            return NULL;
        if (method_exists($item, 'get_name')) {
            return $item->get_name();
        }
        return NULL;
    }

    function getItemQuantity($item)
    {
        if (empty($item))
            return 1;
        if (method_exists($item, 'get_quantity')) {
            return $item->get_quantity();
        }
        return 1;
    }

    function getItemPrice($item)
    {
        if (empty($item))
            return NULL;
        if (method_exists($item, 'get_price')) {
            return $item->get_price();
        }
        return NULL;
    }

    function getItemId($item)
    {
        if (empty($item))
            return 0;
        if (method_exists($item, 'get_product_id')) {
            return $item->get_product_id();
        }
        return 0;
    }

    function getItemSku($item)
    {
        if (empty($item))
            return NULL;
        if (method_exists($item, 'get_sku')) {
            return $item->get_sku();
        } else {
            return $this->getItemId($item);
        }
    }

    function getBillingDetails($order)
    {
        if (empty($order))
            return array();
        return array(
            'first_name' => (method_exists($order, 'get_billing_first_name')) ? $order->get_billing_first_name() : NULL,
            'last_name' => (method_exists($order, 'get_billing_last_name')) ? $order->get_billing_last_name() : NULL,
            'company_name' => (method_exists($order, 'get_billing_company')) ? $order->get_billing_company() : NULL,
            'email' => (method_exists($order, 'get_billing_email')) ? $order->get_billing_email() : NULL,
            'mobile' => (method_exists($order, 'get_billing_phone')) ? $order->get_billing_phone() : NULL,
            'address_1' => (method_exists($order, 'get_billing_address_1')) ? $order->get_billing_address_1() : NULL,
            'address_2' => (method_exists($order, 'get_billing_address_2')) ? $order->get_billing_address_2() : NULL,
            'city' => (method_exists($order, 'get_billing_city')) ? $order->get_billing_city() : NULL,
            'state' => (method_exists($order, 'get_billing_state')) ? $order->get_billing_state() : NULL,
            'country' => (method_exists($order, 'get_billing_country')) ? $order->get_billing_country() : NULL,
            'zipcode' => (method_exists($order, 'get_billing_postcode')) ? $order->get_billing_postcode() : NULL
        );
    }

    function getShippingDetails($order)
    {
        if (empty($order))
            return array();
        return array(
            'first_name' => (method_exists($order, 'get_shipping_first_name') && !empty($order->get_shipping_first_name())) ? $order->get_shipping_first_name() : (method_exists($order, 'get_billing_first_name')) ? $order->get_billing_first_name() : NULL,
            'last_name' => (method_exists($order, 'get_shipping_last_name') && !empty($order->get_shipping_last_name())) ? $order->get_shipping_last_name() : (method_exists($order, 'get_billing_first_name')) ? $order->get_billing_first_name() : NULL,
            'company_name' => (method_exists($order, 'get_shipping_company') && !empty($order->get_shipping_company())) ? $order->get_shipping_company() : (method_exists($order, 'get_billing_first_name')) ? $order->get_billing_first_name() : NULL,
            'email' => (method_exists($order, 'get_billing_email')) ? $order->get_billing_email() : NULL,
            'mobile' => (method_exists($order, 'get_billing_phone')) ? $order->get_billing_phone() : NULL,
            'address_1' => (method_exists($order, 'get_shipping_address_1') && !empty($order->get_shipping_address_1())) ? $order->get_shipping_address_1() : (method_exists($order, 'get_billing_first_name')) ? $order->get_billing_first_name() : NULL,
            'address_2' => (method_exists($order, 'get_shipping_address_2') && !empty($order->get_shipping_address_2())) ? $order->get_shipping_address_2() : (method_exists($order, 'get_billing_first_name')) ? $order->get_billing_first_name() : NULL,
            'city' => (method_exists($order, 'get_shipping_city') && !empty($order->get_shipping_city())) ? $order->get_shipping_city() : (method_exists($order, 'get_billing_first_name')) ? $order->get_billing_first_name() : NULL,
            'state' => (method_exists($order, 'get_shipping_state') && !empty($order->get_shipping_state())) ? $order->get_shipping_state() : (method_exists($order, 'get_billing_first_name')) ? $order->get_billing_first_name() : NULL,
            'country' => (method_exists($order, 'get_shipping_country') && !empty($order->get_shipping_country())) ? $order->get_shipping_country() : (method_exists($order, 'get_billing_first_name')) ? $order->get_billing_first_name() : NULL,
            'zipcode' => (method_exists($order, 'get_shipping_postcode') && !empty($order->get_shipping_postcode())) ? $order->get_shipping_postcode() : (method_exists($order, 'get_billing_first_name')) ? $order->get_billing_first_name() : NULL
        );
    }

    /**
     * Get order Email form order object
     * @param $order
     * @return null
     */
    function getOrderEmail($order)
    {
        if (method_exists($order, 'get_billing_email')) {
            return $order->get_billing_email();
        }
        return NULL;
    }

    /**
     * Get order's  name form order object
     * @param $order
     * @return null
     */
    function getOrderedUserName($order)
    {
        if (method_exists($order, 'get_billing_first_name')) {
            return $order->get_billing_first_name();
        }
        return NULL;
    }

    /**
     * Get Order Id
     * @param $order
     * @return String|null
     */
    function getOrderId($order)
    {
        if (method_exists($order, 'get_id')) {
            return $order->get_id();
        }
        return NULL;
    }

    /**
     * Get Order Total
     * @param $order
     * @return null
     */
    function getOrderTotal($order)
    {
        if (method_exists($order, 'get_total')) {
            return $order->get_total();
        }
        return NULL;
    }

    /**
     * Get Order Status
     * @param $order
     * @return null
     */
    function getOrderStatus($order)
    {
        if (method_exists($order, 'get_status')) {
            $order_status = $order->get_status();
            if (in_array($order_status, array('on-hold', 'processing')))
                return 'unpaid';
            else if (in_array($order_status, array('refunded')))
                return 'cancelled';
            else
                return $order_status;
        }
        return NULL;
    }

    /**
     * Get Order Created date
     * @param $order
     * @return null
     */
    function getOrderCreatedDate($order)
    {
        if (method_exists($order, 'get_date_created')) {
            $order_date = $order->get_date_created()->date('Y-m-d H:i:s');
            if (!empty($order_date))
                return $order_date;
            else
                return current_time('mysql');
        }
        return NULL;
    }

    /**
     * Get Order modified date
     * @param $order
     * @return null
     */
    function getOrderModifiedDate($order)
    {
        if (method_exists($order, 'get_date_modified')) {
            $order_date = $order->get_date_modified()->date('Y-m-d H:i:s');
            if (!empty($order_date))
                return $order_date;
            else
                return current_time('mysql');
        }
        return NULL;
    }

}