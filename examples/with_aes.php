<?php

/**
 * Nanxihang OAuth Client AES 加密示例
 * 
 * 本示例演示了如何使用 AES 加密功能进行安全的 OAuth 通信
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Nanxihang\Oauth\Client;

// 配置参数（启用 AES 加密）
$options = [
    'appkey' => 'your_app_key',
    'appsecret' => 'your_app_secret',
    'host' => 'https://api.example.com',
    'aesopen' => 1,           // 启用 AES 加密
    'aeskey' => 'your_aes_key_16',  // AES 加密密钥（需要16位）
    'appiv' => 'your_iv_16'   // AES 加密向量（需要16位）
];

try {
    echo "=== AES 加密 OAuth 示例 ===\n";
    
    // 创建客户端实例
    $client = new Client($options);
    
    echo "OAuth 客户端创建成功（AES 加密已启用）\n";
    echo "API URL: " . $client->API_URL_PREFIX . "\n";
    echo "AES 加密: 已配置（选项 aesopen=1）\n";
    
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
    $oauthUrl = $client->getOauth($callbackUrl, 'admin');
    
    echo "授权 URL: " . $oauthUrl . "\n";
    
    // 演示 AES 加密功能
    echo "\n=== AES 加密功能演示 ===\n";
    
    // 手动测试 AES 加密解密
    $aes = new \Nanxihang\Oauth\Encryption\Aes();
    $aes->setInit(true, $options['aeskey'], $options['appiv']);
    
    $testData = 'Hello, this is a test message!';
    echo "原始数据: " . $testData . "\n";
    
    // 加密
    $encrypted = $aes->encrypt($testData);
    echo "加密后: " . substr($encrypted, 0, 30) . "...\n";
    
    // 解密
    $decrypted = $aes->decrypt($encrypted);
    echo "解密后: " . $decrypted . "\n";
    
    // 验证加解密是否正确
    if ($testData === $decrypted) {
        echo "AES 加解密功能正常工作！\n";
    } else {
        echo "AES 加解密功能出现问题！\n";
    }
    
    // 演示签名生成
    echo "\n=== 签名生成演示 ===\n";
    $time = time();
    $data = [
        'code' => 'test_code',
        'timestamp' => $time
    ];
    
    $signature = $aes->checkTimeSign($time, $data);
    echo "时间戳: " . $time . "\n";
    echo "数据: " . json_encode($data) . "\n";
    echo "生成的签名: " . substr($signature, 0, 30) . "...\n";
    
    // 如果有授权码，获取用户信息（需要实际的授权码）
    // $code = $_GET['code'] ?? '';
    // if (!empty($code)) {
    //     $userInfo = $client->getUserInfo($code);
    //     if ($userInfo) {
    //         echo "\n用户信息: \n";
    //         print_r($userInfo);
    //     } else {
    //         echo "\n获取用户信息失败\n";
    //     }
    // }
    
} catch (Exception $e) {
    echo "发生错误: " . $e->getMessage() . "\n";
}