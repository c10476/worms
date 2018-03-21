<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/10
 * Time: 22:47
 */

namespace worms\wechat\request;

use worms\wechat\core\Request;

class DebugRequest extends Request
{
    protected function beforeDeal()
    {

    }

    /**
     * @desc   doDefaultEvent
     * @author storn
     * @return string
     */
    protected function doDefaultEvent()
    {
        return $this->text('默认事件拦截器');
    }

    /**
     * @desc   doDefaultClick
     * @author storn
     * @return string
     */
    protected function doDefaultClick()
    {
        return $this->text('默认点击事件拦截器');
    }

    /**
     * @desc   doDefaultMsg
     * @author storn
     * @return string
     */
    protected function doDefaultMsg()
    {
        return $this->text('默认消息拦截器');
    }

    /**
     * @descrpition 关注
     *
     *
     * @return string
     */
    public function eventSubscribe()
    {
        $content = '欢迎您关注我们的微信，将为您竭诚服务';

        return $this->text($content);
    }

    /**
     * @descrpition 取消关注
     *
     *
     * @return string
     */
    public function eventUnsubscribe()
    {
        $content = '为什么不理我了？';

        return $this->text($content);
    }

    /**
     * @descrpition 扫描二维码关注（未关注时）
     *
     *
     * @return string
     */
    public function eventQrsceneSubscribe()
    {
        $content = '欢迎您关注我们的微信，将为您竭诚服务';

        return $this->text($content);
    }

    /**
     * @descrpition 扫描二维码（已关注时）
     *
     *
     * @return string
     */
    public function eventScan()
    {
        $content = '您已经关注了哦～';

        return $this->text($content);
    }

    /**
     * @descrpition 上报地理位置
     *
     *
     * @return string
     */
    public function eventLocation()
    {
        $content = '收到上报的地理位置';

        return $this->text($content);
    }

    /**
     * @descrpition 自定义菜单 - 点击菜单拉取消息时的事件推送
     *
     *
     * @return string
     */
    public function eventClick()
    {
        //获取该分类的信息
        $eventKey = $this->request['eventkey'];
        $content  = '收到点击菜单事件，您设置的key是' . $eventKey;

        return $this->text($content);
    }

    /**
     * @descrpition 自定义菜单 - 点击菜单跳转链接时的事件推送
     *
     *
     * @return string
     */
    public function eventView()
    {
        //获取该分类的信息
        $eventKey = $this->request['eventkey'];
        $content  = '收到跳转链接事件，您设置的key是' . $eventKey;

        return $this->text($content);
    }

    /**
     * @descrpition 自定义菜单 - 扫码推事件的事件推送
     *
     *
     * @return string
     */
    public function eventScancodePush()
    {
        //获取该分类的信息
        $eventKey = $this->request['eventkey'];
        $content  = '收到扫码推事件的事件，您设置的key是' . $eventKey;
        $content .= '。扫描信息：' . $this->request['scancodeinfo'];
        $content .= '。扫描类型(一般是qrcode)：' . $this->request['scantype'];
        $content .= '。扫描结果(二维码对应的字符串信息)：' . $this->request['scanresult'];

        return $this->text($content);
    }

