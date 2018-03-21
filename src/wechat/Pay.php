<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/2/8
 * Time: 11:41
 */

namespace worms\wechat;

use GuzzleHttp\Client;
use worms\wechat\pay\BaseAbstractRequest;
use worms\wechat\pay\CloseOrderRequest;
use worms\wechat\pay\CloseOrderResponse;
use worms\wechat\pay\CreateOrderRequest;
use worms\wechat\pay\QueryOrderRequest;
use worms\wechat\pay\QueryRefundRequest;
use worms\wechat\pay\RefundOrderRequest;

class Pay
{

    protected $options = [
        'app_id'      => '',
        'mch_id'      => '',
        'app_key'     => '',
        'cert_path'   => '',
        'key_path'    => '',
        'rootca_path' => '',
    ];

    private $client;

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        $this->client  = new Client(['timeout' => 10]);
    }

    /**
     * @desc   init
     * @author storn
     *
     * @param BaseAbstractRequest $request
     */
    private function init(BaseAbstractRequest $request)
    {
        $request->setAppId($this->options['app_id'])
            ->setMchId($this->options['mch_id'])
            ->setApiKey($this->options['app_key']);
    }

    /**
     * @desc   create
     * @author storn
     * @return CreateOrderRequest
     */
    public function create()
    {
        $request = new CreateOrderRequest($this->client);

        $this->init($request);

        return $request;
    }

    public function query()
    {
        $request = new QueryOrderRequest($this->client);

        $this->init($request);

        return $request;
    }

    /**
     * @desc   close
     * @author storn
     *
     * @param string $outTradeNo
     *
     * @return CloseOrderResponse
     */
    public function close(string $outTradeNo): CloseOrderResponse
    {
        $request = new CloseOrderRequest($this->client);

        $this->init($request);

        return $request
            ->setOutTradeNo($outTradeNo)
            ->send();
    }

    /**
     * @desc   refund
     * @author storn
     * @return RefundOrderRequest
     */
    public function refund()
    {
        $request = new RefundOrderRequest($this->client);

        $this->init($request);

        $request->setCertPath($this->options['cert_path'])
            ->setKeyPath($this->options['key_path'])
            ->setRootcaPath($this->options['rootca_path']);

        return $request;
    }

    /**
     * @desc   queryRefund
     * @author storn
     * @return QueryRefundRequest
     */
    public function queryRefund()
    {
        $request = new QueryRefundRequest($this->client);

        $this->init($request);

        return $request;
    }
}