<?php
/**
 * Plugin name: Campaignrabbit for WooCommerce
 * Plugin URI: https://www.campaignrabbit.com
 * Description: Campaignrabbit integration for WooCommerce
 * Author: Cartrabbit
 * Author URI: https://www.campaignrabbit.com
 * Version: 1.0.3
 * Text Domain: campaignrabbit-for-woocommerce
 * Domain Path: /i18n/languages/
 * Plugin URI: https://www.campaignrabbit.com
 * Requires at least: 4.6.1
 * WC requires at least: 2.5
 * WC tested up to: 3.5
 */
if (!defined('ABSPATH')) exit;
/**
 * Define the text domain
 */
if (!defined('CRIFW_TEXT_DOMAIN'))
    define('CRIFW_TEXT_DOMAIN', 'campaignrabbit-for-woocommerce');
/**
 * Current version of our app
 */
if (!defined('CRIFW_VERSION'))
    define('CRIFW_VERSION', '1.0.3');
/**
 * Set base file URL
 */
if (!defined('CRIFW_BASE_FILE'))
    define('CRIFW_BASE_FILE', plugin_basename(__FILE__));
if (!defined('CRIFW_BASE_DIR'))
    define('CRIFW_BASE_DIR', __DIR__);
/**
 * Setup the plugin file
 */
if (!defined('CRIFW_PLUGIN_FILE'))
    define('CRIFW_PLUGIN_FILE', __FILE__);
/**
 * Set Plugin prefix
 */
if (!defined('CRIFW_PLUGIN_PREFIX'))
    define('CRIFW_PLUGIN_PREFIX', 'crifw_');
/**
 * Set plugin environment
 */
if (!defined('CRIFW_ENV'))
    define('CRIFW_ENV', 'production');
/**
 * Set Plugin log path
 */
if (!defined('CRIFW_LOG_FILE_PATH')) {
    $path = ABSPATH . 'campaignrabbit.log';
    define('CRIFW_LOG_FILE_PATH', $path);
}
/**
 * Set Plugin log path
 */
if (!defined('CRIFW_DEV_LOG_FILE_PATH')) {
    $path = ABSPATH . 'dev_campaignrabbit.log';
    define('CRIFW_DEV_LOG_FILE_PATH', $path);
}
/**
 * Check and abort if PHP version is is less them 5.6 and does not met the required woocommerce version
 */
register_activation_hook(__FILE__, function () {
    if (version_compare(phpversion(), '5.6', '<')) {
        exit(__('Campaignrabbit-woocommerce requires minimum PHP version of 5.6', CRIFW_TEXT_DOMAIN));
    }
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
        exit(__('Woocommerce must installed and activated in-order to use Campaignrabbit-Woocommerce!', CRIFW_TEXT_DOMAIN));
    } else {
        if (!function_exists('get_plugins'))
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';
        $wc_installed_version = NULL;
        $wc_required_version = '2.5';
        if (isset($plugin_folder[$plugin_file]['Version'])) {
            $wc_installed_version = $plugin_folder[$plugin_file]['Version'];
        }
        if (version_compare($wc_required_version, $wc_installed_version, '>=')) {
            exit(__('Campaignrabbit-woocommerce requires minimum Woocommerce version of ', CRIFW_TEXT_DOMAIN) . ' ' . $wc_required_version . '. ' . __('But your Woocommerce version is ', CRIFW_TEXT_DOMAIN) . ' ' . $wc_installed_version);
        }
    }
});
/**
 * Check for required packages
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    return false;
}
require __DIR__ . '/vendor/autoload.php';

use Crifw\Campaignrabbit\Main;

Main::instance();