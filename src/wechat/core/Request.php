<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/10
 * Time: 17:45
 */

namespace worms\wechat\core;

abstract class Request extends ResponsePassive
{
    protected $request;
    static protected $eventMapping = [
        'masssendjobfinish'     => 'eventMassSendJobFinish',
        'scancode_waitmsg'      => 'eventScancodeWaitMsg',
        'pic_sysphoto'          => 'eventPicSysPhoto',
        'templatesendjobfinish' => 'eventTemplateSendJobFinish',
    ];
    static protected $clickMapping = [];

    abstract protected function beforeDeal();

    abstract protected function doDefaultEvent();

    abstract protected function doDefaultClick();

    abstract protected function doDefaultMsg();

    /**
     * @descrpition 分发请求
     *
     *
     * @return string
     */
    final public function deal($request)
    {
        $this->request = $request;
        $this->setToUserName($request['tousername']);
        $this->setFromUserName($request['fromusername']);
        switch ($this->request['msgtype']) {
            //事件
            case 'event':
                $this->request['event'] = strtolower($this->request['event']);
                switch ($this->request['event']) {
                    case 'click':
                        //点击事件
                        if (isset(static::$clickMapping[$this->request['event']])) {
                            $method = static::$eventMapping[$this->request['event']];
                        } else {
                            $method = 'click' . ucfirst(str_replace('_', '', ucwords($this->request['event'], '_')));
                        }
                        if (method_exists($this, $method)) {
                            $data = $this->$method();
                        } else {
                            $data = $this->doDefaultClick();
                        }
                        break;
                    default:
                        //其他事件
                        if (isset(static::$eventMapping[$this->request['event']])) {
                            $method = static::$eventMapping[$this->request['event']];
                        } else {
                            $method = 'event' . ucfirst(str_replace('_', '', ucwords($this->request['event'], '_')));
                        }
                        if (method_exists($this, $method)) {
                            $data = $this->$method();
                        } else {
                            $data = $this->doDefaultEvent();
                        }
                        break;
                }
                break;
            default:
                $method = 'handle' . ucfirst($this->request['msgtype']);
                if (method_exists($this, $method)) {
                    $data = $this->$method();
                } else {
                    $data = $this->doDefaultMsg();
                }

                break;
        }

        if (!$data) {
            if ($this->wechat->isDebug())
                return $this->text('没有对应该消息的拦截器，请检查您的代码~');
            else {
                return 'success';
            }
        } else {
            return $data;
        }
    }

}