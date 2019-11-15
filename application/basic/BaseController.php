<?php

namespace application\basic;

use woodlsy\phalcon\basic\BasicController;
use woodlsy\phalcon\library\Helper;
use woodlsy\phalcon\library\Redis;

class BaseController extends BasicController
{
    public $isMobile = false;

    protected $page = 1;

    protected $size = 20;

    public $token = null;
    public $user  = null;

    public function initialize()
    {
        parent::initialize();

        $this->page = (int) $this->get('page', 'int', 1);
        $this->size = (int) $this->get('size', 'int', 20);

        $this->isMobile = $this->isMobile();

        $this->token = !$this->getHeader('token') ? $this->cookies->get('token')->getValue() : $this->getHeader('token');
        $this->setUser();
    }

    /**
     * 获取登录信息
     *
     * @author yls
     */
    private function setUser()
    {
        if (!$this->token) {
            return;
        }
        if (Redis::getInstance()->exists($this->token)) {
            $admin      = Redis::getInstance()->get($this->token);
            $this->user = Helper::jsonDecode($admin);
        }
    }

    /**
     * 判断是否是手机端
     *
     * @author yls
     * @return bool
     */
    public function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高。其中'MicroMessenger'是电脑微信
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile', 'MicroMessenger');
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }

}