    /**
     * @descrpition 自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
     *
     *
     * @return string
     */
    public function eventScancodeWaitMsg()
    {
        //获取该分类的信息
        $eventKey = $this->request['eventkey'];
        $content  = '收到扫码推事件且弹出“消息接收中”提示框的事件，您设置的key是' . $eventKey;
        $content .= '。扫描信息：' . $this->request['scancodeinfo'];
        $content .= '。扫描类型(一般是qrcode)：' . $this->request['scantype'];
        $content .= '。扫描结果(二维码对应的字符串信息)：' . $this->request['scanresult'];

        return $this->text($content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出系统拍照发图的事件推送
     *
     *
     * @return string
     */
    public function eventPicSysPhoto()
    {
        //获取该分类的信息
        $eventKey = $this->request['eventkey'];
        $content  = '收到弹出系统拍照发图的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：' . $this->request['sendpicsinfo'];
        $content .= '。发送的图片数量：' . $this->request['count'];
        $content .= '。图片列表：' . $this->request['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：' . $this->request['picmd5sum'];

        return $this->text($content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出拍照或者相册发图的事件推送
     *
     *
     * @return string
     */
    public function eventPicPhotoOrAlbum()
    {
        //获取该分类的信息
        $eventKey = $this->request['eventkey'];
        $content  = '收到弹出拍照或者相册发图的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：' . $this->request['sendpicsinfo'];
        $content .= '。发送的图片数量：' . $this->request['count'];
        $content .= '。图片列表：' . $this->request['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：' . $this->request['picmd5sum'];

        return $this->text($content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出微信相册发图器的事件推送
     *
     *
     * @return string
     */
    public function eventPicWeixin()
    {
        //获取该分类的信息
        $eventKey = $this->request['eventkey'];
        $content  = '收到弹出微信相册发图器的事件，您设置的key是' . $eventKey;
        $content .= '。发送的图片信息：' . $this->request['sendpicsinfo'];
        $content .= '。发送的图片数量：' . $this->request['count'];
        $content .= '。图片列表：' . $this->request['piclist'];
        $content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：' . $this->request['picmd5sum'];

        return $this->text($content);
    }

    /**
     * @descrpition 自定义菜单 - 弹出地理位置选择器的事件推送
     *
     *
     * @return string
     */
    public function eventLocationSelect()
    {
        //获取该分类的信息
        $eventKey = $this->request['eventkey'];
        $content  = '收到点击跳转事件，您设置的key是' . $eventKey;
        $content .= '。发送的位置信息：' . $this->request['sendlocationinfo'];
        $content .= '。X坐标信息：' . $this->request['location_x'];
        $content .= '。Y坐标信息：' . $this->request['location_y'];
        $content .= '。精度(可理解为精度或者比例尺、越精细的话 scale越高)：' . $this->request['scale'];
        $content .= '。地理位置的字符串信息：' . $this->request['label'];
        $content .= '。朋友圈POI的名字，可能为空：' . $this->request['poiname'];

        return $this->text($content);
    }

    /**
     * 群发接口完成后推送的结果
     *
     * 本消息有公众号群发助手的微信号“mphelper”推送的消息
     *

     */
    public function eventMassSendJobFinish()
    {
        //发送状态，为“send success”或“send fail”或“err(num)”。但send success时，也有可能因用户拒收公众号的消息、系统错误等原因造成少量用户接收失败。err(num)是审核失败的具体原因，可能的情况如下：err(10001), //涉嫌广告 err(20001), //涉嫌政治 err(20004), //涉嫌社会 err(20002), //涉嫌色情 err(20006), //涉嫌违法犯罪 err(20008), //涉嫌欺诈 err(20013), //涉嫌版权 err(22000), //涉嫌互推(互相宣传) err(21000), //涉嫌其他
        $status = $this->request['status'];
        //计划发送的总粉丝数。group_id下粉丝数；或者openid_list中的粉丝数
        $totalCount = $this->request['totalcount'];
        //过滤（过滤是指特定地区、性别的过滤、用户设置拒收的过滤，用户接收已超4条的过滤）后，准备发送的粉丝数，原则上，FilterCount = SentCount + ErrorCount
        $filterCount = $this->request['filtercount'];
        //发送成功的粉丝数
        $sentCount = $this->request['sentcount'];
        //发送失败的粉丝数
        $errorCount = $this->request['errorcount'];
        $content    = '发送完成，状态是' . $status . '。计划发送总粉丝数为' . $totalCount . '。发送成功' . $sentCount . '人，发送失败' . $errorCount . '人。';

        return $this->text($content);
    }

    /**
     * 群发接口完成后推送的结果
     *
     * 本消息有公众号群发助手的微信号“mphelper”推送的消息
     *

     */
    public function eventTemplateSendJobFinish()
    {
        //发送状态，成功success，用户拒收failed:user block，其他原因发送失败failed: system failed
        $status = $this->request['status'];
        if ($status == 'success') {
            //发送成功
        } else if ($status == 'failed:user block') {
            //因为用户拒收而发送失败
        } else if ($status == 'failed: system failed') {
            //其他原因发送失败
        }

        return true;
    }

    public function handleText()
    {
        return $this->text("您发送的消息是：" . $this->request['content']);
    }
}