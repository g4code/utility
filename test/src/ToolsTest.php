<?php

use G4\Utility\Tools;

class ToolsTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var \G4\Utility\Tools
     */
    private $tools;

    public function setUp()
    {
        $this->tools = new Tools();
    }

    public function testReservedIpPrependHeadersLocalhost()
    {
        $_SERVER = [
            'HTTP_X_CLIENT_IP' => '192.168.1.1'
        ];

        $this->assertFalse($this->tools->getRealIP(false, ['HTTP_X_CLIENT_IP']));
    }

    public function testReservedIpPrependHeadersAllowPrivateRangeLocalhost()
    {
        $ip = '192.168.1.1';
        $_SERVER = [
            'HTTP_X_CLIENT_IP' => $ip
        ];

        $this->assertEquals($ip, $this->tools->getRealIP(true, ['HTTP_X_CLIENT_IP']));
    }

    public function testReservedIpPrependHeaders()
    {
        $ip = '10.1.2.3';
        $_SERVER = [
            'HTTP_X_CLIENT_IP' => $ip
        ];

        $this->assertFalse($this->tools->getRealIP(false, ['HTTP_X_CLIENT_IP']));
    }

    public function testReservedIpPrependHeadersAllowPrivateRange()
    {
        $ip = '10.1.2.3';
        $_SERVER = [
            'HTTP_X_CLIENT_IP' => $ip
        ];

        $this->assertEquals($ip, $this->tools->getRealIP(true, ['HTTP_X_CLIENT_IP']));
    }

    public function testIp1()
    {
        $ip = '160.99.1.1';

        $_SERVER = [
            'HTTP_X_CLIENT_IP' => $ip,
            'REMOTE_ADDR' => '199.99.99.99'
        ];

        $this->assertEquals($ip, $this->tools->getRealIP(false, ['HTTP_X_CLIENT_IP']));
    }

    public function testIp1NoPrepend()
    {
        $ip = '160.99.1.1';

        $_SERVER = [
            'HTTP_X_CLIENT_IP' => $ip,
            'REMOTE_ADDR' => '199.99.99.99'
        ];

        $this->assertEquals('199.99.99.99', $this->tools->getRealIP());
    }

    public function testCfIp1()
    {
        $ip = '160.99.1.1';

        $_SERVER = [
            'HTTP_CF_CONNECTING_IP' => $ip,
            'REMOTE_ADDR' => '199.99.99.99'
        ];

        $this->assertEquals($ip, $this->tools->getRealIP());
    }

    public function testClientIp()
    {
        $ip = '160.99.1.1';

        $_SERVER = [
            'HTTP_CLIENT_IP' => $ip,
            'REMOTE_ADDR' => '199.99.99.99'
        ];

        $this->assertEquals($ip, $this->tools->getRealIP());
    }

    public function testHttpXForwardedFor()
    {
        $ip = '180.99.99.12,123.123.123.123,160.99.1.1';

        $_SERVER = [
            'HTTP_X_FORWARDED_FOR' => $ip,
            'REMOTE_ADDR' => '199.99.99.99'
        ];

        $this->assertEquals('160.99.1.1', $this->tools->getRealIP());
    }

    public function testPort()
    {
        $ip = '160.99.1.1:8888';

        $_SERVER = [
            'CLIENT_IP' => $ip,
            'REMOTE_ADDR' => '199.99.99.99'
        ];

        $this->assertEquals('160.99.1.1', $this->tools->getRealIP());
    }

    public function testIpv6()
    {
        $ip = '2001:4860:4801:40::32';

        $_SERVER = [
            'CLIENT_IP' => $ip,
            'REMOTE_ADDR' => '199.99.99.99'
        ];

        $this->assertEquals($ip, $this->tools->getRealIP());

    }

}
