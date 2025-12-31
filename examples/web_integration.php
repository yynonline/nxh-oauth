<?php

/**
 * Nanxihang OAuth Web 应用集成示例
 * 
 * 本示例演示了如何在 Web 应用中集成 OAuth 功能
 * 包括授权流程、令牌管理、用户信息获取等
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Nanxihang\Oauth\Client;

// 模拟会话管理
session_start();

// 模拟缓存实现
class WebCache
{
    private static $cache = [];
    
    public static function get($key)
    {
        if (isset(self::$cache[$key]) && self::$cache[$key]['expires'] > time()) {
            return self::$cache[$key]['value'];
        }
        return null;
    }
    
    public static function set($key, $value, $ttl = 1800) // 默认30分钟过期
    {
        self::$cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
    }
    
    public static function delete($key)
    {
        unset(self::$cache[$key]);
    }
    
    public static function getCacheCount()
    {
        return count(self::$cache);
    }
}

class OAuthWebApp
{
    private $client;
    private $cacheKey;
    
    public function __construct($options)
    {
        $this->client = new Client($options);
        $this->cacheKey = 'oauth_token_' . $options['appkey'];
    }
    
    /**
     * 生成授权 URL 并重定向用户
     */
    public function redirectToAuth($callbackUrl, $state = null)
    {
        $authUrl = $this->client->getOauth($callbackUrl, 'admin');
        
        // 保存状态信息到会话
        if ($state) {
            $_SESSION['oauth_state'] = $state;
        }
        
        header("Location: $authUrl");
        exit();
    }
    
    /**
     * 处理授权回调
     */
    public function handleCallback()
    {
        $code = $_GET['code'] ?? null;
        $state = $_GET['state'] ?? null;
        
        // 验证状态
        if (isset($_SESSION['oauth_state']) && $_SESSION['oauth_state'] !== $state) {
            throw new Exception('状态验证失败');
        }
        
        if (!$code) {
            throw new Exception('缺少授权码');
        }
        
        // 使用授权码获取用户信息
        $userInfo = $this->getUserInfo($code);
        
        // 清除状态
        unset($_SESSION['oauth_state']);
        
        return $userInfo;
    }
    
    /**
     * 获取用户信息
     */
    public function getUserInfo($code)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            throw new Exception('无法获取访问令牌');
        }
        
        $userInfo = $this->client->getUserInfo($code, $accessToken);
        if (!$userInfo) {
            throw new Exception('无法获取用户信息');
        }
        
        return $userInfo;
    }
    
    /**
     * 获取访问令牌（优先从缓存获取）
     */
    public function getAccessToken()
    {
        // 首先尝试从缓存获取
        $cachedToken = WebCache::get($this->cacheKey);
        if ($cachedToken) {
            return $cachedToken;
        }
        
        // 如果缓存中没有，则请求新的访问令牌
        $newToken = $this->client->checkAuth();
        
        if ($newToken) {
            // 将新的访问令牌存入缓存
            WebCache::set($this->cacheKey, $newToken);
        }
        
        return $newToken;
    }
    
    /**
     * 清除访问令牌缓存
     */
    public function clearToken()
    {
        WebCache::delete($this->cacheKey);
    }
    
    /**
     * 获取授权 URL
     */
    public function getAuthUrl($callbackUrl, $type = 'admin')
    {
        return $this->client->getOauth($callbackUrl, $type);
    }
}

// 配置参数
$config = [
    'appkey' => 'your_app_key',
    'appsecret' => 'your_app_secret',
    'host' => 'https://api.example.com',
    'aesopen' => 0
];

// 创建 OAuth Web 应用实例
$oauthApp = new OAuthWebApp($config);

try {
    echo "=== Web 应用 OAuth 集成示例 ===\n";
    
    // 检查是否是回调请求
    if (isset($_GET['code'])) {
        // 处理授权回调
        echo "处理授权回调...\n";
        
        $userInfo = $oauthApp->handleCallback();
        
        echo "获取到用户信息:\n";
        print_r($userInfo);
        
    } else {
        // 生成授权 URL
        echo "生成授权 URL...\n";
        $callbackUrl = 'https://your-app.com/oauth/callback';
        $state = bin2hex(random_bytes(16)); // 生成随机状态值
        
        $authUrl = $oauthApp->client->getOauth($callbackUrl, 'admin');
        echo "授权 URL: " . $authUrl . "\n";
        echo "状态值: " . $state . "\n";
        
        // 在实际应用中，您会重定向到授权 URL
        // $oauthApp->redirectToAuth($callbackUrl, $state);
        
        echo "\n注意: 在实际应用中，您需要重定向用户到授权 URL 以开始 OAuth 流程\n";
    }
    
    // 演示令牌管理
    echo "\n=== 令牌管理示例 ===\n";
    
    // 获取访问令牌
    $token = $oauthApp->getAccessToken();
    echo "当前访问令牌: " . ($token ? substr($token, 0, 20) . "..." : '无') . "\n";
    
    // 清除令牌
    $oauthApp->clearToken();
    echo "已清除访问令牌缓存\n";
    
    // 再次获取令牌（会重新请求）
    $newToken = $oauthApp->getAccessToken();
    echo "重新获取的令牌: " . ($newToken ? substr($newToken, 0, 20) . "..." : '无') . "\n";
    
} catch (Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}

// 显示当前缓存状态
echo "\n=== 当前缓存状态 ===\n";
echo "缓存中的令牌数量: " . WebCache::getCacheCount() . "\n";