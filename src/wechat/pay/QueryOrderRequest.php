<?php
namespace worms\wechat\pay;

use worms\wechat\core\WechatException;

/**
 * Class QueryOrderRequest
 *
 * @method CreateOrderRequest setOutTradeNo($val) 商户订单号
 * @method CreateOrderRequest setTransactionId($val) 微信订单号
 *
 * @method string getOutTradeNo() 商户订单号
 * @method string getTransactionId() 微信订单号
 * @method QueryOrderResponse send()
 * @method QueryOrderResponse getResponse()
 */
class QueryOrderRequest extends BaseAbstractRequest
{

    const END_POINT = 'https://api.mch.weixin.qq.com/pay/orderquery';

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return array
     * @throws WechatException
     */
    public function getData(): array
    {
        $this->validate('app_id', 'mch_id');

        if (!$this->getTransactionId() && !$this->getOutTradeNo()) {
            throw new WechatException("The 'transaction_id' or 'out_trade_no' parameter is required");

        }

        $data = [
            'appid'          => $this->getAppId(),
            'mch_id'         => $this->getMchId(),
            'transaction_id' => $this->getTransactionId(),
            'out_trade_no'   => $this->getOutTradeNo(),
            'nonce_str'      => md5(uniqid()),
        ];

        $data = array_filter($data);

        $data['sign'] = Helper::sign($data, $this->getApiKey());

        return $data;
    }

    /**
     * Send the request with specified data
     *
     * @param  array $data The data to send
     *
     * @return QueryOrderResponse
     */
    public function sendData($data)
    {
        $request  = $this->httpClient->post(self::END_POINT, ['body' => Helper::array2xml($data)]);
        $response = $request->getBody()->getContents();

        return $this->response = new QueryOrderResponse($this, Helper::xml2array($response));
    }
}
