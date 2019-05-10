<?php
if (!defined('ABSPATH')) exit;

class CMB2_Field_Campaignrabbit_Sync_old_orders
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
        add_filter('cmb2_render_campaignrabbit_sync_old_orders', array($this, 'render_campaignrabbit_sync_old_orders'), 10, 5);
    }

    /**
     * Render select box field
     */
    public function render_campaignrabbit_sync_old_orders($field, $field_escaped_value, $field_object_id, $field_object_type, $field_type_object)
    {
        $total_orders = 0;
        if ($field_type_object->field->args['attributes']['total_orders'])
            $total_orders = $field_type_object->field->args['attributes']['total_orders'];
        $this->setupAdminScripts();
        ?>
        <p>
            <b><?php echo __('Note : ', CRIFW_TEXT_DOMAIN); ?></b> <?php echo __('You have total of ', CRIFW_TEXT_DOMAIN) . $total_orders . __(' orders.', CRIFW_TEXT_DOMAIN); ?>
        </p>
        <?php
        if ($total_orders > 0) {
            echo __('To sync those orders, Please click the "Synchronize" button.', CRIFW_TEXT_DOMAIN);
        }
        ?>
        <br><br>
        <input type="hidden" id="campaignrabbit_ajax_path" value="<?php echo admin_url('admin-ajax.php') ?>">
        <input type="hidden" id="total_orders_found" value="<?php echo $total_orders ?>">
        <input type="button" class="button button-primary"
               id="sync_orders_campaignrabbit_app_btn"
               value="<?php echo __('Synchronize', CRIFW_TEXT_DOMAIN); ?>">
        <p>
            <?php
            if (isset($field_type_object->field->args['desc']))
                echo $field_type_object->field->args['desc'];
            ?>
        </p>
        <div style="background: #ebebeb;padding:10px;display: none;" id="ajax_message_container"
             data-complete="<?php echo __('orders successfully added to queue!', CRIFW_TEXT_DOMAIN); ?>"
             data-noorder="<?php echo __('No orders were found to sync!', CRIFW_TEXT_DOMAIN); ?>"
             data-message="<?php echo __(' orders remaining to sync!', CRIFW_TEXT_DOMAIN); ?>">
        </div>
        <div>
            <?php
            global $wpdb;
            $completed_order_results = $wpdb->get_row("SELECT count(ID) as completed FROM {$wpdb->prefix}posts WHERE (post_title LIKE '%campaignrabbit_process_order_queues%' OR post_title LIKE '%campaignrabbit_process_update_order_queues%') AND post_status='publish'", OBJECT);
            $pending_order_results = $wpdb->get_row("SELECT count(ID) as pending FROM {$wpdb->prefix}posts WHERE (post_title LIKE '%campaignrabbit_process_order_queues%' OR post_title LIKE '%campaignrabbit_process_update_order_queues%') AND post_status='pending'", OBJECT);
            $completed_customer_results = $wpdb->get_row("SELECT count(ID) as completed FROM {$wpdb->prefix}posts WHERE (post_title LIKE '%campaignrabbit_process_customer_queues%' OR post_title LIKE '%campaignrabbit_process_update_customer_queues%') AND post_status='publish'", OBJECT);
            $pending_customer_results = $wpdb->get_row("SELECT count(ID) as pending FROM {$wpdb->prefix}posts WHERE (post_title LIKE '%campaignrabbit_process_customer_queues%' OR post_title LIKE '%campaignrabbit_process_update_customer_queues%') AND post_status='pending'", OBJECT);
            if (!empty($pending_order_results)) {
                $count = $pending_order_results->pending;
                if ($count > 0) {
                    ?>
                    <p><?php echo $count; ?> <?php echo __('orders pending in queue ', CRIFW_TEXT_DOMAIN); ?> -
                        <button type="button" class="button button-primary remove-from-queue-table"
                                data-remove="pending"
                                data-hook="order"><?php echo __('Terminate', CRIFW_TEXT_DOMAIN); ?>
                        </button>
                    </p>
                    <?php
                }
            }
            if (!empty($completed_order_results)) {
                $count = $completed_order_results->completed;
                if ($count > 0) {
                    ?>
                    <p><?php echo $count; ?> <?php echo __('orders processed in queue', CRIFW_TEXT_DOMAIN); ?> -
                        <button type="button" class="button button-primary remove-from-queue-table"
                                data-remove="completed" data-hook="order"><?php echo __('Remove', CRIFW_TEXT_DOMAIN); ?>
                        </button>
                    </p>
                    <?php
                }
            }
            if (!empty($pending_customer_results)) {
                $count = $pending_customer_results->pending;
                if ($count > 0) {
                    ?>
                    <p><?php echo $count; ?> <?php echo __('customers pending in queue ', CRIFW_TEXT_DOMAIN); ?> -
                        <button type="button" class="button button-primary remove-from-queue-table"
                                data-remove="pending"
                                data-hook="customer"><?php echo __('Terminate', CRIFW_TEXT_DOMAIN); ?>
                        </button>
                    </p>
                    <?php
                }
            }
            if (!empty($completed_customer_results)) {
                $count = $completed_customer_results->completed;
                if ($count > 0) {
                    ?>
                    <p><?php echo $count; ?> <?php echo __('customers processed in queue ', CRIFW_TEXT_DOMAIN); ?>-
                        <button type="button" class="button button-primary remove-from-queue-table"
                                data-remove="completed" data-hook="customer">
                            <?php echo __('Remove', CRIFW_TEXT_DOMAIN); ?>
                        </button>
                    </p>
                    <?php
                }
            }
            ?>
        </div>
        <?php
    }

    /**
     * Enqueue scripts and styles
     */
    public function setupAdminScripts()
    {
        $asset_path = apply_filters('cmb2_field_campaignrabbit_app_asset_path', plugins_url('', __FILE__));
        wp_enqueue_script('campaignrabbit-app', $asset_path . '/js/campaignrabbit_sync_orders.js');
    }
}

new CMB2_Field_Campaignrabbit_Sync_old_orders();