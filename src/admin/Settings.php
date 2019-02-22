<?php

namespace Crifw\Campaignrabbit\Admin;

use Crifw\Campaignrabbit\Library\CampaignrabbitApi;
use Crifw\Campaignrabbit\WcFunctions;

class Settings
{
    public $wc_functions;

    /**
     * Settings constructor.
     */
    function __construct()
    {
        $this->wc_functions = new WcFunctions();
    }

    /**
     * Render the admin pages
     */
    function renderPage()
    {
        add_action('cmb2_admin_init', function () {
            //connection tab
            $app_settings = new_cmb2_box(array(
                'id' => CRIFW_PLUGIN_PREFIX . 'campaignrabbit',
                'title' => __('CampaignRabbit', CRIFW_TEXT_DOMAIN),
                'object_types' => array('options-page'),
                'option_key' => 'campaignrabbit',
                'tab_group' => 'campaignrabbit',
                'tab_title' => __('Connection', CRIFW_TEXT_DOMAIN),
                'icon_url' => 'dashicons-email-alt',
                'save_button' => __('Save Changes', CRIFW_TEXT_DOMAIN)
            ));
            $app_settings->add_field(array(
                'name' => __('App ID', CRIFW_TEXT_DOMAIN),
                'id' => CRIFW_PLUGIN_PREFIX . 'campaignrabbit_app_id',
                'type' => 'text',
                'default' => '',
                'after_field' => '<p style="color: red" id="app_id_error"></p>',
                'attributes' => array('id' => 'campaignrabbit_app_id'),
                'after' => '<style>#submit-cmb{display:none;}</style>',
                'desc' => __('You can get your app id at CampaignRabbit Settings tab.', CRIFW_TEXT_DOMAIN)
            ));
            $app_settings->add_field(array(
                'name' => __('Api Token', CRIFW_TEXT_DOMAIN),
                'id' => CRIFW_PLUGIN_PREFIX . 'campaignrabbit_api_token',
                'type' => 'text',
                'default' => '',
                'after_field' => '<p style="color: red" id="secret_key_error"></p>',
                'attributes' => array('id' => 'campaignrabbit_secret_key'),
                'desc' => __('You can get your app token at CampaignRabbit Settings tab.', CRIFW_TEXT_DOMAIN)
            ));
            $app_settings->add_field(array(
                'id' => CRIFW_PLUGIN_PREFIX . 'is_campaignrabbit_connected',
                'type' => 'hidden',
                'default' => 0,
                'attributes' => array('id' => 'is_campaignrabbit_app_connected')
            ));
            $app_settings->add_field(array(
                'name' => '&nbsp;',
                'id' => CRIFW_PLUGIN_PREFIX . 'campaignrabbit_app',
                'type' => 'campaignrabbit_app',
                'default' => '',
                'after' => '<style>#submit-cmb{display:none;}</style>'
            ));
            if ($this->isAppConnected()) {
                //Settings tab
                $sync_data_settings = new_cmb2_box(array(
                    'id' => CRIFW_PLUGIN_PREFIX . 'campaignrabbit_sync_settings',
                    'title' => __('Settings', CRIFW_TEXT_DOMAIN),
                    'object_types' => array('options-page'),
                    'option_key' => 'campaignrabbit_settings',
                    'tab_group' => 'campaignrabbit',
                    'parent_slug' => 'campaignrabbit',
                    'tab_title' => __('Settings (Optional)', CRIFW_TEXT_DOMAIN),
                    'save_button' => __('Save Changes', CRIFW_TEXT_DOMAIN)
                ));
                $sync_data_settings->add_field(array(
                    'id' => CRIFW_PLUGIN_PREFIX . 'campaignrabbit_sync_batch_count',
                    'name' => __('Synchronize Batch count', CRIFW_TEXT_DOMAIN),
                    'desc' => __('Select synchronize batch count for each request', CRIFW_TEXT_DOMAIN),
                    'type' => 'select',
                    'default' => 20,
                    'attributes' => array('id' => 'sync_batch_count'),
                    'options' => array(
                        '5' => 5,
                        '10' => 10,
                        '20' => 20,
                        '50' => 50,
                        '100' => 100,
                    ),
                ));
                $sync_data_settings->add_field(array(
                    'name' => __('Sync old orders', CRIFW_TEXT_DOMAIN),
                    'id' => CRIFW_PLUGIN_PREFIX . 'campaignrabbit_sync_old_orders',
                    'type' => 'campaignrabbit_sync_old_orders',
                    'default' => '',
                    'desc' => __('Click to synchronize existing users and orders', CRIFW_TEXT_DOMAIN),
                    'before' => __('This step is optional. You can use this to synchronize orders that are made before you installed the Campaignrabbit', CRIFW_TEXT_DOMAIN),
                    'attributes' => array(
                        'total_orders' => $this->wc_functions->getTotalOrdersCount()
                    )
                ));
                $sync_data_settings->add_field(array(
                    'id' => CRIFW_PLUGIN_PREFIX . 'campaignrabbit_enable_log',
                    'name' => __('Enable Log', CRIFW_TEXT_DOMAIN),
                    'type' => 'radio_inline',
                    'options' => array(
                        '0' => __('No', CRIFW_TEXT_DOMAIN),
                        '1' => __('Yes', CRIFW_TEXT_DOMAIN)
                    ),
                    'default' => '0',
                ));
                //Log the details
                $log = new_cmb2_box(array(
                    'id' => CRIFW_PLUGIN_PREFIX . 'campaignrabbit_log',
                    'title' => __('Logs', CRIFW_TEXT_DOMAIN),
                    'object_types' => array('options-page'),
                    'option_key' => 'campaignrabbit_logs',
                    'tab_group' => 'campaignrabbit',
                    'parent_slug' => 'campaignrabbit',
                    'tab_title' => __('Logs', CRIFW_TEXT_DOMAIN),
                    'save_button' => __('Save Changes', CRIFW_TEXT_DOMAIN)
                ));
                $log->add_field(array(
                    'name' => '',
                    'id' => CRIFW_PLUGIN_PREFIX . 'campaignrabbit_sync_old_orders',
                    'type' => 'campaignrabbit_logs',
                    'default' => '',
                    'after' => '<style>#submit-cmb{display:none;}</style>'
                ));
            }
        });
    }

