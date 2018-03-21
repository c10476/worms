<?php

namespace worms\wechat\pay;

use worms\wechat\core\WechatException;

/**
 * Class CloseOrderRequest
 *
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_3&index=5
 * @method CloseOrderResponse send()
 * @method CloseOrderResponse getResponse()
 *
 * @method CloseOrderRequest setOutTradeNo($val) 商户订单号
 * @method string getOutTradeNo() 商户订单号
 */
class CloseOrderRequest extends BaseAbstractRequest
{

    const END_POINT = 'https://api.mch.weixin.qq.com/pay/closeorder';

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return array
     * @throws WechatException
     */
    protected function getData()
    {

        $this->validate('app_id', 'mch_id', 'out_trade_no');

        $data = [
            'appid'        => $this->getAppId(),
            'mch_id'       => $this->getMchId(),
            'out_trade_no' => $this->getOutTradeNo(),
            'nonce_str'    => md5(uniqid()),
        ];

        $data = array_filter($data);

        $data['sign'] = Helper::sign($data, $this->getApiKey());

        return $data;
    }

    /**
     * Send the request with specified data
     *
     * @param  mixed $data The data to send
     *
     * @return BaseAbstractResponse
     */
    protected function sendData($data)
    {
        $request  = $this->httpClient->post(self::END_POINT, ['body' => Helper::array2xml($data)]);
        $response = $request->getBody()->getContents();

        return $this->response = new CloseOrderResponse($this, Helper::xml2array($response));
    }
}
