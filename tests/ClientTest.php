<?php

namespace Nanxihang\Oauth\Tests;

use Nanxihang\Oauth\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testConstructWithDefaultOptions()
    {
        $options = [
            'appkey' => 'test_appkey',
            'appsecret' => 'test_appsecret',
            'host' => 'https://example.com'
        ];
        
        $client = new Client($options);
        
        $this->assertEquals('https://example.com', $client->API_URL_PREFIX);
    }

    public function testConstructWithDefaultHost()
    {
        $options = [
            'appkey' => 'test_appkey',
            'appsecret' => 'test_appsecret'
        ];
        
        $client = new Client($options);
        
        $this->assertEquals('https://nxh-tp5-dev.iwxapi.cn', $client->API_URL_PREFIX);
    }

    public function testGetAccessTokenWithTokenSet()
    {
        $options = [
            'token' => 'test_token',
            'appkey' => 'test_appkey',
            'appsecret' => 'test_appsecret'
        ];
        
        $client = new Client($options);
        
        $this->assertEquals('test_token', $client->getAccessToken());
    }

    public function testGetAccessTokenWithoutToken()
    {
        $options = [
            'appkey' => 'test_appkey',
            'appsecret' => 'test_appsecret'
        ];
        
        $client = new Client($options);
        
        $this->assertFalse($client->getAccessToken());
    }

    public function testGetOauthUrl()
    {
        $options = [
            'appkey' => 'test_appkey',
            'appsecret' => 'test_appsecret',
            'host' => 'https://example.com'
        ];
        
        $client = new Client($options);
        
        $oauthUrl = $client->getOauth('https://callback.com', 'admin');
        $expected = 'https://example.com/cdp/auth/admin/oauth/authorize?appkey=test_appkey&redirect_uri=' . urlencode('https://callback.com');
        
        $this->assertEquals($expected, $oauthUrl);
    }

    public function testGetOauthUrlForApi()
    {
        $options = [
            'appkey' => 'test_appkey',
            'appsecret' => 'test_appsecret',
            'host' => 'https://example.com'
        ];
        
        $client = new Client($options);
        
        $oauthUrl = $client->getOauth('https://callback.com', 'api');
        $expected = 'https://example.com/cdp/auth/api/oauth/authorize?appkey=test_appkey&redirect_uri=' . urlencode('https://callback.com');
        
        $this->assertEquals($expected, $oauthUrl);
    }

    public function testResetAuth()
    {
        $options = [
            'appkey' => 'test_appkey',
            'appsecret' => 'test_appsecret'
        ];
        
        $client = new Client($options);
        
        $result = $client->resetAuth();
        
        $this->assertTrue($result);
    }

    public function testResetAuthWithAppKey()
    {
        $options = [
            'appkey' => 'test_appkey',
            'appsecret' => 'test_appsecret'
        ];
        
        $client = new Client($options);
        
        $result = $client->resetAuth('another_appkey');
        
        $this->assertTrue($result);
    }
}