    /**
     * Check fo entered API key is valid or not
     * @return bool
     */
    function isAppConnected()
    {
        $settings = get_option('campaignrabbit', array());
        if (!empty($settings) && isset($settings[CRIFW_PLUGIN_PREFIX . 'is_campaignrabbit_connected']) && !empty($settings[CRIFW_PLUGIN_PREFIX . 'is_campaignrabbit_connected'])) {
            return true;
        }
        return false;
    }

    /**
     * Check is log enabled or not
     * @return bool
     */
    function isLogEnabled()
    {
        $settings = get_option('campaignrabbit_settings', array());
        if (!empty($settings) && isset($settings[CRIFW_PLUGIN_PREFIX . 'campaignrabbit_enable_log']) && !empty($settings[CRIFW_PLUGIN_PREFIX . 'campaignrabbit_enable_log'])) {
            return true;
        }
        return false;
    }

    /**
     * Check fo entered API key is valid or not
     * @param string $api_key
     * @param string $secret_key
     * @return bool
     */
    function isApiEnabled($api_key, $secret_key)
    {
        if (!empty($api_key)) {
            $api = new CampaignrabbitApi($api_key, $secret_key);
            return $api->validateApi();
        } else
            return false;
    }

    /**
     * set API keys
     * @return CampaignrabbitApi|null
     */
    function setApi()
    {
        if ($this->isAppConnected()) {
            $app_id = $this->getAppId();
            $api_token = $this->getSecretKey();
            if (!empty($app_id) && !empty($api_token)) {
                return new CampaignrabbitApi($app_id, $api_token);
            }
        }
        return NULL;
    }

    /**
     * Get all the settings given in general settings tab
     * @return mixed
     */
    function getSettings()
    {
        return $settings = get_option('campaignrabbit', array());
    }

    /**
     * get app id given by user
     * @return null|string
     */
    function getAppId()
    {
        $settings = $this->getSettings();
        if (!empty($settings) && isset($settings[CRIFW_PLUGIN_PREFIX . 'campaignrabbit_app_id']) && !empty($settings[CRIFW_PLUGIN_PREFIX . 'campaignrabbit_app_id'])) {
            return $settings[CRIFW_PLUGIN_PREFIX . 'campaignrabbit_app_id'];
        }
        return NULL;
    }

    /**
     * Get secret key entered by user
     * @return null|string
     */
    function getSecretKey()
    {
        $settings = $this->getSettings();
        if (!empty($settings) && isset($settings[CRIFW_PLUGIN_PREFIX . 'campaignrabbit_api_token']) && !empty($settings[CRIFW_PLUGIN_PREFIX . 'campaignrabbit_api_token'])) {
            return $settings[CRIFW_PLUGIN_PREFIX . 'campaignrabbit_api_token'];
        }
        return NULL;
    }

    /**
     * Create Customer
     * @param $customer
     * @return array|bool|mixed|object|string
     */
    function createCustomer($customer)
    {
        if (empty($customer))
            return false;
        $api = $this->setApi();
        if (!empty($api)) {
            return $api->manageCustomer('create', $customer);
        }
        return false;
    }

    /**
     * Get customer details by email
     * @param $customer_email
     * @return array|bool|mixed|object|string
     */
    function getCustomerByEmail($customer_email)
    {
        if (empty($customer_email))
            return false;
        $api = $this->setApi();
        if (!empty($api)) {
            return $api->manageCustomer('fetch', array(), $customer_email);
        }
        return false;
    }

    /**
     * Tell campaignrabbit about sync initiated
     */
    function initiateOrderSync()
    {
        $api = $this->setApi();
        if (!empty($api)) {
            return $api->initiateSync();
        }
        return NULL;
    }

    /**
     * Update customer details
     * @param $customer_details
     * @param $customer_id
     * @return array|bool|mixed|object|string
     */
    function updateCustomer($customer_details, $customer_id)
    {
        if (empty($customer_details) || empty($customer_id))
            return false;
        $api = $this->setApi();
        if (!empty($api)) {
            return $api->manageCustomer('update', $customer_details, '', $customer_id);
        }
        return false;
    }

    /**
     * Create Order
     * @param $order
     * @return array|bool|mixed|object|string
     */
    function createOrder($order)
    {
        if (empty($order))
            return false;
        $api = $this->setApi();
        if (!empty($api)) {
            return $api->manageOrder('create', $order);
        }
        return false;
    }

    /**
     * Update Order
     * @param $order
     * @param $order_id
     * @return array|bool|mixed|object|string
     */
    function updateOrder($order, $order_id)
    {
        if (empty($order) || empty($order_id))
            return false;
        $api = $this->setApi();
        if (!empty($api)) {
            return $api->manageOrder('update', $order, $order_id);
        }
        return false;
    }

    /**
     * Get order by id
     * @param $order_id
     * @return array|bool|mixed|object|string|null
     */
    function getOrderById($order_id)
    {
        if (empty($order_id))
            return false;
        $api = $this->setApi();
        if (!empty($api)) {
            return $api->manageOrder('fetch', array(), $order_id);
        }
        return false;
    }
}