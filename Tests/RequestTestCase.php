<?php

namespace Openprovider\Service\Config\Tests;

use Openprovider\Service\Http\Request;

class RequestTestCase  extends \PHPUnit_Framework_TestCase
{
    public function testRequest()
    {
        $request  = new Request('google.com');
        $this->assertEquals('http://google.com', $request->getUrl());
        $request->setUrl('https://github.com/');
        $this->assertEquals('https://github.com/', $request->getUrl());
        $request->setUrl('http://github.com/');
        $this->assertEquals('http://github.com/', $request->getUrl());
        $request->setMethod('POST');
        $this->assertEquals('POST', $request->getMethod());
        $request->setMethod('DELETE');
        $this->assertEquals('DELETE', $request->getMethod());
        // test for incorrect method
        $request->setMethod('POT');
        $this->assertEquals('GET', $request->getMethod());
        $request->setPostData('data=some_data');
        $this->assertEquals('data=some_data', $request->getPostData());
        $request->setHeaders('Content-type: application/json');
        $this->assertEquals(['Content-type: application/json'], $request->getHeaders());
        $request->setOptions([CURLOPT_RETURNTRANSFER => false]);
        $options = $request->getOptions();
        $this->assertFalse($options[CURLOPT_RETURNTRANSFER]);
        $request->setFollowLocation(false);
        $options = $request->getOptions();
        $this->assertFalse($options[CURLOPT_FOLLOWLOCATION]);
        $request->setMaxRedirs(3);
        $options = $request->getOptions();
        $this->assertEquals(3, $options[CURLOPT_MAXREDIRS]);
        $request->setSslVerifyPeer(false);
        $options = $request->getOptions();
        $this->assertFalse($options[CURLOPT_SSL_VERIFYPEER]);
        $request->setTimeout(12);
        $options = $request->getOptions();
        $this->assertEquals(12, $options[CURLOPT_TIMEOUT]);
        $request->setTimeout(1.5);
        $options = $request->getOptions();
        $this->assertEquals(1500, $options[CURLOPT_TIMEOUT_MS]);
        $request->setCookie('PREF=ID; Name=Noname');
        $options = $request->getOptions();
        $this->assertEquals('PREF=ID; Name=Noname', $options[CURLOPT_COOKIE]);
        $request->setCookie([
            [
                'value' => [
                    'key' => 'PREF',
                    'value' => 'ID',
                ],
                'expires' => 1454606942,
                'path' => '/',
                'domain' => '.google.ru',
            ],
            [
                'value' => [
                    'key' => 'NID',
                    'value' => 67,
                ],
                'expires' => 1407346142,
                'path' => '/',
                'domain' => '.google.ru',
                'HttpOnly' => true,
            ],
        ]);
        $options = $request->getOptions();
        $this->assertEquals('PREF=ID; NID=67', $options[CURLOPT_COOKIE]);
        $newRequest = Request::get('google.com')->setHeaders('Content-type: text/html')->setPostData('a=b&c=d');
        $this->assertEquals('http://google.com', $newRequest->getUrl());
        $this->assertEquals('GET', $newRequest->getMethod());
        $this->assertEquals('a=b&c=d', $newRequest->getPostData());
        $this->assertEquals(array('Content-type: text/html'), $newRequest->getHeaders());
        $dryRunResponse = Request::get('google.com')->setTestMode(true)->execute();
        $this->assertTrue($dryRunResponse->isSuccess());
        $this->assertFalse($dryRunResponse->isError());
        $this->assertEquals(200, $dryRunResponse->getHttpStatusCode());
        $this->assertEquals('Ok', $dryRunResponse->getRaw());
        $this->assertEquals('Ok', $dryRunResponse->getData());
    }

    public function testRealRequest()
    {
        $response = Request::get('google.com')->execute();
        $this->assertEquals(200, $response->getHttpStatusCode());
    }
}
