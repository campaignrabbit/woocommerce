<?php

namespace Crifw\Campaignrabbit;

use Crifw\Campaignrabbit\Admin\Settings;

class RunCampaign
{
    public $admin, $wc_functions;

    function __construct()
    {
        $this->admin = new Settings();
        $this->wc_functions = new WcFunctions();
    }

    /**
     * Init the Admin
     */
    function init()
    {
        $this->admin->renderPage();
    }

    /**
     * Add settings link
     * @param $links
     * @return array
     */
    function pluginActionLinks($links)
    {
        $action_links = array(
            'settings' => '<a href="' . admin_url('admin.php?page=campaignrabbit') . '">' . __('Settings', CRIFW_TEXT_DOMAIN) . '</a>',
        );
        return array_merge($action_links, $links);
    }

    /**
     * Add required scripts
     */
    function enqueueScripts()
    {
        if ($this->admin->isAppConnected() && !is_admin()) {
            $current_user = wp_get_current_user();
            $user_email = '';
            if (isset($current_user->user_email) && !empty($current_user->user_email)) {
                $user_email = $current_user->user_email;
            }
            $app_id = $this->admin->getAppId();
            ?>
            <script type="text/javascript">
                window.campaignrabbit = {
                    app_id: "<?php echo $app_id ?>"
                };
                <?php if (!empty($user_email)) { ?>
                window.campaignrabbit.email = "<?php echo $user_email; ?>";
                <?php } ?>
                !function (e, t, n, p, o, a, i, s, c) {
                    e[o] || (i = e[o] = function () {
                        i.process ? i.process.apply(i, arguments) : i.queue.push(arguments)
                    }, i.queue = [], i.t = 1 * new Date, s = t.createElement(n),
                        s.async = 1, s.src = p + "?t=" + Math.ceil(new Date / a) * a, c = t.getElementsByTagName(n)[0], c.parentNode.insertBefore(s, c))
                }(window, document, "script", "https://cdn.campaignrabbit.com/campaignrabbit.analytics.js", "rabbit", 1), rabbit("init", "<?php echo $app_id ?>"), rabbit("event", "pageload");

            </script>
            <?php
        }
    }

    /**
     * Process the customer queues
     * @param $customer_details
     * @param $need_validation
     * @return bool
     */
    function processCustomerQueue($customer_details, $need_validation = true)
    {
        if (empty($customer_details))
            return false;
        $message = "";
        $create_customer = true;
        if ($need_validation && isset($customer_details['email'])) {
            //Check the customer already exists or not
            $customer = $this->admin->getCustomerByEmail($customer_details['email']);
            if (isset($customer->body->data->id) && !empty($customer->body->data->id)) {
                $this->processUpdateCustomerQueue($customer_details, $customer_details['email'], $customer->body->data->id, false);
                $create_customer = false;
            }
        }
        //Create customer if customer not exists
        if ($create_customer) {
            $response = $this->admin->createCustomer(json_encode($customer_details));
            if (isset($response->body->errors)) {
                $message = __('<b>Error in creating customer  - </b>', CRIFW_TEXT_DOMAIN);
                $message .= json_encode($response->body->errors);
            } elseif (isset($response->body->data->id)) {
                $message = __('<b>Customer created successfully with ID - </b>', CRIFW_TEXT_DOMAIN);
                $message .= $response->body->data->id;
            }
        }
        $this->logMessage($message);
        return true;
    }

    /**
     * Process the update customer queues
     * @param $customer_details
     * @param $customer_email
     * @param $customer_id
     * @param $need_validation
     * @return bool
     */
    function processUpdateCustomerQueue($customer_details, $customer_email, $customer_id = "", $need_validation = true)
    {
        if (empty($customer_details) && !empty($customer_email))
            return false;
        $update_customer = true;
        if ($need_validation) {
            //Check the customer is already exists
            $customer = $this->admin->getCustomerByEmail($customer_email);
            if (isset($customer->body->data->id) && !empty($customer->body->data->id)) {
                $customer_id = $customer->body->data->id;
            } else {
                //Add customer to queue
                $update_customer = false;
                $this->processCustomerQueue($customer_details, false);
            }
        }
        //Update customer if customer exists else create customer
        if ($update_customer && !empty($customer_id)) {
            $customer_update = $this->admin->updateCustomer(json_encode($customer_details), $customer_id);
            $message = "";
            if (isset($customer_update->body->errors)) {
                $message = __('<b>Error in updating customer  - </b>', CRIFW_TEXT_DOMAIN);
                $message .= json_encode($customer_update->body->errors);
            } elseif (isset($customer_update->body->data->id)) {
                $message = __('<b>Customer updated successfully with ID - </b>', CRIFW_TEXT_DOMAIN);
                $message .= $customer_update->body->data->id;
            }
            $this->logMessage($message);
        }
        return true;
    }

