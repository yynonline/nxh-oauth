# Nanxihang OAuth Client

南熙航SaaS环境三方授权客户端库，用于与南熙航OAuth服务进行交互。此库专注于提供核心OAuth功能，缓存和配置逻辑由业务端处理。

## 安装

使用Composer安装：

```bash
composer require nanxihang/oauth
```

```plaintext
dev host: https://nxh-tp5-dev.iwxapi.cn
prod host: https://api-app.iwxapi.com
```

## 使用方法

### 基本用法

```php
<?php

require_once 'vendor/autoload.php';

use Nanxihang\Oauth\Client;

// 配置选项
$options = [
    'appkey' => 'your_app_key',
    'appsecret' => 'your_app_secret',
    'host' => 'https://your-api-domain.com',
    'aesopen' => 1, // 是否启用AES加密
    'aeskey' => 'your_aes_key',
    'appiv' => 'your_aes_iv'
];

// 创建客户端实例
$client = new Client($options);

// 获取访问令牌
$accessToken = $client->getAccessToken();

// 获取OAuth授权URL
$oauthUrl = $client->getOauth('https://your-callback-url.com', 'admin');

// 通过code获取用户信息
$userInfo = $client->getUserInfo('code');
```

## API

### Client

- `__construct($options)` - 构造函数
- `getAccessToken()` - 获取访问令牌（仅返回已存在的令牌）
- `checkAuth($appkey = '', $appsecret = '', $token = '')` - 检查认证并获取新令牌
- `resetAuth($appkey = '')` - 重置认证
- `getOauth($callback = '', $type = 'admin')` - 获取OAuth授权URL
- `getUserInfo($code, $accessToken = null)` - 通过code获取用户信息，可选择性地传递访问令牌
- `http($url, $params = [], $method = 'GET', $header = [], $multi = false)` - HTTP请求方法

### ClientExternal

与Client类具有相同的API，但使用不同的默认配置。

## 配置选项

- `appkey` - 应用的API密钥
- `appsecret` - 应用的API密钥
- `host` - API服务器地址
- `token` - 手动指定的访问令牌
- `aesopen` - 是否启用AES加密
- `aeskey` - AES加密密钥
- `appiv` - AES加密偏移量

## 缓存

缓存逻辑由业务端处理，此库不包含内置缓存功能。建议在业务层实现适当的缓存策略来管理访问令牌的生命周期。

## 许可证

MIT