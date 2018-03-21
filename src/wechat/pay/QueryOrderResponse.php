<?php

namespace worms\wechat\pay;

/**
 * Class UnifiedOrderRequest
 *
 * @package Omnipay\WechatPay\Message
 * @link    https://pay.weixin.qq.com/wiki/doc/api/app.php?chapter=9_1
 */
class QueryOrderResponse extends BaseAbstractResponse
{
    /**
     * SUCCESS—支付成功
     * REFUND—转入退款
     * NOTPAY—未支付
     * CLOSED—已关闭
     * REVOKED—已撤销（刷卡支付）
     * USERPAYING--用户支付中
     * PAYERROR--支付失败(其他原因，如银行返回失败)
     *
     * @desc   getTradeStatus
     * @author storn
     */
    public function getTradeState()
    {
        if ($this->isSuccessful()) {
            $data = $this->getData();

            return $data['trade_state'];
        }

        return null;
    }

    /**
     * @desc   isPayed 是否支付成功
     * @author storn
     * @return bool
     */
    public function isPayed()
    {
        return $this->getTradeState() === 'SUCCESS';
    }

    public function getTransactionId()
    {
        if ($this->isPayed()) {
            $data = $this->getData();

            return $data['transaction_id'];
        }

        return null;
    }

    /**
     * @desc   getOutTradeNo
     * @author storn
     * @return null
     */
    public function getOutTradeNo()
    {
        if ($this->isPayed()) {
            $data = $this->getData();

            return $data['out_trade_no'];
        }

        return null;
    }
}
