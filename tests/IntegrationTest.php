<?php

namespace Nanxihang\Oauth\Tests;

use Nanxihang\Oauth\Client;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    public function testClientFullFlow()
    {
        // 创建一个基本的客户端实例
        $options = [
            'appkey' => 'test_appkey',
            'appsecret' => 'test_appsecret',
            'host' => 'https://example.com',
            'aesopen' => 0, // 禁用AES加密以简化测试
        ];
        
        $client = new Client($options);
        
        // 验证基本属性
        $this->assertEquals('https://example.com', $client->API_URL_PREFIX);
        
        // 验证获取OAuth URL
        $oauthUrl = $client->getOauth('https://callback.com', 'admin');
        $this->assertStringContainsString('https://example.com/cdp/auth/admin/oauth/authorize', $oauthUrl);
        $this->assertStringContainsString('appkey=test_appkey', $oauthUrl);
        $this->assertStringContainsString('redirect_uri=https%3A%2F%2Fcallback.com', $oauthUrl);
        
        // 验证访问令牌
        $this->assertFalse($client->getAccessToken());
        
        // 验证重置认证
        $result = $client->resetAuth();
        $this->assertTrue($result);
    }

    public function testClientWithToken()
    {
        $options = [
            'token' => 'predefined_token',
            'appkey' => 'test_appkey',
            'appsecret' => 'test_appsecret',
        ];
        
        $client = new Client($options);
        
        // 验证预定义的token优先返回
        $this->assertEquals('predefined_token', $client->getAccessToken());
    }

    public function testAesEncryptionFlow()
    {
        $aes = new \Nanxihang\Oauth\Encryption\Aes();
        $aes->setInit(true, '1234567890123456', '1234567890123456');
        
        // 测试加密解密流程
        $original = 'test data to encrypt';
        $encrypted = $aes->encrypt($original);
        $decrypted = $aes->decrypt($encrypted);
        
        $this->assertNotEquals($original, $encrypted);
        $this->assertEquals($original, $decrypted);
        
        // 测试签名生成
        $time = time();
        $data = ['code' => 'test_code'];
        $sign = $aes->checkTimeSign($time, $data);
        
        $this->assertIsString($sign);
        $this->assertNotEmpty($sign);
    }

    public function testClientWithAes()
    {
        $options = [
            'appkey' => 'test_appkey',
            'appsecret' => 'test_appsecret',
            'aesopen' => 1,
            'aeskey' => '1234567890123456',
            'appiv' => '1234567890123456',
        ];
        
        $client = new Client($options);
        
        // We can't access private properties, but we can check that initialization worked
        $this->assertTrue(true); // Basic test to ensure no errors during construction
    }
}