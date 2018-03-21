<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/2/24
 * Time: 12:28
 */

namespace worms\wechat\core;

class TemplateMsg extends Base
{

    /**
     * 向用户推送模板消息
     *
     * @param        $data       = array(
     *                           'first'=>array('value'=>'您好，您已成功消费。', 'color'=>'#0A0A0A')
     *                           'keynote1'=>array('value'=>'巧克力', 'color'=>'#CCCCCC')
     *                           'keynote2'=>array('value'=>'39.8元', 'color'=>'#CCCCCC')
     *                           'keynote3'=>array('value'=>'2014年9月16日', 'color'=>'#CCCCCC')
     *                           'keynote3'=>array('value'=>'欢迎再次购买。', 'color'=>'#173177')
     *                           );
     * @param string $touser     接收方的OpenId。
     * @param string $templateId 模板Id。在公众平台线上模板库中选用模板获得ID
     * @param string $url        URL
     * @param string $topcolor   顶部颜色， 可以为空。默认是红色
     *
     * @return array("errcode"=>0, "errmsg"=>"ok", "msgid"=>200228332} "errcode"是0则表示没有出错
     *
     * 注意：推送后用户到底是否成功接受，微信会向公众号推送一个消息。
     */
    public function sendTemplateMessage($data, $touser, $templateId, $url, $topcolor = '#FF0000')
    {
        $queryUrl                = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->wechat->getAccessToken();
        $template                = [];
        $template['touser']      = $touser;
        $template['template_id'] = $templateId;
        $template['url']         = $url;
        $template['topcolor']    = $topcolor;
        $template['data']        = $data;

        return $this->wechat->getHttp()
            ->setUrl($queryUrl)
            ->post($template);

    }
}