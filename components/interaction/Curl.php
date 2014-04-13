<?php


namespace components\interaction;


use components\Component;
use components\system\Config;

class Curl extends Component
{
    private $curl;

    public function __construct(Config $config = null)
    {
        parent::__construct($config);
    }

    private function doInit()
    {
        if (!$this->curl) {
            $cookieFile = $this->cookieJar;
            if (is_file($cookieFile)) {
                unlink($cookieFile);
            }
            $this->curl = curl_init();
            curl_setopt_array(
                $this->curl,
                array(
                    CURLOPT_COOKIEJAR      => $cookieFile,
                    CURLOPT_COOKIEFILE     => $cookieFile,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_USERAGENT      => $this->userAgent,
                    CURLOPT_FOLLOWLOCATION => 1,
                )
            );
        }
    }

    public function doRequest($url, array $params = array())
    {
        $this->doInit();
        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);
        return curl_exec($this->curl);
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }
} 