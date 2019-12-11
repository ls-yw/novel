<?php

namespace application\basic;

use application\library\HelperExtend;
use application\logic\BookLogic;
use application\logic\ConfigLogic;
use Phalcon\Mvc\View;
use woodlsy\phalcon\basic\BasicController;
use woodlsy\phalcon\library\Redis;

class BaseController extends BasicController
{
    public $isMobile = false;

    protected $page = 1;

    protected $size = 20;

    public $token = null;
    public $user  = null;

    public $config = [];

    public function initialize()
    {
        parent::initialize();

        $this->page = (int) $this->get('page', 'int', 1);
        $this->size = (int) $this->get('size', 'int', 20);

        $this->isMobile = $this->isMobile();

        $this->token = !$this->getHeader('token') ? $this->cookies->get('token')->getValue() : $this->getHeader('token');
        $this->setUser();

        $this->checkLogin();

        $this->view->version = 20191120;

        if ($this->isMobile) {
            $this->view->disableLevel(
                View::LEVEL_MAIN_LAYOUT
            );
        }

        $this->config       = (new ConfigLogic())->getPairs('system');
        $this->view->config = $this->config;
        $this->view->user   = $this->user;
        $this->view->controllerName   = $this->router->getControllerName();

        $this->view->category = (new BookLogic())->getCategoryPairs();
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
            $user       = Redis::getInstance()->get($this->token);
            $this->user = HelperExtend::jsonDecode($user);
        }
    }

    public function checkLogin()
    {
        if ($this->router->getControllerName() === 'member' && !in_array($this->router->getActionName(), ['login', 'register']) && !$this->user) {
            if ($this->request->isAjax()) {
                header('Content-type: application/json');
                echo HelperExtend::jsonEncode(['code' => 201, 'msg' => '未登录']);
                exit;
            } else {
                die('<script>alert("未登录请先登录");location.href="/member/login"</script>');
            }
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