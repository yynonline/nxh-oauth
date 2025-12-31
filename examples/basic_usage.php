<?php

/**
 * Nanxihang OAuth Client 基本使用示例
 * 
 * 本示例演示了如何使用 Client 类进行基本的 OAuth 操作
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Nanxihang\Oauth\Client;

// 配置参数
$options = [
    'appkey' => '',
    'appsecret' => '',
    'host' => 'http://nxh-tp5.iwxapi.cn',  // OAuth 服务的域名
    'token' => '',  // 可选：预先获取的访问令牌
    'aesopen' => 0, // 是否启用AES加密
    'aeskey' => '', // AES加密密钥
    'appiv' => ''   // AES加密向量
];

try {
    // 创建客户端实例
    $client = new Client($options);
    
    echo "OAuth 客户端创建成功\n";
    echo "API URL: " . $client->API_URL_PREFIX . "\n";
    
    // 获取访问令牌
    echo "\n正在获取访问令牌...\n";
    $accessToken = $client->checkAuth();
    
    if ($accessToken) {
        echo "访问令牌获取成功: " . substr($accessToken, 0, 20) . "...\n";
    } else {
        echo "访问令牌获取失败\n";
        echo "错误代码: " . $client->errCode . "\n";
        echo "错误信息: " . $client->errMsg . "\n";
    }
    
    // 生成 OAuth 授权 URL
    echo "\n生成 OAuth 授权 URL...\n";
    $callbackUrl = 'https://your-app.com/callback';
    $oauthUrl = $client->getOauth($callbackUrl, 'admin'); // 'admin' 表示后台授权，'api' 表示API授权
    
    echo "授权 URL: " . $oauthUrl . "\n";
    
    // 如果有授权码，获取用户信息
    // 注意：在实际使用中，您需要先通过授权流程获取 code
    // $code = $_GET['code'] ?? '';
    // if (!empty($code)) {
    //     $userInfo = $client->getUserInfo($code);
    //     if ($userInfo) {
    //         echo "用户信息: \n";
    //         print_r($userInfo);
    //     } else {
    //         echo "获取用户信息失败\n";
    //     }
    // }
    
} catch (Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}