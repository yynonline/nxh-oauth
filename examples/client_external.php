<?php

/**
 * Nanxihang OAuth ClientExternal 使用示例
 * 
 * 本示例演示了如何使用 ClientExternal 类进行 OAuth 操作
 * ClientExternal 类与 Client 类功能类似，但使用不同的默认配置
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Nanxihang\Oauth\ClientExternal;

// 配置参数
$options = [
    'appkey' => 'your_app_key',
    'appsecret' => 'your_app_secret',
    'host' => 'https://api.example.com',  // OAuth 服务的域名
    'token' => '',  // 可选：预先获取的访问令牌
    'aesopen' => 0, // 是否启用AES加密
    'aeskey' => '', // AES加密密钥
    'appiv' => ''   // AES加密向量
];

try {
    // 创建 ClientExternal 实例
    $client = new ClientExternal($options);
    
    echo "=== ClientExternal OAuth 示例 ===\n";
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
    
    // 生成移动端授权 URL
    $mobileOauthUrl = $client->getOauth($callbackUrl, 'api');
    echo "移动端授权 URL: " . $mobileOauthUrl . "\n";
    
    // 演示手动设置访问令牌
    echo "\n=== 手动设置访问令牌示例 ===\n";
    $predefinedToken = 'manually_set_token_example';
    $clientWithToken = new ClientExternal([
        'token' => $predefinedToken,
        'appkey' => 'test_key',
        'appsecret' => 'test_secret'
    ]);
    
    $token = $clientWithToken->getAccessToken();
    echo "预设令牌: " . $token . "\n";
    
    // 重置认证
    echo "\n=== 重置认证示例 ===\n";
    $resetResult = $clientWithToken->resetAuth();
    echo "重置认证结果: " . ($resetResult ? '成功' : '失败') . "\n";
    
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
    
    echo "\nClientExternal 示例执行完成\n";
    
} catch (Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}