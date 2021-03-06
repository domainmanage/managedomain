<?php

namespace ManageDomain;

use WHMCS\Database\Capsule as DB;

class ApiClient
{
    public $method = "POST";
    public $results = array();
    private $makeUrl;
    public $message = "";
    public $status = false;
    public $ApiUrl;
    public $ApiKey;

    public function __construct()
    {
        $res = DB::table("tblregistrars")->where("registrar", "Manage_Domain")->pluck('value', 'setting');
        $this->ApiUrl = $this->DecryptPassword($res["ApiUrl"])["password"];
        $this->ApiKey = $this->DecryptPassword($res["ApiKey"])["password"];
    }

    public function generateUrl($action, $url)
    {
        $this->makeUrl = $url . DIRECTORY_SEPARATOR . $action;
    }

    public function call($action, $postfields = null)
    {
        $this->generateUrl($action, $this->ApiUrl);

        $post = array('params' => $postfields);

        $authorization = "Authorization: Bearer " . trim($this->ApiKey);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->makeUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization, 'Accept: application/json'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new \Exception('Connection Error: ' . curl_errno($ch) . ' - ' . curl_error($ch));
        }
        curl_close($ch);

        $this->results = $this->processResponse($response);

        logModuleCall(
            'Registrarmodule',
            $postfields,
            $response,
            $this->results,
            array(
                $postfields['username'],
                $postfields['password'],
            )
        );
        if ($this->results === null && json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Bad response received from API');
        } else {
            if ($this->results['result'] != "error") {
                $this->status = true;
            } else {
                $this->status = false;
                foreach ($this->results["message"] as $value) {
                    $this->message .= key($value) . " => " . $value[key($value)];
                }
            }
        }

        return $this->results;
    }

    /**
     * Process API response.
     *
     * @param string $response
     *
     * @return array
     */
    public function processResponse($response)
    {
        return json_decode($response, true);
    }

    /**
     * Get from response results.
     *
     * @param string $key
     *
     * @return string
     */
    public function getFromResponse($key)
    {
        return isset($this->results[$key]) ? $this->results[$key] : '';
    }

    protected function DecryptPassword($password)
    {
        $command = 'DecryptPassword';
        $postData = array(
            'password2' => $password,
        );
        $results = localAPI($command, $postData);
        return $results;
    }

    /**
     * set method
     *
     * @return $this
     */
    public function post()
    {
        $this->method = "POST";
        return $this;
    }

    public function get()
    {
        $this->method = "GET";
        return $this;
    }
}
