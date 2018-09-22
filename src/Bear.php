<?php

/**
 * Created by zithan.
 * User: zithan <zithan@163.com>
 */

namespace Xjw\BearSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Xjw\BearSdk\Exceptions\BearException;
use Xjw\BearSdk\Exceptions\HttpException;
use Xjw\BearSdk\Exceptions\MsgCryptErrorException;
use Xjw\BearSdk\Support\Config;
use Xjw\BearSdk\Support\MsgCrypt;
use Xjw\BearSdk\Support\Json;

class Bear
{
    protected $config;
    protected $guzzleOptions = [];
    protected $aesKey;
    protected $aesIV;
    protected $makerUniqueId;

    public function __construct(array $config)
    {
        $this->config = new Config($config);

        $this->aesKey = $this->config->get('bear_params.aes_key');
        $this->aesIV = $this->config->get('bear_params.aes_iv');
        $this->makerUniqueId = $this->config->get('bear_params.maker_unique_id');

        $guzzleOptions = [
            'base_uri' => $this->config->get('base_uri'),
            'timeout' => $this->config->get('timeout'),
        ];
        $this->setGuzzleOptions($guzzleOptions);
    }

    public function getHttpClient()
    {
        return new Client($this->guzzleOptions);
    }

    public function setGuzzleOptions(array $options)
    {
        $this->guzzleOptions = $options;
    }

    public function getToken()
    {
        $formParams = array_filter([
            'grant_type' => $this->config->get('bear_params.grant_type'),
            'access_key_id' => $this->config->get('bear_params.access_key_id'),
            'access_key_secret' => $this->config->get('bear_params.access_key_secret'),
            'timestamp' => time(),
        ]);

        try {
            $response = $this->getHttpClient()->post('token/get', ['form_params' => $formParams])->getBody()->getContents();
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        $response = json_decode($response);

        if ($response->errcode != 0) {
            throw new BearException($response->msg);
        }

        if (!$result =  MsgCrypt::decrypt($response->result, $this->aesKey, $this->aesIV)) {
            throw new MsgCryptErrorException('解密出错了，请检查参数正常性');
        }

        return json_decode($result, true);
    }

    public function push(array $auth, array $push)
    {
        $uri = 'goods/pull';
        $timestamp = time();

        $orders = [
            'order_sn' => $push['orders_sn'],
            'logistics_title' => $push['logistics_title'],
            'linkman' => $push['linkman'],
            'phone' => $push['mobile'],
            'address' => $push['address'],
            'deliver_expired_at' => $push['deliver_expired_at'],
            'goods_list' => $push['goods_list'],
        ];

        $xData = [
            'maker_unique_id' => $this->makerUniqueId,
            'notify_url' => $this->config->get('bear_params.notify_url'),
            'orders' => urlencode(Json::encode($orders)),
        ];
        $xData = MsgCrypt::encrypt(Json::encode($xData), $this->config->get('bear_params.aes_key'), $this->config->get('bear_params.aes_iv'));

        $sign = md5($this->config->get('base_uri') . $uri . "?openid=" . $auth['openid'] ."&timestamp=". $timestamp . "&token=" . $auth['token']);

        try {
            $response = $this->getHttpClient()->request('POST', $uri . '?openid=' . $auth['openid'] . '&timestamp=' . $timestamp . "&sign=" . $sign, [
                'form_params' => [
                    'xData' => $xData
                ]
            ])->getBody()->getContents();
        } catch (GuzzleException $e) {
            throw new HttpException($e->getMessage(), $e->getCode(), $e);
        }

        $response = json_decode($response);

        if (!is_object($response)) {
            throw new BearException('笨熊返回信息异常');
        }

        if ($response->errcode != 0) {
            throw new BearException($response->msg);
        }

        if (!$result =  MsgCrypt::decrypt($response->result->xData, $this->aesKey, $this->aesIV)) {
            throw new MsgCryptErrorException('解密出错了，请检查参数正常性');
        }

        return json_decode($result, true);
    }
}
