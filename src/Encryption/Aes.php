<?php

namespace Nanxihang\Oauth\Encryption;

class Aes
{
    private $hex_iv = null;
    private $key = null;
    private $isOpen = null;
    private $nTimeout = 180;

    function __construct()
    {

    }

    /**
     * 当没有配置信息时候又希望用到，可以通过设置初始化数据
     * @param $isOpen
     * @param $key
     * @param $iv
     * @return void
     */
    public function setInit($isOpen, $key, $iv)
    {
        $this->isOpen = $isOpen;
        $this->key = $key;
        $this->hex_iv = $iv;
    }

    /**
     * 解密数据
     *
     * @param $arrData
     * @return array
     */
    public function decryptData($arrData)
    {
        if ($this->isOpen !== 1) {
            return [0, $arrData];
        }

        if (false == isset($arrData['time'])) {
            return [1, '参数错误，缺少TIME'];
        }

        if (false == isset($arrData['data'])) {
            return [1, '参数错误，缺少data'];
        }

        $nTime = $arrData['time'];

        //校验有效时间，如果前端时间与服务器时间差异过大直接报错
        if ((time() - $nTime) > $this->nTimeout) {
            return [1, 'Outtime sign , LINE:' . __LINE__];
        }


        $arrDeData = $this->decrypt($arrData['data']);
        $arrDeData = empty($arrDeData) ? array() : json_decode($arrDeData, true);
        if (false == isset($arrDeData['__sign']) || strlen($arrDeData['__sign']) <= 0) {
            return [1, '参数错误，缺少__sign' . __LINE__];
        }
        $sAccesSign = $arrDeData['__sign'];
        unset($arrDeData['__sign']);
        //校验sign
        if ($this->checkTimeSign($nTime, $arrDeData) != $sAccesSign) {
            return [1, '__sign 签名错误' . __LINE__];
        }

        //返回解密数据
        return [0, $arrDeData];
    }


    /**
     * 数据加密操作
     * @param $input
     * @return false|string
     */
    public function encrypt($input)
    {
        if (is_null($this->key) || is_null($this->hex_iv)) {
            return false;
        }
        $data = openssl_encrypt($input, 'AES-128-CBC', $this->key, OPENSSL_RAW_DATA, $this->hex_iv);
        $data = base64_encode($data);
        return $data;
    }

    /**
     * 数据解密
     *
     * @param $input
     * @return false|string
     */
    public function decrypt($input)
    {
        if (is_null($this->key) || is_null($this->hex_iv)) {
            return false;
        }

        $decrypted = openssl_decrypt(base64_decode($input, true), 'AES-128-CBC', $this->key, OPENSSL_RAW_DATA, $this->hex_iv);
        return $decrypted;
    }

    /*
    * 验证token获取
    */
    public function checkTimeSign($nTime, $arrData = array())
    {
        if (strlen($nTime) <= 0) {
            return false;
        }
        $arrParam = $arrData;
        ksort($arrParam);
        $arrParam = array_values($arrParam);
        $arrParam[] = $nTime;
        $sParam = strtolower(md5(implode($arrParam)));
        $sSign = $this->encrypt($sParam);
        return $sSign;
    }
}