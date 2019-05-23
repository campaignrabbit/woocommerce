<?php
if (!defined('ABSPATH')) exit;

class CMB2_Field_Campaignrabbit_App
{
    /**
     * Current version number
     */
    const VERSION = '1.0.0';

    /**
     * Initialize the plugin by hooking into CMB2
     */
    public function __construct()
    {
        add_filter('cmb2_render_campaignrabbit_app', array($this, 'render_campaignrabbit_app'), 10, 5);
    }

    /**
     * Render select box field
     */
    public function render_campaignrabbit_app($field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object)
    {
        $admin_settings = new Crifw\Campaignrabbit\Admin\Settings();
        $is_app_connected = $admin_settings->isAppConnected();
        $app_id = $admin_settings->getAppId();
        $api_token = $admin_settings->getSecretKey();
        $this->setupAdminScripts();
        ?>
        <div style="display:block;background: #fff;color:#333;padding: 20px;max-width: 100%;text-align:center;border-radius: 4px;margin: auto;">
            <div class="center">
                <img style="max-width: 173px; height: auto;margin-top: -20px; display:inline-block"
                     src="https://s3.us-east-2.amazonaws.com/woocampaigns-public/images/logo.png">
            </div>
            <h3 style="flex: 1;font-family:'helvetica',sans-serif;font-weight: 600;font-size:25px;color: #333;line-height:1.3;">
                Don't have an account?</h3>
            <h4 style="font-size: 19px;line-height: 1.33;margin-bottom: 20px;color: #555;">
                Campaignrabbit helps you automate your marketing efforts and increase conversions.
            </h4>
            <p style="font-family:'helvetica',sans-serif;margin: 15px 0;color:#777;font-size: 17px;letter-spacing:0.02em;line-height:1.6;">
                Sell more by showing targeted, data-driven Calls to Actions (popups / slide-ins /action bars) in
                your
                storefront and sending personalised emails to your customers.</p>
            <p style="font-family:'helvetica',sans-serif;margin: 15px 0;color:#777;font-size: 17px;letter-spacing:0.02em;line-height:1.6;">
                No coding skill required. Drag and drop to design your campaigns and go live within minutes.</p>
            <p style="font-family:'helvetica',sans-serif;margin: 15px 0;color:#777;font-size: 17px;letter-spacing:0.02em;line-height:1.6;">
                Get actionable insights and metrics on your store.</p>
            <p>
                <?php
                $user_id = get_current_user_id();
                if ($user_id) {
                    $user = get_user_by('ID', $user_id);
                    if (!empty($user)) {
                        $params = array(
                            'email' => $user->user_email,
                            'domain_name' => $this->getShopDomain(),
                            'redirect_url' => $this->getRedirectUrl(),
                            'name' => $user->display_name,
                            'password' => ''
                        );
                        $campaignrabbit = new \Crifw\Campaignrabbit\Library\CampaignrabbitApi('', '');
                        $url = $campaignrabbit->registerOrLoginUrl($params);
                        ?>
                        <?php
                        if (current_user_can('manage_woocommerce')) {
                            ?>
                            <a href="<?php echo $url; ?>"
                               style="font-family:'helvetica',sans-serif;display: inline-block;font-size: 16px;padding: 10px 20px;text-decoration: none;color:#fff;background:#6772e5;border-radius: 4px;font-weight: 500;line-height:1.6;"><?php echo (!$is_app_connected) ? __('Connect', CRIFW_TEXT_DOMAIN) : __('Re-Connect', CRIFW_TEXT_DOMAIN); ?>
                                &nbsp;<?php echo __('to Campaignrabbit', CRIFW_TEXT_DOMAIN); ?></a>
                            <?php
                            if ($is_app_connected) {
                                ?>
                                <a href="javascript:;" id="disconnect_campaignrabbit_app_btn"
                                   style="font-family:'helvetica',sans-serif;display: inline-block;font-size: 16px;padding: 10px 20px;text-decoration: none;color:#fff;background:#ff5151;border-radius: 4px;font-weight: 500;line-height:1.6;">
                                    <?= __('Dis-Connect', CRIFW_TEXT_DOMAIN) ?>
                                </a>
                                <?php
                            }
                        }
                    }
                }
                ?>
            </p>
        </div>
        <p class="campaignrabbit_app_validation_message" style="text-align: center">
            <?PHP
            if (!$is_app_connected && (!empty($app_id) || !empty($api_token))) {
                ?>
                <span style="color:red;"><?php echo __('We found entered API credentials were wrong!', CRIFW_TEXT_DOMAIN); ?></span>
                <?php
            } else {
                ?>
                <span style="color:green;"><?php echo ($is_app_connected) ? __('Successfully connected to Campaignrabbit', CRIFW_TEXT_DOMAIN) : '' ?></span>
                <?php
            }
            ?>
        </p>
        <?php
    }

    function getRedirectUrl()
    {
        return add_query_arg(array(
            'api' => 'campaignrabbit',
            'connect' => 'complete'
        ), get_home_url());
    }

    /**
     * Is this a multisite directory install?
     * @return boolean
     */
    function isMultiSiteDirectoryInstall()
    {
        return defined('MULTISITE') && MULTISITE && (!defined('SUBDOMAIN_INSTALL') || !SUBDOMAIN_INSTALL);
    }

    /**
     * Get the shop domain
     * @return mixed|string
     */
    function getShopDomain()
    {
        $domain = parse_url(get_home_url(), PHP_URL_HOST);
        if ($this->isMultiSiteDirectoryInstall()) {
            $domain .= parse_url(get_home_url(), PHP_URL_PATH);
        }
        return $domain;
    }

    /**
     * Enqueue scripts and styles
     */
    public function setupAdminScripts()
    {
        $asset_path = apply_filters('cmb2_field_campaignrabbit_app_asset_path', plugins_url('', __FILE__));
        wp_enqueue_script('campaignrabbit-app', $asset_path . '/js/campaignrabbit_app.js');
    }
}

new CMB2_Field_Campaignrabbit_App();