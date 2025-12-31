<?php

/**
 * Nanxihang OAuth Client 缓存使用示例
 * 
 * 本示例演示了如何在业务层实现访问令牌缓存
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Nanxihang\Oauth\Client;

// 模拟缓存实现（在实际项目中，您可能使用 Redis、Memcached 或文件缓存）
class SimpleCache
{
    private static $cache = [];
    
    public static function get($key)
    {
        return self::$cache[$key] ?? null;
    }
    
    public static function set($key, $value, $ttl = 3600)
    {
        self::$cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
    }
    
    public static function has($key)
    {
        if (!isset(self::$cache[$key])) {
            return false;
        }
        
        if (self::$cache[$key]['expires'] < time()) {
            unset(self::$cache[$key]); // 过期则删除
            return false;
        }
        
        return true;
    }
}

class OAuthService
{
    private $client;
    private $appKey;
    
    public function __construct($options)
    {
        $this->client = new Client($options);
        $this->appKey = $options['appkey'];
    }
    
    /**
     * 获取访问令牌，优先从缓存获取
     */
    public function getAccessToken()
    {
        $cacheKey = 'oauth_access_token_' . $this->appKey;
        
        // 首先尝试从缓存获取
        if (SimpleCache::has($cacheKey)) {
            $token = SimpleCache::get($cacheKey);
            echo "从缓存获取访问令牌\n";
            return $token;
        }
        
        echo "缓存中未找到访问令牌，正在请求新的令牌...\n";
        
        // 如果缓存中没有，则请求新的访问令牌
        $token = $this->client->checkAuth();
        
        if ($token) {
            // 将新的访问令牌存入缓存，设置过期时间（例如30分钟）
            SimpleCache::set($cacheKey, $token, 1800);
            echo "新的访问令牌已存入缓存\n";
        }
        
        return $token;
    }
    
    /**
     * 通过授权码获取用户信息
     */
    public function getUserInfo($code)
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            throw new Exception('无法获取访问令牌');
        }
        
        return $this->client->getUserInfo($code, $accessToken);
    }
    
    /**
     * 生成 OAuth 授权 URL
     */
    public function getOauthUrl($callback, $type = 'admin')
    {
        return $this->client->getOauth($callback, $type);
    }
}

// 使用示例
$options = [
    'appkey' => 'your_app_key',
    'appsecret' => 'your_app_secret',
    'host' => 'https://api.example.com',
    'aesopen' => 0
];

try {
    $oauthService = new OAuthService($options);
    
    echo "=== OAuth 缓存示例 ===\n";
    
    // 第一次获取访问令牌（会请求API并缓存）
    $token1 = $oauthService->getAccessToken();
    echo "第一次获取的令牌: " . ($token1 ? substr($token1, 0, 20) . "..." : '失败') . "\n\n";
    
    // 第二次获取访问令牌（应该从缓存获取）
    $token2 = $oauthService->getAccessToken();
    echo "第二次获取的令牌: " . ($token2 ? substr($token2, 0, 20) . "..." : '失败') . "\n\n";
    
    // 生成授权 URL
    $authUrl = $oauthService->getOauthUrl('https://your-app.com/callback', 'admin');
    echo "授权 URL: " . $authUrl . "\n\n";
    
    // 模拟使用授权码获取用户信息（需要实际的授权码）
    // $userInfo = $oauthService->getUserInfo('authorization_code_here');
    // if ($userInfo) {
    //     echo "用户信息: \n";
    //     print_r($userInfo);
    // }
    
} catch (Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}