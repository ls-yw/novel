<?php

namespace application\basic;

use application\library\HelperExtend;
use application\logic\BookLogic;
use application\logic\ConfigLogic;
use woodlsy\phalcon\basic\BasicController;
use woodlsy\phalcon\library\Redis;

class BaseController extends BasicController
{
    public  $isMobile = false;
    private $version  = 20200401;

    protected $page = 1;

    protected $size = 20;

    public $token = null;
    public $user  = null;

    public $config = [];

    public $needResponse = null;

    public function initialize()
    {
        parent::initialize();


        $this->page = (int) $this->get('page', 'int', 1);
        $this->size = (int) $this->get('size', 'int', 20);

//        $this->isMobile = $this->isMobile();
        $this->isMobile = $this->isMHost();

        $this->token = empty($this->getHeader('token')) ? $this->cookies->get('token')->getValue() : $this->getHeader('token');
        $this->needResponse = $this->getHeader('need-response');
        $this->setUser();

        $this->checkLogin();

        $this->loadAlert();

        $this->setCookies();

        $this->view->version = $this->version;

        if ($this->isMobile) {
            $this->size = 10;
            $this->view->setMainView('m');
        }

        $this->config               = (new ConfigLogic())->getPairs('system');

        if ($this->isMobile()) {
            $host = explode('.', $_SERVER['SERVER_NAME']);
            if ('m' !== $host[0] && 'm' !== $host[1]) {
                $this->response->redirect($this->config['m_host'].$_SERVER['REQUEST_URI']);
            }
        }

        $this->view->config         = $this->config;
        $this->view->user           = $this->user;
        $this->view->controllerName = $this->router->getControllerName();

        $this->view->category    = (new BookLogic())->getCategoryPairs();
        $this->view->keywords    = $this->config['host_seo_keywords'];
        $this->view->description = $this->config['host_seo_description'];

    }

    protected function loadAlert()
    {
        $success = Redis::getInstance()->get('success_alert_msg');
        $error   = Redis::getInstance()->get('error_alert_msg');

        Redis::getInstance()->del('success_alert_msg');
        Redis::getInstance()->del('error_alert_msg');

        $this->view->successMsg = $success;
        $this->view->errorMsg   = $error;
    }

    protected function setAlertMsg(string $type, string $message)
    {
        if ('success' === $type) {
            Redis::getInstance()->setex('success_alert_msg', 10, $message);
        }
        if ('error' === $type) {
            Redis::getInstance()->setex('error_alert_msg', 10, $message);
        }
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
        if ($this->router->getControllerName() === 'member' && !in_array($this->router->getActionName(), ['login', 'register']) && !$this->user && !$this->isMobile) {
            if ($this->request->isAjax()) {
                header('Content-type: application/json');
                echo HelperExtend::jsonEncode(['code' => 201, 'msg' => '未登录']);
                exit;
            } else {
                die('<script>alert("未登录请先登录");location.href="/member/login.html"</script>');
            }
        }

        if ($this->isMobile && !$this->user && $this->router->getControllerName() === 'member' && !in_array($this->router->getActionName(), ['login', 'register', 'index', 'book'])) {
            if ($this->request->isAjax()) {
                header('Content-type: application/json');
                echo HelperExtend::jsonEncode(['code' => 201, 'msg' => '未登录']);
                exit;
            } else {
                $this->setAlertMsg('error', "未登录请先登录");
                die('<script>location.href="/member/login.html"</script>');
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

    /**
     * 判断是否是wap域名
     *
     * @author woodlsy
     * @return bool
     */
    public function isMHost()
    {
        $host = $_SERVER['SERVER_NAME'];
        $host = explode('.', $host);
        if ('m' === $host[0] || 'm' === $host[1]) {
            return true;
        }
        return false;
    }

    /**
     * 设置cookies
     *
     * @author woodlsy
     */
    public function setCookies()
    {
        if ($this->isMobile) {
            $this->cookies->set('referer', 'app', (time() + 86400 * 365));
        }
    }

    protected function goBack()
    {
        die('<script>history.go(-1)</script>');
    }

}