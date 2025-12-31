<?php

namespace Nanxihang\Oauth;

use Nanxihang\Oauth\Encryption\Aes;
use Nanxihang\Oauth\Exception\OauthException;

class ClientExternal
{
    //请求域名
    public $API_URL_PREFIX = 'https://nxh-tp5-dev.iwxapi.cn';
    //token授权
    private $AUTH_URL = '/cdp/auth/api/index/auth?';
    //用户获取
    private $USER_INFO_URL = '/cdp/auth/api/index/userinfo?';
    //授权地址
    public $OAUTH_AUTHORIZE_URL = 'https://nxh-tp5-dev.iwxapi.cn/cdp/auth/api/oauth/authorize?';
    //后台授权地址
    public $OAUTH_AUTHORIZE_URL_ADMIN = 'https://nxh-tp5-dev.iwxapi.cn/cdp/auth/admin/oauth/authorize?';

    private $token;//手动指定token，优先使用
    private $appkey;//秘钥key
    private $appsecret;//秘钥secret
    private $access_token;//token
    private $aesopen;//应用解密
    private $aeskey;//应用解密key
    private $aesiv;//应用解密偏移量
    public $errCode = 40001;
    public $errMsg = "no access";
    
    public function __construct($options = [])
    {
        $this->API_URL_PREFIX = $options['host'] ?? 'https://nxh-tp5-dev.iwxapi.cn';
        
        $this->OAUTH_AUTHORIZE_URL = $this->API_URL_PREFIX . '/cdp/auth/api/oauth/authorize?';
        $this->OAUTH_AUTHORIZE_URL_ADMIN = $this->API_URL_PREFIX . '/cdp/auth/admin/oauth/authorize?';
        
        $this->token = isset($options['token']) ? $options['token'] : '';
        $this->appkey = isset($options['appkey']) ? $options['appkey'] : '';
        $this->appsecret = isset($options['appsecret']) ? $options['appsecret'] : '';
        $this->aesopen = isset($options['aesopen']) ? $options['aesopen'] : 0;
        $this->aeskey = isset($options['aeskey']) ? $options['aeskey'] : '';
        $this->aesiv = isset($options['appiv']) ? $options['appiv'] : '';
    }

    /**
     * 返回access_token
     * @return string|bool
     */
    public function getAccessToken()
    {
        if ($this->token) {
            return $this->token;
        }
        return $this->access_token ?: false;
    }

    /**
     * 获取access_token
     * 注意：缓存逻辑应该在业务端处理
     * @param string $appkey 如在类初始化时已提供，则可为空
     * @param string $appsecret 如在类初始化时已提供，则可为空
     * @param string $token 手动指定access_token，非必要情况不建议用
     */
    public function checkAuth($appkey = '', $appsecret = '', $token = '')
    {
        if (!$appkey || !$appsecret) {
            $appkey = $this->appkey;
            $appsecret = $this->appsecret;
        }
        if ($token) { //手动指定token，优先使用
            $this->access_token = $token;
            return $this->access_token;
        }

        $result = $this->http($this->API_URL_PREFIX . $this->AUTH_URL . 'appkey=' . $appkey . '&secret=' . $appsecret);
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['error']) && $json['error'] != 0)) {
                $this->errCode = $json['error'] ?? $this->errCode;
                $this->errMsg = $json['msg'] ?? $this->errMsg;
                return false;
            }
            $data = $json['data']['result'];
            $this->access_token = $data['access_token'] ?? false;
            return $this->access_token;
        }
        return false;
    }

    /**
     * 删除验证数据
     * @param string $appkey
     */
    public function resetAuth($appkey = '')
    {
        if (!$appkey) {
            $appkey = $this->appkey;
        }
        $this->access_token = '';
        return true;
    }

    /**获取跳转地址
     * @param string $callback
     * @param string $type admin 后台 api移动端
     * @return string
     */
    public function getOauth($callback = '', $type = 'admin')
    {
        $domain = $type == 'admin' ? $this->OAUTH_AUTHORIZE_URL_ADMIN : $this->OAUTH_AUTHORIZE_URL;
        return $domain . 'appkey=' . $this->appkey . '&redirect_uri=' . urlencode($callback);
    }

    /**
     * 通过code获取用户信息
     * @param string $code 授权码
     * @param string $accessToken 访问令牌，如果提供则使用此令牌，否则使用实例中的令牌
     */
    public function getUserInfo($code, $accessToken = null)
    {
        if (empty($code)) return false;
        
        $token = $accessToken ?: $this->access_token;
        if (!$token && !$this->token) {
            if (!$this->checkAuth()) return false;
            $token = $this->access_token;
        } elseif ($this->token) {
            $token = $this->token;
        }
        
        if (!empty($this->aesopen)) {
            $aes = new Aes();
            $aes->setInit(true, $this->aeskey, $this->aesiv);

            //签名生成
            $arrData = array();
            $arrData['code'] = $code;
            $nTime = time();
            $sSign = $aes->checkTimeSign($nTime, $arrData);
            $arrData['__sign'] = $sSign;

            $arrParam = array();
            $arrParam['access_token'] = $token;
            $arrParam['data'] = $aes->encrypt(json_encode($arrData));
            $arrParam['time'] = $nTime;
        } else {
            $arrParam = array();
            $arrParam['access_token'] = $token;
            $arrParam['code'] = $code;
        }

        $result = $this->http($this->API_URL_PREFIX . $this->USER_INFO_URL, $arrParam, 'POST');
        if ($result) {
            $json = json_decode($result, true);
            if (!$json || (isset($json['error']) && $json['error'] != 0)) {
                $this->errCode = $json['error'] ?? $this->errCode;
                $this->errMsg = $json['msg'] ?? $this->errMsg;
                return false;
            }
            $encryptData = $json['data']['result'];
            if (!empty($this->aesopen)) {
                $data = $aes->decrypt($encryptData);
                $data = json_decode($data, true);
            } else {
                $data = $encryptData;
            }
            return $data;
        }
        return false;
    }

    /**
     * curl 请求api
     * @param $url
     * @param array $params
     * @param string $method
     * @param array $header
     * @param bool $multi
     * @return bool|string
     */
    public function http($url, $params = [], $method = 'GET', $header = array(), $multi = false)
    {
        $opts = array();
        if (!empty($header)) {
            $opts[CURLOPT_HTTPHEADER] = $header;
        }
        $opts[CURLOPT_TIMEOUT] = 30;
        $opts[CURLOPT_RETURNTRANSFER] = 1;
        if (stripos($url, "https://") !== FALSE) {
            $opts[CURLOPT_SSL_VERIFYPEER] = false;
            $opts[CURLOPT_SSL_VERIFYHOST] = false;
            $opts[CURLOPT_SSLVERSION] = 1;
        }
        /* 根据请求类型设置特定参数 */
        switch (strtoupper($method)) {
            case 'GET':
                if ($params) {
                    $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                } else {
                    $opts[CURLOPT_URL] = $url;
                }
                break;
            case 'POST':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            case 'PUT':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_CUSTOMREQUEST] = "PUT";
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            case 'DELETE':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_CUSTOMREQUEST] = "DELETE";
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new OauthException('不支持的请求方式！');
        }
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        set_time_limit(30);
        $data = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        return $data;
    }
}