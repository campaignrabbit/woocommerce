<?php

namespace Crifw\Campaignrabbit;
if (!defined('ABSPATH')) exit;

class Main
{
    public static $init;
    public $crifw;

    /**
     * Initiate the plugin
     * @return Main
     */
    public static function instance()
    {
        return self::$init = (self::$init == NULL) ? new self() : self::$init;
    }

    /**
     * Main constructor.
     */
    function __construct()
    {
        $this->crifw = ($this->crifw == NULL) ? new RunCampaign() : $this->crifw;
        $this->activateEvents();
        $this->checkForPluginUpdates();
    }

    /**
     * Activate the required events
     */
    function activateEvents()
    {
        //Check for dependencies
        add_action('plugins_loaded', array($this, 'checkDependencies'));
        //Activate CMB2 functions
        add_action('init', function () {
            $this->crifw->init();
        });
        //Settings link
        add_filter('plugin_action_links_' . CRIFW_BASE_FILE, array($this->crifw, 'pluginActionLinks'));
        //Validate key
        add_action('wp_ajax_validateCampaignRabbitAppKey', array($this->crifw, 'validateCampaignRabbitAppKey'));
        add_action('wp_ajax_syncOldOrdersWithCampaignrabbit', array($this->crifw, 'syncOldOrdersWithCampaignrabbit'));
        add_action('wp_ajax_clearCampaignrabbitLogs', array($this->crifw, 'clearCampaignrabbitLogs'));
        add_action('wp_ajax_campaignrabbitAnalytics', array($this->crifw, 'campaignrabbitAnalytics'));
        //Process customer queue
        add_action('campaignrabbit_process_customer_queues', array($this->crifw, 'processCustomerQueue'), 10, 2);
        add_action('campaignrabbit_process_update_customer_queues', array($this->crifw, 'processUpdateCustomerQueue'), 10, 4);
        //Process Order queue
        add_action('campaignrabbit_process_order_queues', array($this->crifw, 'processOrderQueue'), 10, 1);
        add_action('campaignrabbit_process_update_order_queues', array($this->crifw, 'processUpdateOrderQueue'), 10, 1);
        //Order status changes
        add_action('woocommerce_order_status_completed', array($this->crifw, 'orderStatusUpdated'), 10, 1);
        add_action('woocommerce_order_status_failed', array($this->crifw, 'orderStatusUpdated'), 10, 1);
        add_action('woocommerce_order_status_cancelled', array($this->crifw, 'orderStatusUpdated'), 10, 1);
        add_action('woocommerce_order_status_pending', array($this->crifw, 'orderStatusUpdated'), 10, 1);

        add_action('woocommerce_order_status_on-hold', array($this->crifw, 'orderStatusUpdated'), 10, 1);
        add_action('woocommerce_order_status_processing', array($this->crifw, 'orderStatusUpdated'), 10, 1);
        add_action('woocommerce_order_status_refunded', array($this->crifw, 'orderStatusUpdated'), 10, 1);
        //User changes
        add_action('user_register', array($this->crifw, 'newUserCreated'), 10, 1);
        add_action('profile_update', array($this->crifw, 'oldUserUpdated'), 10, 2);
        //Enqueue scripts
        add_action('wp_enqueue_scripts', array($this->crifw, 'enqueueScripts'));
    }

    /**
     * Check any updates found
     */
    function checkForPluginUpdates()
    {
        \Puc_v4_Factory::buildUpdateChecker('https://github.com/campaignrabbit/woocommerce', CRIFW_PLUGIN_FILE);
    }

    /**
     * Dependency check for our plugin
     */
    function checkDependencies()
    {
        if (!defined('WC_VERSION')) {
            $this->showAdminNotice(__('Woocommerce must be activated for Campaignrabbit-Woocommerce to work', CRIFW_TEXT_DOMAIN));
        } else {
            if (version_compare(WC_VERSION, '2.0', '<')) {
                $this->showAdminNotice(__('Your woocommerce version is ', CRIFW_TEXT_DOMAIN) . WC_VERSION . __('. Some of the features of Campaignrabbit-Woocommerce will not work properly on this woocommerce version.', CRIFW_TEXT_DOMAIN));
            }
        }
    }

    /**
     * Show notices for user..if anything unusually happen in our plugin
     * @param string $message - message to notice users
     */
    function showAdminNotice($message = "")
    {
        if (!empty($message)) {
            add_action('admin_notices', function () use ($message) {
                echo '<div class="error notice"><p>' . $message . '</p></div>';
            });
        }
    }
}