    /**
     * Process the orders in queue
     * @param $order_id
     * @return bool
     */
    function processOrderQueue($order_id)
    {
        if (empty($order_id))
            return false;
        $order = $this->wc_functions->getOrder($order_id);
        if (!empty($order)) {
            $order_details = $this->getOrderDetailsForQueue($order);
            $response = $this->admin->createOrder(json_encode($order_details));
            $message = "";
            if (isset($response->body->errors)) {
                $message = __('<b>Error in creating Order  - </b>', CRIFW_TEXT_DOMAIN);
                $message .= json_encode($response->body->errors);
            } elseif (isset($response->body->data->id)) {
                $message = __('<b>Order created successfully with ID - </b>', CRIFW_TEXT_DOMAIN);
                $message .= $response->body->data->id;
            }
            $this->logMessage($message);
            return true;
        }
        return false;
    }

    /**
     * Process the orders in queue
     * @param $order_id
     * @return bool
     */
    function processUpdateOrderQueue($order_id)
    {
        if (empty($order_id))
            return false;
        $order = $this->wc_functions->getOrder($order_id);
        if (!empty($order)) {
            $order_details = $this->getOrderDetailsForQueue($order);
            $response = $this->admin->updateOrder(json_encode($order_details), $order_id);
            $message = "";
            if (isset($response->body->errors)) {
                $message = __('<b>Error in updating Order  - </b>', CRIFW_TEXT_DOMAIN);
                $message .= json_encode($response->body->errors);
            } elseif (isset($response->body->data->id)) {
                $message = __('<b>Order updated successfully - </b>', CRIFW_TEXT_DOMAIN);
                $message .= $response->body->data->id;
            }
            $this->logMessage($message);
            return true;
        }
        return false;
    }

    /**
     * Create log file named campaignrabbit.log
     * @param $message
     */
    function logMessage($message)
    {
        if ($this->admin->isLogEnabled() && !empty($message)) {
            $message .= "\n\n";
            try {
                $file = fopen(CRIFW_LOG_FILE_PATH, 'a');
                $message = __('<b>Time : </b>') . current_time('mysql') . ' | ' . $message;
                fwrite($file, $message);
                fclose($file);
            } catch (\Exception $e) {
                $e->getMessage();
            }
        }
    }

    /**
     * Clear log file named campaignrabbit.log
     */
    function clearCampaignrabbitLogs()
    {
        $response = array();
        try {
            if (file_exists(CRIFW_LOG_FILE_PATH)) {
                unlink(CRIFW_LOG_FILE_PATH);
                $response['success'] = __('Log cleared successfully!', CRIFW_TEXT_DOMAIN);
            } else {
                $response['error'] = __('There is no log to clear!', CRIFW_TEXT_DOMAIN);
            }
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }
        wp_send_json($response);
    }

    /**
     * update order with campaignrabbit
     * @param $order_id
     */
    function newOrderCreated($order_id)
    {
        $order = $this->wc_functions->getOrder($order_id);
        if (!empty($order)) {
            as_schedule_single_action(time(), 'campaignrabbit_process_order_queues', array('order_id' => $order_id));
        }
    }

    /**
     * update order with campaignrabbit
     * @param $order_id
     */
    function orderStatusUpdated($order_id)
    {
        $response = $this->admin->getOrderById($order_id);
        if (isset($response->body->data->id) && !empty($response->body->data->id)) {
            $order = $this->wc_functions->getOrder($order_id);
            if (!empty($order)) {
                as_schedule_single_action(time(), 'campaignrabbit_process_update_order_queues', array('order_id' => $order_id));
            }
        } else {
            $this->newOrderCreated($order_id);
        }
    }

