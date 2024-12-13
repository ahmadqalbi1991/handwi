<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait SMSTrait
{
    // API Doc URL : https://www.smscountry.com/bulk-smsc-api-documentation

    // SMS Country credentials
    private $username = "BGDealz";
    private $password = "Assad@2207";

    // SMS Country Sender ID
    private $senderId = "smscntry";

    // Type of Message, Default N: Normal Message
    private $messageType = "N";

    // Delivery Reports, Default:Y
    private $deliveryReport = "Y";

    // SMS Country URL
    private $url = "http://www.smscountry.com/SMSCwebservice_Bulk.aspx";

    // Proxy server details (if applicable)
    private $proxyIp = "";
    private $proxyPort = "";

    // CURL responses
    public $curlResponse;
    public $curlError;
    public $curlInfo;

    /**
     * Initialize with custom configuration.
     *
     * @param array $config
     */
    public function initSMSConfig(array $config = [])
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Send normal SMS message using SMS Country API.
     *
     * @param string $message
     * @param string $mobileNumbers
     * @param string|null $senderId
     * @return bool
     */
    public function sendNormalSMS(string $message, string $mobileNumbers, string $senderId = null): bool
    {
        $this->messageType = "N";
        $message = urlencode($message);
        $senderId = $senderId ?? $this->senderId;

        $ch = curl_init();

        if (!$ch) {
            $this->curlError = "Couldn't initialize a cURL handle";
            return false;
        }

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'User' => $this->username,
            'passwd' => $this->password,
            'mobilenumber' => $mobileNumbers,
            'message' => $message,
            'sid' => $senderId,
            'mtype' => $this->messageType,
            'DR' => $this->deliveryReport,
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if (!empty($this->proxyIp)) {
            curl_setopt($ch, CURLOPT_PROXY, $this->proxyIp . ":" . $this->proxyPort);
        }

        $this->curlResponse = curl_exec($ch);

        if (curl_errno($ch)) {
            $this->curlError = curl_error($ch);
        }

        if ($this->curlResponse === false) {
            curl_close($ch);
            return false;
        } else {
            $this->curlInfo = curl_getinfo($ch);
            curl_close($ch);
            return true;
        }
    }
}
