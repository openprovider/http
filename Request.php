<?php

namespace Openprovider\Service\Http;

use Openprovider\Service\Helper\ArrayHelper;

class Request
{
    /**
     * @var string
     */
    private $url;

    /**
     * @var string ( GET/POST/PUT/DELETE )
     */
    private $method;

    /**
     * Data to be used with POST/PUT requests
     *
     * @var mixed|null
     */
    private $data;

    /**
     * @var array
     */
    private $headers = array();

    /**
     * Set of default options to be used with request
     * (Can be overridden)
     *
     * @var array
     */
    private $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER         => true,
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT        => 30,
    );

    /**
     * Init new Request
     *
     * @param $url
     * @param string $method
     * @param mixed|null $data
     */
    public function __construct($url, $method = 'GET', $data = null)
    {
        $this->setUrl($url);
        $this->setMethod($method);
        $this->setPostData($data);
    }

    /**
     * Perform GET request with new instance
     *
     * @param $url
     * @return Request
     */
    public static function get($url)
    {
        return new self($url);
    }

    /**
     * Perform POST request with new instance
     *
     * @param $url
     * @param $data
     * @return Request
     */
    public static function post($url, $data)
    {
        return new self($url, 'POST', $data);
    }

    /**
     * Perform PUT request with new instance
     *
     * @param $url
     * @param $data
     * @return Request
     */
    public static function put($url, $data)
    {
        return new self($url, 'PUT', $data);
    }

    /**
     * Perform DELETE request with new instance
     *
     * @param $url
     * @param $data
     * @return Request
     */
    public static function delete($url)
    {
        return new self($url, 'DELETE');
    }

    /**
     * Setup URL with correction of http head if necessary
     *
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        if (!preg_match('#^https?://#', $url)) {
            $url = 'http://' . $url;
        }
        $this->url = $url;

        return $this;
    }

    /**
     * Get Url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set method of request
     *
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        if (!preg_match('#^GET|POST|PUT|DELETE$#', $method)) {
            $method = 'GET';
        }
        $this->method = $method;

        return $this;
    }

    /**
     * Get method of request (GET, POST, PUT, DELETE)
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set data to be applied to POST/PUT requests
     *
     * @param mixed $data
     * @return $this
     */
    public function setPostData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get query data
     *
     * @return string
     */
    public function getPostData()
    {
        return $this->data;
    }

    /**
     * Set header/headers
     *
     * @param array|string $headers
     * @return $this
     */
    public function setHeaders($headers)
    {
        if (!is_array($headers)) {
            $headers = (array) $headers;
        }
        $this->headers = $headers;

        return $this;
    }

    /**
     * Return Headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set option/options
     *
     * @param array  $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = ArrayHelper::mergeDeep($this->options, $options);

        return $this;
    }

    /**
     * Return Options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set User & Password option
     *
     * @param string $userPassword
     * @return $this
     */
    public function setUserPassword($userPassword)
    {
        $this->options = ArrayHelper::mergeDeep($this->options, array(
            CURLOPT_USERPWD => $userPassword
        ));

        return $this;
    }

    /**
     * Set SSL Verification Peer option
     *
     * @param bool $verify
     * @return $this
     */
    public function setSslVerifyPeer($verify = false)
    {
        $this->options = ArrayHelper::mergeDeep($this->options, array(
            CURLOPT_SSL_VERIFYPEER => $verify
        ));

        return $this;
    }

    /**
     * Set Cookie option
     *
     * @param string|array $cookie
     * @return $this
     */
    public function setCookie($cookie)
    {
        if (is_array($cookie)) {
            $cookie = Response::cookieFromArray($cookie);
        }
        $this->options = ArrayHelper::mergeDeep($this->options, array(
            CURLOPT_COOKIE => $cookie
        ));

        return $this;
    }

    /**
     * Set Follow Location option
     *
     * @param bool $followLocation
     * @return $this
     */
    public function setFollowLocation($followLocation = true)
    {
        $this->options = ArrayHelper::mergeDeep($this->options, array(
            CURLOPT_FOLLOWLOCATION => $followLocation
        ));

        return $this;
    }

    /**
     * Set Maximum Redirections option
     *
     * @param int $maxRedirs
     * @return $this
     */
    public function setMaxRedirs($maxRedirs = 5)
    {
        $this->options = ArrayHelper::mergeDeep($this->options, array(
            CURLOPT_MAXREDIRS => $maxRedirs
        ));

        return $this;
    }

    /**
     * Set Timeout option
     *
     * @param int $timeout
     * @return $this
     */
    public function setTimeout($timeout = 30)
    {
        if (is_float($timeout)) {
            $this->options = ArrayHelper::mergeDeep($this->options, array(
                CURLOPT_TIMEOUT_MS => 1000 * $timeout
            ));
        } else {
            $this->options = ArrayHelper::mergeDeep($this->options, array(
                CURLOPT_TIMEOUT => $timeout
            ));
        }

        return $this;
    }

    /**
     * Execute request
     *
     */
    public function execute($run = true)
    {
        $handle = curl_init();
        curl_setopt_array($handle, $this->prepareOptions());
        if (!$run) {
            return new Response('Ok', 200, 0);
        }
        $output    = curl_exec($handle);
        $httpCode = @curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $headerSize = @curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $response = new Response($output, $httpCode, $headerSize, curl_errno($handle), curl_error($handle));
        curl_close($handle);

        return $response;
    }

    private function prepareOptions()
    {
        $options = $this->getOptions();
        $options[CURLOPT_URL] = $this->getUrl();
        $options[CURLOPT_CUSTOMREQUEST]  = $this->getMethod();
        if ($data = $this->getPostData()) {
            $options[CURLOPT_POST]       = true;
            $options[CURLOPT_POSTFIELDS] = $data;
        }
        if ($headers = $this->getHeaders()) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        return $options;
    }
}