    /**
     * Sync old orders with the campaignrabbit
     */
    function syncOldOrdersWithCampaignrabbit()
    {
        $start = isset($_REQUEST['start']) ? $_REQUEST['start'] : 0;
        $limit = isset($_REQUEST['limit']) ? $_REQUEST['limit'] : 20;
        $total_orders = isset($_REQUEST['limit']) ? $_REQUEST['count'] : 0;
        $orders = $this->wc_functions->getOrders(array('limit' => $limit, 'offset' => $start));
        if (!empty($orders)) {
            foreach ($orders as $order) {
                $order_id = $this->wc_functions->getOrderId($order);
                as_schedule_single_action(time(), 'campaignrabbit_process_order_queues', array('order_id' => $order_id));
            }
        }
        $response = array();
        if ($total_orders < $limit) {
            $response['total'] = 0;
            $response['success'] = true;
            if ($this->admin->initiateOrderSync()) {
                $this->logMessage(__('Sync initiated!', CRIFW_TEXT_DOMAIN));
            }
        } else {
            $response['total'] = $total_orders - $limit;
            $response['dopatch'] = true;
        }
        $response['start'] = $start + $limit;
        wp_send_json($response);
    }

    /**
     * Get order details
     * @param $order
     * @return array
     */
    function getOrderDetailsForQueue($order)
    {
        if (empty($order))
            return array();
        $items = array();
        $order_meta = array();
        $item_meta_details = $this->wc_functions->getMeta($order);
        if (!empty($item_meta_details)) {
            foreach ($item_meta_details as $order_meta_key => $order_meta_value) {
                if (!empty($order_meta_value) && !is_array($order_meta_value) && !is_object($order_meta_value)) {
                    $order_meta[] = array(
                        'meta_key' => $order_meta_key,
                        'meta_value' => $order_meta_value,
                        'meta_options' => ''
                    );
                }
            }
        }
        $order_items = $this->wc_functions->getOrderItems($order);
        if (!empty($order_items)) {
            foreach ($order_items as $order_item) {
                $item_meta_details = $this->wc_functions->getMeta($order_item);
                $item_meta = array();
                if (!empty($item_meta_details)) {
                    foreach ($item_meta_details as $item_meta_key => $item_meta_value) {
                        if (!empty($item_meta_value) && !is_array($item_meta_value) && !is_object($item_meta_value)) {
                            $item_meta[] = array(
                                'meta_key' => $item_meta_key,
                                'meta_value' => $item_meta_value,
                                'meta_options' => ''
                            );
                        }
                    }
                }
                $item_quantity = $this->wc_functions->getItemQuantity($order_item);
                $product_price = round(($order_item['line_total'] / $item_quantity), 2);
                $items[] = array(
                    'r_product_id' => $this->wc_functions->getItemId($order_item),
                    'sku' => $this->wc_functions->getItemSku($order_item),
                    'product_name' => $this->wc_functions->getItemName($order_item),
                    'product_price' => $product_price,
                    'item_total' => $order_item['line_total'],
                    'item_qty' => $item_quantity,
                    'meta' => $item_meta
                );
            }
        }
        $billing = $this->wc_functions->getBillingDetails($order);
        $shipping = $this->wc_functions->getShippingDetails($order);
        $user = $this->wc_functions->getUser($order);
        $order_email = $this->wc_functions->getOrderEmail($order);
        $order_details = array(
            'r_order_id' => $this->wc_functions->getOrderId($order),
            'r_order_ref' => $this->wc_functions->getOrderId($order),
            'customer_email' => $order_email,
            'customer_name' => $this->wc_functions->getOrderedUserName($order),
            'order_total' => $this->wc_functions->getOrderTotal($order),
            'meta' => $order_meta,
            'order_items' => $items,
            'shipping' => $shipping,
            'billing' => $billing,
            'status' => strtolower($this->wc_functions->getOrderStatus($order)),
            'created_at' => $this->wc_functions->getOrderCreatedDate($order),
            'updated_at' => $this->wc_functions->getOrderModifiedDate($order),
            'customer_created_at' => (isset($user->user_registered) && !empty($user->user_registered)) ? $user->user_registered : $this->getCustomerRegisterDate($order_email, $order),
            'customer_updated_at' => (isset($user->user_registered) && !empty($user->user_registered)) ? $user->user_registered : $this->getCustomerModifiedDate($order_email, $order)
        );
        return $order_details;
    }

