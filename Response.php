<?php

namespace Openprovider\Service\Http;

class Response
{

    /**
     * @var int
     */
    private $httpCode;

    /**
     * @var string
     */
    private $raw;

    /**
     * @var array
     */
    private $header;

    /**
     * @var int
     */
    private $headerSize;

    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var string
     */
    private $errorDescription;

    public function __construct($raw, $httpCode, $headerSize, $errorCode = null, $errorDescription = '')
    {
        $this->httpCode = $httpCode;
        $this->raw = $raw;
        $this->headerSize = $headerSize;
        $this->header = explode("\n", substr($raw, 0, $headerSize));
        $this->errorCode = $errorCode;
        $this->errorDescription = $errorDescription;
    }

    /**
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * @return array|string
     */
    public function getCookie($toString = true)
    {
        $cookie = array();
        foreach ($this->getHeader() as $line) {
            if (preg_match('/^Set-Cookie: /i', $line)) {
                $line = preg_replace('/^Set-Cookie: /i', '', trim($line));
                $csplit = explode(';', $line);
                $cdata = array();
                foreach ($csplit as $data) {
                    $cinfo = explode('=', $data);
                    $cinfo[0] = trim($cinfo[0]);
                    $loweredCinfo = strtolower($cinfo[0]);
                    if ($loweredCinfo == 'expires') {
                        $cinfo[1] = strtotime($cinfo[1]);
                    }
                    if ($loweredCinfo == 'secure') {
                        $cinfo[1] = "true";
                    }
                    if ($loweredCinfo == 'httponly') {
                        $cinfo[1] = "true";
                    }
                    if (in_array($loweredCinfo, array('domain', 'expires', 'path', 'secure', 'comment', 'httponly'))) {
                        $cdata[trim($cinfo[0])] = $cinfo[1];
                    } else {
                        $cdata['value']['key'] = $cinfo[0];
                        $cdata['value']['value'] = $cinfo[1];
                    }
                }
                $cookie[] = $cdata;
            }
        }
        if ($toString) {
            return self::cookieFromArray($cookie);
        }

        return $cookie;
    }

    /**
     * Convert cookie from array to string
     *
     * @param array $cookie
     */
    public static function cookieFromArray(array $cookie)
    {
        $result = '';
        foreach ($cookie as $value) {
            $result[] = $value['value']['key'] . '=' . $value['value']['value'];
        }
        if (is_array($result)) {
            return trim(implode('; ', $result));
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return substr($this->raw, $this->headerSize);
    }

    /**
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpCode;
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return !$this->isError();
    }

    /**
     * @return bool
     */
    public function isError()
    {
        $str = (string)$this->httpCode;
        if (($str[0] != '2' && $str[0] != '3') || $this->getErrorCode()) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @return string
     */
    public function getErrorDescription()
    {
        return $this->errorDescription;
    }
}
