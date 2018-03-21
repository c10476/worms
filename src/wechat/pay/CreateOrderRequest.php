<?php

namespace worms\wechat\pay;

/**
 * Class CreateOrderRequest
 * @method CreateOrderRequest setDeviceInfo($val) 设备号
 * @method CreateOrderRequest setNonceStr($val) 随机字符串
 * @method CreateOrderRequest setBody($val) 商品描述
 * @method CreateOrderRequest setDetail($val) 商品详情
 * @method CreateOrderRequest setAttach($val) 附加数据
 * @method CreateOrderRequest setOutTradeNo($val) 商户订单号
 * @method CreateOrderRequest setFeeType($val) 标价币种
 * @method CreateOrderRequest setTotalFee($val) 订单总金额，单位为分，详见支付金额
 * @method CreateOrderRequest setSpbillCreateIp($val) 终端ip
 * @method CreateOrderRequest setTimeStart($val) 订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。
 * @method CreateOrderRequest setTimeExpire($val) 订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。注意：最短失效时间间隔必须大于5分钟
 * @method CreateOrderRequest setGoodsTag($val) 商品标记，使用代金券或立减优惠功能时需要的参数，说明详见代金券或立减优惠
 * @method CreateOrderRequest setNotifyUrl($val) 异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数。
 * @method CreateOrderRequest setTradeType($val) 取值如下：JSAPI，NATIVE，APP
 * @method CreateOrderRequest setProductId($val) trade_type = NATIVE时（即扫码支付），此参数必传。此参数为二维码中包含的商品ID，商户自行定义
 * @method CreateOrderRequest setOpenId($val) trade_type = JSAPI时（即公众号支付），此参数必传，此参数为微信用户在商户对应appid下的唯一标识
 *
 * @method string getDeviceInfo() 设备号
 * @method string getNonceStr() 随机字符串
 * @method string getBody() 商品描述
 * @method string getDetail() 商品详情
 * @method string getAttach() 附加数据
 * @method string getOutTradeNo() 商户订单号
 * @method string getFeeType() 标价币种
 * @method int getTotalFee() 订单总金额，单位为分，详见支付金额
 * @method string getSpbillCreateIp() 终端ip
 * @method string getTimeStart() 订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。
 * @method string getTimeExpire() 订单失效时间，格式为yyyyMMddHHmmss，如2009年12月27日9点10分10秒表示为20091227091010。注意：最短失效时间间隔必须大于5分钟
 * @method string getGoodsTag() 商品标记，使用代金券或立减优惠功能时需要的参数，说明详见代金券或立减优惠
 * @method string getNotifyUrl() 异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数。
 * @method string getTradeType() 取值如下：JSAPI，NATIVE，APP
 * @method string getProductId() trade_type = NATIVE时（即扫码支付），此参数必传。此参数为二维码中包含的商品ID，商户自行定义
 * @method string getOpenId() trade_type = JSAPI时（即公众号支付），此参数必传，此参数为微信用户在商户对应appid下的唯一标识
 * @method string getLimitPay() 上传此参数no_credit--可限制用户不能使用信用卡支付
 *
 * @method CreateOrderResponse send()
 * @method CreateOrderResponse getResponse()
 * package worms\wechat\pay
 */
class CreateOrderRequest extends BaseAbstractRequest
{
    const END_POINT = "https://api.mch.weixin.qq.com/pay/unifiedorder";

    const TRADE_TYPE_NATIVE = 'NATIVE';
    const TRADE_TYPE_APP = 'APP';
    const TRADE_TYPE_JSAPI = 'JSAPI';

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return array
     */
    protected function getData()
    {
        $this->validate(
            'app_id',
            'mch_id',
            'body',
            'out_trade_no',
            'total_fee',
            'notify_url',
            'trade_type',
            'spbill_create_ip'
        );

        $tradeType = strtoupper($this->getTradeType());

        $tradeType == self::TRADE_TYPE_JSAPI && $this->validate('open_id');

        $data = [
            'appid'            => $this->getAppId(),//*
            'mch_id'           => $this->getMchId(),
            'device_info'      => $this->getDeviceInfo(),//*
            'body'             => $this->getBody(),//*
            'detail'           => $this->getDetail(),
            'attach'           => $this->getAttach(),
            'out_trade_no'     => $this->getOutTradeNo(),//*
            'fee_type'         => $this->getFeeType(),
            'total_fee'        => $this->getTotalFee(),//*
            'spbill_create_ip' => $this->getSpbillCreateIp(),//*
            'time_start'       => $this->getTimeStart(),//yyyyMMddHHmmss
            'time_expire'      => $this->getTimeExpire(),//yyyyMMddHHmmss
            'goods_tag'        => $this->getGoodsTag(),
            'notify_url'       => $this->getNotifyUrl(), //*
            'trade_type'       => $this->getTradeType(), //*
            'limit_pay'        => $this->getLimitPay(),
            'openid'           => $this->getOpenId(),//*(trade_type=JSAPI)
            'nonce_str'        => md5(uniqid()),//*
        ];

        $data['sign'] = Helper::sign(array_filter($data), $this->getApiKey());

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

        return $this->response = new CreateOrderResponse($this, Helper::xml2array($response));
    }

    /**
     * @desc   setLimitPay 上传此参数no_credit--可限制用户不能使用信用卡支付
     * @author storn
     * @return $this
     */
    public function setLimitPay()
    {
        $this->parameters->set('limit_pay', 'no_credit');

        return $this;
    }
}
