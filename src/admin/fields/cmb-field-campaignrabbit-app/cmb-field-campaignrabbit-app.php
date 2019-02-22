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
        $asset_path = apply_filters('pw_cmb2_field_select2_asset_path', plugins_url('', __FILE__));
        ?>
        <input type="hidden" id="campaignrabbit_ajax_path" value="<?php echo admin_url('admin-ajax.php') ?>">
        <input type="button" class="button button-primary"
               id="validate_campaignrabbit_app_btn"
               value="<?php echo (!$is_app_connected) ? __('Connect', CRIFW_TEXT_DOMAIN) : __('Re-Connect', CRIFW_TEXT_DOMAIN); ?>">&nbsp;
        <img src="<?= $asset_path ?>/image/loader.gif" id="campaign_loader_img" style="display:none;"/>
        <?php
        if ($is_app_connected) {
            ?>
            <button class="button" type="button" id="disconnect_campaignrabbit_app_btn">
                <?= __('Dis-Connect', CRIFW_TEXT_DOMAIN) ?>
            </button>
            <img src="<?= $asset_path ?>/image/loader.gif" id="disconnect_campaign_loader_img" style="display:none;"/>
            <?php
        }
        ?>
        <div class="campaignrabbit_app_validation_message" style="display:flex;">
            <?PHP
            if (!$is_app_connected && (!empty($app_id) || !empty($api_token))) {
                ?>
                <p style="color:red;"><?php echo __('We found entered API credentials were wrong!', CRIFW_TEXT_DOMAIN); ?></p>
                <?php
            } else {
                ?>
                <p style="color:green;"><?php echo ($is_app_connected) ? __('Successfully connected to Campaignrabbit', CRIFW_TEXT_DOMAIN) : '' ?></p>
                <?php
            }
            ?>
        </div>
        <?php
        if (!$is_app_connected) {
            ?>
            <div style="display:block;background: #fff;border: 1px solid #eee;color:#333;padding: 20px;max-width: 100%;text-align:center;border-radius: 4px;box-shadow: 0 0 5px 0 #ddd;margin: auto;">
                <div class="center">
                    <img style="max-width: 173px; height: auto;margin-top: -20px; display:inline-block"
                         src="https://www.campaignrabbit.com/wp-content/uploads/2018/04/campaignrabbit_logo_02.png">
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
                    <a href="https://app.campaignrabbit.com/register?utm_campaign=integration&utm_source=j2store&utm_content=register&utm_medium=web"
                       target="_blank"
                       style="font-family:'helvetica',sans-serif;display: inline-block;font-size: 16px;padding: 10px 20px;text-decoration: none;color:#fff;background:#6772e5;border-radius: 4px;font-weight: 500;line-height:1.6;">Get
                        Stated for free</a>
                </p>
            </div>
            <?php
        }
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