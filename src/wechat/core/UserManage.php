<?php
/**
 * Created by PhpStorm.
 * User: storn
 * Date: 2017/1/12
 * Time: 15:40
 */

namespace worms\wechat\core;

class UserManage extends Base
{

    /**
     * @descrpition 创建分组
     *
     * @param string $groupName 组名 UTF-8
     */
    public function createGroup($groupName)
    {
        //获取ACCESS_TOKEN
        $this->wechat->getHttp()
            ->setUrl('https://api.weixin.qq.com/cgi-bin/groups/create')
            ->post(['group' => ['name' => $groupName]]);
    }

    /**
     * @descrpition 获取分组列表
     * @return array
     */
    public function getGroupList()
    {
        //获取ACCESS_TOKEN
        return $this->wechat->getHttp()
            ->setUrl('https://api.weixin.qq.com/cgi-bin/groups/get')
            ->get();
    }

    /**
     * @descrpition 查询用户所在分组
     *
     * @param string $openId 用户唯一OPENID
     *
     * @return array  ['groupid'=>0]
     */
    public function getGroupByOpenId($openId)
    {
        return $this->wechat->getHttp()
            ->setUrl('https://api.weixin.qq.com/cgi-bin/groups/getid')
            ->post(['openid' => $openId]);

    }

    /**
     * @descrpition 修改分组名
     *
     * @param string $groupId   要修改的分组ID
     * @param string $groupName 新分组名
     */
    public function editGroupName($groupId, $groupName)
    {
        //获取ACCESS_TOKEN
        $this->wechat->getHttp()
            ->setUrl('https://api.weixin.qq.com/cgi-bin/groups/update')
            ->post(['group' => ['id' => $groupId, 'name' => $groupName]]);
    }

    /**
     * @descrpition 移动用户分组
     *
     * @param string $openid     要移动的用户OpenId
     * @param string $to_groupid 移动到新的组ID
     */
    public function editUserGroup($openid, $to_groupid)
    {
        $this->wechat->getHttp()
            ->setUrl('https://api.weixin.qq.com/cgi-bin/groups/members/update')
            ->post(['openid' => $openid, 'to_groupid' => $to_groupid]);

    }

    //-----------------------------用-------户-------管--------理----------------------

    /**
     * @descrpition 获取用户基本信息
     *
     * @param string $openId 用户唯一OpenId
     *
     * @return User
     */
    public function getUser($openId)
    {
        $info = $this->wechat->getHttp()
            ->setUrl('https://api.weixin.qq.com/cgi-bin/user/info')
            ->setPrameter('openid', $openId)
            ->get();

        return new User($info, $this->wechat);
    }

    /**
     * @descrpition 获取关注者列表
     *
     * @param string $next_openid 第一个拉取的OPENID，不填默认从头开始拉取
     *
     * JSON {"total":2,"count":2,"data":{"openid":["OPENID1","OPENID2"]},"next_openid":"NEXT_OPENID"}
     *
     * @return array
     */
    public function getFansList($next_openid = '')
    {
        $http = $this->wechat->getHttp()
            ->setUrl('https://api.weixin.qq.com/cgi-bin/user/get');
        if ($next_openid) {

            $http->setPrameter('next_openid', $next_openid);
        }
        return $http->get();
    }

    /**
     * 设置备注名 开发者可以通过该接口对指定用户设置备注名，该接口暂时开放给微信认证的服务号。
     *
     * @param string $openId 用户的openId
     * @param string $remark 新的昵称
     *
     * array('errorcode'=>0, 'errmsg'=>'ok') 正常时是0
     *
     */
    public function setRemark($openId, $remark)
    {
        $this->wechat->getHttp()
            ->setUrl('https://api.weixin.qq.com/cgi-bin/user/info/updateremark')
            ->post(['openid' => $openId, 'remark' => $remark]);
    }
}