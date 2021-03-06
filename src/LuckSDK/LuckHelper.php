<?php
/**
 * LuckHelper.php.
 *
 * Part of Tianyong90\LuckSDK.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author    tianyong90 <412039588@qq.com>
 * @copyright 2016 tianyong90 <412039588@qq.com>
 *
 * @link      https://github.com/tianyong90
 */
namespace Tianyong90\LuckSDK;

/**
 * 调用纳客接口的帮助类.
 */
class LuckHelper
{
    const LUCK_VERSION_CLASSIC = 1; //经典版
    const LUCK_VERSION_POPULAR = 2; //商盟大众版
    const LUCK_VERSION_ULTIMATE = 3; //商盟旗舰版
    const LUCK_VERSION_ENTERPRISE = 4; //企业版

    /**
     * HttpClient.
     *
     * @var Http
     */
    public $http;

    /**
     * 接口URL.
     *
     * @var string
     */
    private $interfaceUrl;

    /**
     * 企业代码
     *
     * @var string
     */
    private $companyCode;

    /**
     * 店铺号，仅商盟旗舰版需要
     *
     * @var string
     */
    private $shopId;

    /**
     * 接口密钥.
     *
     * @var string
     */
    private $interfaceKey;

    /**
     * 对接的纳客系统版本.
     *
     * @var string
     */
    private $luckVersion;

    /**
     * 接口返回结果.
     *
     * @var array
     */
    public $reault = array();

    /**
     * 加/解密器.
     *
     * @var Encryptor
     */
    private $encryptor;

    /**
     * SDK 参数.
     *
     * @var array
     */
    public $option = array();

    /**
     * Constructor.
     *
     * @param array $option
     */
    public function __construct($option)
    {
        //公众号设置的纳客接口地址
        if(!array_key_exists('luck_url', $option) || !$option['luck_url']) {
            throw(new Exception('微少接口地址配置'));
        }
            $this->interfaceUrl = $option['luck_url'];

        //公众号设置的纳客接口密钥
        if(!array_key_exists('luck_key', $option) || !$option['luck_key']) {
            throw(new Exception('微少接口密钥配置'));
        }
        $this->interfaceKey = $option['luck_key'];

        //获取设置的纳客接口版本
        if(!array_key_exists('luck_version', $option) || !$option['luck_version']) {
            throw(new Exception('微少对接会员系统版本配置'));
        }
        $this->luckVersion = $option['luck_version'];

        //企业代码
        if ($this->luckVersion == self::LUCK_VERSION_ENTERPRISE && (!array_key_exists('company_code', $option) || !$option['company_code'])) {
            throw new Exception('', 50004);
        }
        $this->companyCode = $option['company_code'];

        //店铺号，针对商盟旗舰版
        if ($this->luckVersion == self::LUCK_VERSION_ULTIMATE && (!array_key_exists('shop_id', $option) || !$option['shop_id'])) {
            throw new Exception('', 50005);
        }
        $this->shopId = $option['shop_id'];

        //加解密器
        $this->encryptor = new Encryptor($option['luck_key']);

        $this->http = new Http();
    }

    /**
     * 调用纳客接口.
     *
     * @param string $_method 接口方法名
     * @param array  $data    发送的数据
     */
    public function callnake($_method, array $data = array())
    {
        //实际请求地址
        $url = trim($this->interfaceUrl, '/ \/').'/Interface/GeneralInterfaceHandler.ashx';

        //请求方法名加入到参数中
        $data['do'] = $_method;

        //如果是商盟旗舰版，则参数中加入店铺ID
        if ($this->luckVersion === self::LUCK_VERSION_ULTIMATE) {
            $data['ShopID'] = $this->shopId;
        }

        //数据
        foreach ($data as $key => $value) {
            $data[$key] = $this->encryptor->encrypt($value);
        }

        //企业代码不加密
        $data['CompCode'] = $this->companyCode;

        $this->result = $this->http->post($url, $data);

        return $this->result;
    }
}
