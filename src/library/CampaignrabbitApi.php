<?php

namespace Crifw\Campaignrabbit\Library;

use Unirest\Request;

class CampaignrabbitApi
{
    public $api_url, $site_url, $app_id, $secret_key;

    /**
     * CampaignrabbitApi constructor.
     * @param $api_key
     * @param $secret_key
     */
    function __construct($api_key, $secret_key)
    {
        $this->api_url = "https://api.campaignrabbit.com/v1/";
        $this->site_url = site_url();
        $this->app_id = $api_key;
        $this->secret_key = $secret_key;
        return $this;
    }

    /**
     * tell campaignrabbit about orders going to sync
     */
    function initiateSync()
    {
        $response = $this->request('POST', 'store/initiated_sync');
        if (isset($response->body->success) && $response->body->success)
            return true;
        else
            return false;
    }

    /**
     * Manage customer related updates
     * @param $action
     * @param $data
     * @param string $customer_email
     * @param string $customer_id
     * @return array|bool|mixed|object|string|null
     */
    function manageCustomer($action, $data, $customer_email = "", $customer_id = "")
    {
        $uri = 'customer';
        switch ($action) {
            case "create":
                return $this->request('POST', $uri, $data);
                break;
            case "update":
                return $this->request('PUT', $uri . '/' . $customer_id, $data);
                break;
            case "fetch":
                return $this->request('GET', $uri . '/get_by_email/' . $customer_email);
                break;
            default:
                return NULL;
                break;
        }
    }

    /**
     * Manage order related operations
     * @param $action
     * @param $data
     * @param string $order_id
     * @return array|bool|mixed|object|string|null
     */
    function manageOrder($action, $data, $order_id = "")
    {
        $uri = 'order';
        switch ($action) {
            case "create":
                return $this->request('POST', $uri, $data);
                break;
            case "update":
                return $this->request('PUT', $uri . '/' . $order_id, $data);
                break;
            case "fetch":
                return $this->request('GET', $uri . '/get_by_r_id/' . $order_id);
                break;
            default:
                return NULL;
                break;
        }
    }

    /**
     * Validate API Key
     * @return bool
     */
    function validateApi()
    {
        $response = $this->request('POST', 'user/store/auth');
        if (isset($response->body->success) && $response->body->success)
            return true;
        else
            return false;
    }

    /**
     * get operation for Remote URL
     * @param $method
     * @param $url
     * @param array $data
     * @return array|bool|mixed|object|string
     */
    function request($method, $url, $data = array())
    {
        $response = '';
        try {
            $headers = array(
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Request-From-Domain' => $this->site_url,
                'App-Id' => $this->app_id,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            );
            $url = $this->api_url . $url;
            switch ($method) {
                case 'POST':
                    $response = Request::post($url, $headers, $data);
                    break;
                case 'GET':
                    $response = Request::get($url, $headers, $data);
                    break;
                case 'PUT':
                    $response = Request::put($url, $headers, $data);
                    break;
                case 'DELETE':
                    $response = Request::delete($url, $headers, $data);
                    break;
                case 'PATCH':
                    $response = Request::patch($url, $headers, $data);
                    break;
                default:
                    break;
            }
        } catch (\Exception $e) {
            $response = $e;
        }
        $this->logResponse($url, $response, $data);
        return $response;
    }

    /**
     * Create log file named campaignrabbit.log
     * @param $url
     * @param $response
     * @param $data
     */
    function logResponse($url, $response, $data)
    {
        if (CRIFW_ENV == "development") {
            $message = $url . ' -> REQUEST : ' . json_encode($data) . "\n RESPONSE : ";
            if (is_array($response) || is_object($response)) {
                $message .= json_encode($response);
            } else {
                $message .= $response;
            }
            $message .= "\n\n";
            try {
                $file = fopen(CRIFW_DEV_LOG_FILE_PATH, 'a');
                $message = __('<b>Time : </b>') . current_time('mysql') . ' | ' . $message;
                fwrite($file, $message);
                fclose($file);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}