# Nanxihang OAuth Client 使用示例

本目录包含 Nanxihang OAuth Client 库的各种使用示例，帮助您快速上手和集成 OAuth 功能。

## 示例列表

### 1. basic_usage.php
基本使用示例，演示如何创建客户端实例、获取访问令牌和生成授权 URL。

```bash
php examples/basic_usage.php
```

### 2. with_cache.php
缓存使用示例，演示如何在业务层实现访问令牌缓存以提高性能。

```bash
php examples/with_cache.php
```

### 3. with_aes.php
AES 加密示例，演示如何使用 AES 加密功能进行安全的 OAuth 通信。

```bash
php examples/with_aes.php
```

### 4. client_external.php
ClientExternal 类使用示例，演示如何使用 ClientExternal 类进行 OAuth 操作。

```bash
php examples/client_external.php
```

### 5. web_integration.php
Web 应用集成示例，演示如何在 Web 应用中集成 OAuth 功能，包括授权流程、令牌管理等。

```bash
php examples/web_integration.php
```

## 配置说明

在运行示例之前，请确保替换示例代码中的以下配置参数：

- `your_app_key` - 您的应用密钥
- `your_app_secret` - 您的应用密钥
- `https://api.example.com` - OAuth 服务的域名
- `your_aes_key_16` - AES 加密密钥（需要16位）
- `your_iv_16` - AES 加密向量（需要16位）

## 使用说明

1. 安装依赖：
   ```bash
   composer install
   ```

2. 根据您的实际情况修改示例中的配置参数

3. 运行示例：
   ```bash
   php examples/basic_usage.php
   ```

## 重要提示

- 请确保在生产环境中妥善保管您的密钥和令牌
- 建议在业务层实现适当的缓存策略来管理访问令牌的生命周期
- AES 加密功能用于保护敏感数据的传输，确保使用安全的密钥和向量
- 访问令牌通常有有效期，请实现适当的刷新机制

这些示例展示了库的主要功能，您可以根据实际需求进行调整和扩展。