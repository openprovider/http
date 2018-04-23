<?php
// Copyright 2018 Openprovider Authors. All rights reserved.
// Use of this source code is governed by a license
// that can be found in the LICENSE file.

namespace Openprovider\Service\Http;

class Request
{
    /**
     * Available http methods
     */
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';
    const OPTIONS = 'OPTIONS';
    const HEAD = 'HEAD';

    /**
     * Available http request encodings
     */
    const ENCODING_GZIP = 'gzip';

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
    private $headers = [];

    /**
     * for testing purposes
     * @var boolean
     */
    private $testMode = false;

    /**
     * Set of default options to be used with request
     * (Can be overridden)
     *
     * @var array
     */
    private $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HEADER => true,
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_ENCODING => '',
    ];

    /**
     * Init new Request
     *
     * @param $url
     * @param string $method
     * @param mixed|null $data
     */
    public function __construct($url, $method = Request::GET, $data = null)
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
        return new self($url, Request::POST, $data);
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
        return new self($url, Request::PUT, $data);
    }

    /**
     * Perform DELETE request with new instance
     *
     * @param $url
     * @return Request
     */
    public static function delete($url)
    {
        return new self($url, Request::DELETE);
    }

    /**
     * Perform HEAD request with new instance
     *
     * @param $url
     * @return Request
     */
    public static function head($url)
    {
        return new self($url, Request::HEAD);
    }

    /**
     * Perform OPTIONS request with new instance
     *
     * @param $url
     * @return Request
     */
    public static function options($url)
    {
        return new self($url, Request::OPTIONS);
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
        if (!preg_match('#^GET|POST|PUT|DELETE|HEAD|OPTIONS$#', $method)) {
            $method = Request::GET;
        }
        $this->method = $method;

        return $this;
    }

    /**
     * Get method of request (GET, POST, PUT, DELETE, HEAD, OPTIONS)
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
            $headers = (array)$headers;
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
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = self::mergeDeep($this->options, $options);

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
        $this->options = self::mergeDeep(
            $this->options,
            [
                CURLOPT_USERPWD => $userPassword
            ]
        );

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
        $this->options = self::mergeDeep(
            $this->options,
            [
                CURLOPT_SSL_VERIFYPEER => $verify
            ]
        );

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
        $this->options = self::mergeDeep(
            $this->options,
            [
                CURLOPT_COOKIE => $cookie
            ]
        );

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
        $this->options = self::mergeDeep(
            $this->options,
            [
                CURLOPT_FOLLOWLOCATION => $followLocation
            ]
        );

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
        $this->options = self::mergeDeep(
            $this->options,
            [
                CURLOPT_MAXREDIRS => $maxRedirs
            ]
        );

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
            $this->options = self::mergeDeep(
                $this->options,
                [
                    CURLOPT_TIMEOUT_MS => 1000 * $timeout
                ]
            );
        } else {
            $this->options = self::mergeDeep(
                $this->options,
                [
                    CURLOPT_TIMEOUT => $timeout
                ]
            );
        }

        return $this;
    }

    /**
     * Set Encoding option
     *
     * @param string $encoding
     * @return $this
     */
    public function setEncoding($encoding = '')
    {
        $this->options = self::mergeDeep(
            $this->options,
            [
                CURLOPT_ENCODING => $encoding
            ]
        );

        return $this;
    }

    /**
     * Execute request
     *
     */
    public function execute()
    {
        $handle = curl_init();
        curl_setopt_array($handle, $this->prepareOptions());
        if ($this->testMode) {
            return new Response('Ok', 200, 0);
        }
        $output = curl_exec($handle);
        $httpCode = @curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $headerSize = @curl_getinfo($handle, CURLINFO_HEADER_SIZE);
        $response = new Response($output, $httpCode, $headerSize, curl_errno($handle), curl_error($handle));
        curl_close($handle);

        return $response;
    }

    /**
     * Used for testing purposes
     *
     * @param boolean $mode
     * @return $this
     */
    public function setTestMode($mode)
    {
        if (is_bool($mode)) {
            $this->testMode = $mode;
        }
        return $this;
    }

    private function prepareOptions()
    {
        $options = $this->getOptions();
        $options[CURLOPT_URL] = $this->getUrl();
        $options[CURLOPT_CUSTOMREQUEST] = $this->getMethod();
        if ($data = $this->getPostData()) {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $data;
        }
        if ($headers = $this->getHeaders()) {
            $options[CURLOPT_HTTPHEADER] = $headers;
        }

        return $options;
    }

    /**
     * Merge two or more arrays together recursively.
     *
     * @param array $array ...
     * @return array
     */
    private static function mergeDeep()
    {
        $args = func_get_args();

        if (!$args) {
            return array();
        }
        if (sizeof($args) == 1) {
            return $args[0];
        }

        $array = array_shift($args);

        while (($otherArray = array_shift($args)) !== null) {
            $array = self::mergeDeepHelper($array, $otherArray);
        }

        return $array;
    }

    /**
     * Recursively merges two arrays together.
     *
     * @param array $array1
     * @param array|null $array2
     * @return array
     */
    private static function mergeDeepHelper(array $array1, $array2 = null)
    {
        if (is_array($array2)) {
            foreach ($array2 as $key => $val) {
                if (is_array($array2[$key])) {
                    $array1[$key] = (array_key_exists($key, $array1) && is_array($array1[$key]))
                        ? self::mergeDeepHelper($array1[$key], $array2[$key])
                        : $array2[$key];
                } else {
                    $array1[$key] = $val;
                }
            }
        }

        return $array1;
    }
}