    /**
     * get the first order of the customer
     * @param $email
     * @param $current_order
     * @return null
     */
    function getCustomerRegisterDate($email, $current_order)
    {
        try {
            $orders = $this->wc_functions->getFirstOrderByEmail($email);
            $order_date = NULL;
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    $order_date = $this->wc_functions->getOrderCreatedDate($order);
                }
            }
            if (empty($order_date)) {
                $order_date = $this->wc_functions->getOrderCreatedDate($current_order);
            }
            return $order_date;
        } catch (\Exception $e) {
            return NULL;
        }
    }

    /**
     * get the Last order of the customer
     * @param $email
     * @param $current_order
     * @return null
     */
    function getCustomerModifiedDate($email, $current_order)
    {
        try {
            $orders = $this->wc_functions->getLastOrderByEmail($email);
            $order_date = NULL;
            if (!empty($orders)) {
                foreach ($orders as $order) {
                    $order_date = $this->wc_functions->getOrderCreatedDate($order);
                }
            }
            if (empty($order_date)) {
                $order_date = $this->wc_functions->getOrderCreatedDate($current_order);
            }
            return $order_date;
        } catch (\Exception $e) {
            return NULL;
        }
    }

    /**
     * Tell campaignrabbit about user details updated
     * @param $user_id
     * @param $old_user_data
     */
    function oldUserUpdated($user_id, $old_user_data)
    {
        if ((isset($_POST['email']) && !empty($_POST['email'])) || (isset($_POST['account_email']) && !empty($_POST['account_email'])) && !empty($user_id)) {
            //User role
            $user_role = (isset($_POST['role']) && !empty($_POST['role'])) ? (is_array($_POST['role'])) ? implode('|', $_POST['role']) : $_POST['role'] : 'customer';
            //User email
            if (isset($_POST['email']) && !empty($_POST['email']))
                $user_email = $_POST['email'];
            else if (isset($_POST['account_email']) && !empty($_POST['account_email']))
                $user_email = $_POST['account_email'];
            else
                $user_email = $old_user_data->user_email;
            //User name
            $first_name = $last_name = "";
            if (isset($_POST['first_name']) && !empty($_POST['first_name'])) {
                $first_name = $_POST['first_name'];
            } elseif (isset($_POST['account_first_name']) && !empty($_POST['account_first_name'])) {
                $first_name = $_POST['account_first_name'];
            }
            if (isset($_POST['last_name']) && !empty($_POST['last_name'])) {
                $last_name = $_POST['last_name'];
            } elseif (isset($_POST['account_last_name']) && !empty($_POST['account_last_name'])) {
                $last_name = $_POST['account_last_name'];
            }
            if (empty($first_name) && empty($last_name)) {
                if (isset($_POST['user_login']) && !empty($_POST['user_login']))
                    $name = $_POST['user_login'];
                elseif (isset($_POST['account_display_name']) && !empty($_POST['account_display_name']))
                    $name = $_POST['account_display_name'];
                else
                    $name = $old_user_data->user_login;
            } else {
                $name = $first_name . ' ' . $last_name;
            }
            $meta = array(array(
                'meta_key' => 'CUSTOMER_GROUP',
                'meta_value' => $user_role,
                'meta_options' => ''
            ));
            $customer_details = array(
                'email' => $user_email,
                'name' => $name,
                'created_at' => $old_user_data->user_registered,
                'updated_at' => current_time('mysql'),
                'meta' => $meta
            );
            as_schedule_single_action(time(), 'campaignrabbit_process_update_customer_queues', array('data' => $customer_details, 'customer_email' => $user_email, 'customer_id' => '', 'need_validation' => true));
        }
    }

    /**
     * Tell campaignrabbit about new account creation
     * @param $user_id
     */
    function newUserCreated($user_id)
    {
        $user = new \stdClass();
        if (!empty($user_id) && is_numeric($user_id)) {
            $user = get_userdata($user_id);
        }
        if (isset($user->ID) && $user->ID > 0) {
            $post_customer = $this->createRegisteredUser($user);
        } else {
            $post_customer = $this->createGuestUser();
        }
        $this->logMessage(__(json_encode($post_customer), CRIFW_TEXT_DOMAIN));
        if ($post_customer) {
            as_schedule_single_action(time(), 'campaignrabbit_process_customer_queues', array('data' => $post_customer, 'validation' => true));
        }
    }

    /**
     * When user role gets updated
     * @param $user_id
     */
    public function AfterUserRoleIsChanged($user_id)
    {
        $user = new \stdClass();
        if (!empty($user_id) && is_numeric($user_id)) {
            $user = get_userdata($user_id);
        }
        if (isset($user->ID) && $user->ID > 0) {
            $post_customer = $this->createRegisteredUser($user);
            $this->logMessage(__(json_encode($user->roles), CRIFW_TEXT_DOMAIN));
            if ($post_customer) {
                as_schedule_single_action(time(), 'campaignrabbit_process_customer_queues', array('data' => $post_customer, 'validation' => true));
            }
        }
    }

    /**
     * Create registered user
     * @param $user
     * @return array
     */
    public function createRegisteredUser($user)
    {
        $first_name = get_user_meta($user->ID, 'first_name', true);
        $last_name = get_user_meta($user->ID, 'last_name', true);
        if (empty($first_name) && empty($last_name)) {
            $name = $user->user_login;
        } else {
            $name = $first_name . ' ' . $last_name;
        }
        $roles = '';
        if (isset($user->roles)) {
            if (is_array($user->roles)) {
                $roles = implode(' | ', $user->roles);
            } elseif (is_string($user->roles)) {
                $roles = $user->roles;
            }
        }
        $meta_array = array(array(
            'meta_key' => 'CUSTOMER_GROUP',
            'meta_value' => $roles,
            'meta_options' => ''
        ));
        $post_customer = array(
            'email' => $user->user_email,
            'name' => $name,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'meta' => $meta_array
        );
        return $post_customer;
    }

    /**
     * Create guest user
     * @return array|bool
     */
    public function createGuestUser()
    {
        $post_customer = false;
        if (
            (isset($_POST['email']) && !empty($_POST['email'])) ||
            (isset($_POST['billing_email']) || !empty($_POST['billing_email']))
        ) {
            $first_name = isset($_POST['first_name']) ? $_POST['first_name'] : '';
            $last_name = isset($_POST['last_name']) ? $_POST['last_name'] : '';
            $name = $first_name . ' ' . $last_name;
            if ($name == ' ') {
                $name = isset($_POST['user_login']) ? $_POST['user_login'] : '';
            }
            $meta_array = array(array(
                'meta_key' => 'CUSTOMER_GROUP',
                'meta_value' => isset($_POST['role']) ? $_POST['role'] : '',
                'meta_options' => ''
            ));
            $post_customer = array(
                'email' => isset($_POST['email']) ? $_POST['email'] : '',
                'name' => $name,
                'created_at' => current_time('mysql'),
                // 'updated_at'=>current_time( 'mysql' ),
                'meta' => $meta_array
            );
            if (isset($_POST['createaccount']) ? $_POST['createaccount'] : false) {
                $post_customer = array(
                    'email' => isset($_POST['billing_email']) ? $_POST['billing_email'] : '',
                    'name' => $name,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                    'meta' => $meta_array
                );
            }
        }
        return $post_customer;
    }

    /**
     * Add Customer to queue
     * @param $order
     * @return array
     */
    function getCustomerDetailsForQueue($order)
    {
        if (empty($order))
            return array();
        $user = $this->wc_functions->getUser($order);
        if (isset($user->ID) && !empty($user->ID)) {
            $first_name = get_user_meta($user->ID, 'first_name', true);
            $last_name = get_user_meta($user->ID, 'last_name', true);
            $name = $first_name . ' ' . $last_name;
            if (empty($first_name) && empty($last_name)) {
                if (isset($user->user_login))
                    $name = $user->user_login;
                elseif (isset($user->display_name))
                    $name = $user->display_name;
            }
            $user_roles = '';
            if (isset($user->roles)) {
                if (is_array($user->roles)) {
                    $user_roles = implode(' | ', $user->roles);
                } elseif (is_string($user->roles)) {
                    $user_roles = $user->roles;
                }
            }
            $meta = array(array(
                'meta_key' => 'CUSTOMER_GROUP',
                'meta_value' => $user_roles,
                'meta_options' => ''
            ));
            $customer_details = array(
                'email' => $user->user_email,
                'name' => $name,
                'created_at' => $user->user_registered,
                'updated_at' => $user->user_registered,
                'meta' => $meta
            );
        } else {
            $meta = array(array(
                'meta_key' => 'CUSTOMER_GROUP',
                'meta_value' => 'customer',
                'meta_options' => ''
            ));
            $order_email = $this->wc_functions->getOrderEmail($order);
            $customer_details = array(
                'email' => $order_email,
                'name' => $this->wc_functions->getOrderedUserName($order),
                'created_at' => $this->getCustomerRegisterDate($order_email, $order),
                'updated_at' => $this->getCustomerModifiedDate($order_email, $order),
                'meta' => $meta
            );
        }
        return $customer_details;
    }

    /**
     * Validate app Id
     */
    function validateCampaignRabbitAppKey()
    {
        $app_id = isset($_REQUEST['app_id']) ? $_REQUEST['app_id'] : '';
        $secret_key = isset($_REQUEST['secret_key']) ? $_REQUEST['secret_key'] : '';
        $response = array();
        if (empty($app_id)) {
            $response['error']['app_id'] = __('Please enter App-Id', CRIFW_TEXT_DOMAIN);
        }
        if (empty($secret_key)) {
            $response['error']['secret_key'] = __('Please enter Secret key', CRIFW_TEXT_DOMAIN);
        }
        if (empty($response)) {
            $is_api_enabled = $this->admin->isApiEnabled($app_id, $secret_key);
            if ($is_api_enabled) {
                $response['success'] = __('Successfully connected to Retainful', CRIFW_TEXT_DOMAIN);
            } else {
                $response['error'] = __('We found entered API credentials were wrong!', CRIFW_TEXT_DOMAIN);
            }
        }
        wp_send_json($response);
    }

    /**
     * Auto connect api
     */
    function connectApp()
    {
        if (isset($_REQUEST['api']) && isset($_REQUEST['connect']) && $_REQUEST['app_id'] && $_REQUEST['api_token']) {
            $api = sanitize_text_field($_REQUEST['api']);
            $api_token = sanitize_text_field($_REQUEST['api_token']);
            $app_id = sanitize_text_field($_REQUEST['app_id']);
            $connect = sanitize_text_field($_REQUEST['connect']);
            if (!empty($api) && $api == 'campaignrabbit' && !empty($connect) && $connect == 'complete' && !empty($app_id) && !empty($api_token)) {
                $is_api_enabled = $this->admin->isApiEnabled($app_id, $api_token);
                if ($is_api_enabled) {
                    $connected = 1;
                } else {
                    $connected = 0;
                }
                $update = array(
                    CRIFW_PLUGIN_PREFIX . 'campaignrabbit_app_id' => $app_id,
                    CRIFW_PLUGIN_PREFIX . 'campaignrabbit_api_token' => $api_token,
                    CRIFW_PLUGIN_PREFIX . 'is_campaignrabbit_connected' => $connected
                );
                update_option('campaignrabbit', $update);
                wp_safe_redirect(admin_url('admin.php?page=campaignrabbit'));
            }
        }
    }

    /**
     * Remove the scheduled actions
     */
    function removeFromQueue()
    {
        if (isset($_REQUEST['hook']) && isset($_REQUEST['remove'])) {
            $hook = sanitize_text_field($_REQUEST['hook']);
            $remove = sanitize_text_field($_REQUEST['remove']);
            if (!empty($hook) && !empty($remove)) {
                if ($hook == 'order') {
                    $where_hook = "(post_title LIKE '%campaignrabbit_process_order_queues%' OR post_title LIKE '%campaignrabbit_process_update_order_queues%')";
                } else {
                    $where_hook = "(post_title LIKE '%campaignrabbit_process_customer_queues%' OR post_title LIKE '%campaignrabbit_process_update_customer_queues%')";
                }
                if ($remove == 'completed') {
                    $where_status = "post_status='publish'";
                } else {
                    $where_status = "post_status='pending'";
                }
                global $wpdb;
                $query = "SELECT ID FROM {$wpdb->prefix}posts WHERE {$where_hook} AND {$where_status} AND post_type='scheduled-action'";
                $posts = $wpdb->get_results($query);
                $res = true;
                if (!empty($posts)) {
                    foreach ($posts as $post) {
                        if (!wp_delete_post($post->ID, true)) {
                            $res = false;
                        }
                    }
                }
                if ($res) {
                    $response = array('error' => false, 'message' => __('Process completed', CRIFW_TEXT_DOMAIN));
                } else {
                    $response = array('error' => true, 'message' => __('Error in processing', CRIFW_TEXT_DOMAIN));
                }
            } else {
                $response = array('error' => true, 'message' => __('Invalid request', CRIFW_TEXT_DOMAIN));
            }
        } else {
            $response = array('error' => true, 'message' => __('Invalid request', CRIFW_TEXT_DOMAIN));
        }
        wp_send_json($response);
    }
}