<?php
namespace application\controllers;

use application\basic\BaseController;
use application\library\NovelException;
use application\logic\BookLogic;
use application\logic\MemberLogic;
use Exception;
use woodlsy\phalcon\library\Log;
use woodlsy\phalcon\library\Redis;

class MemberController extends BaseController
{
    public function initialize()
    {
        parent::initialize();

    }

    public function indexAction()
    {
        try {



            $this->view->user = $this->user;

            if (true === $this->isMobile) {
                $this->view->pick('member/index-wap');
            }

        } catch (NovelException $e) {
            return $this->ajaxReturn(1, $e->getMessage());
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            return $this->ajaxReturn(500, '系统错误');
        }
    }

    public function loginAction()
    {
        try {
            $username = $this->post('username', 'string');
            $password = $this->post('password', 'string');
            $username = preg_replace('/[^\w]/i', '', $username);

            if (strlen($username) < 6) {
                throw new NovelException('用户名必须为字母且大于6位');
            }

            if (strlen($password) < 6) {
                throw new NovelException('密码必须大于6位');
            }

            $token = (new MemberLogic())->login($username, $password);
            return $this->ajaxReturn(0, '登录成功', $token);
        } catch (NovelException $e) {
            return $this->ajaxReturn(1, $e->getMessage());
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            return $this->ajaxReturn(500, '系统错误');
        }
    }

    public function registerAction()
    {
        try {
            $username = $this->post('username', 'string');
            $password = $this->post('password', 'string');
            $num = preg_match('/[^a-zA-Z]/i', $username);
            if ($num > 0 || strlen($username) < 6) {
                throw new NovelException('用户名必须为字母且大于6位');
            }

            if (strlen($username) > 20) {
                throw new NovelException('用户名最多20位');
            }

            if (strlen($password) < 6) {
                throw new NovelException('密码必须大于6位');
            }

            $token = (new MemberLogic())->register($username, $password);
            return $this->ajaxReturn(0, '注册成功', $token);
        } catch (NovelException $e) {
            return $this->ajaxReturn(1, $e->getMessage());
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            return $this->ajaxReturn(500, '系统错误');
        }
    }

    public function infoAction()
    {
        if ($this->request->isAjax()) {
            try {
                $data = [
                    'id' => $this->user['id'],
                    'username' => $this->user['username'],
                ];
                return $this->ajaxReturn(0, 'ok', $data);
            } catch (NovelException $e) {
                return $this->ajaxReturn(1, $e->getMessage());
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                return $this->ajaxReturn(500, '系统错误');
            }
        } else {
            $this->view->user = $this->user;

            if (true === $this->isMobile) {
                $this->view->pick('member/info-wap');
            }
        }
    }

    public function addBookAction()
    {
        try {
            $id = (int) $this->get('id');
            $book = (new BookLogic())->getById($id);
            if (empty($book)) {
                throw new NovelException('小说不存在');
            }

            $userBook = (new MemberLogic())->getUserBookByBookId((int)$this->user['id'], $id);
            if ($userBook) {
                return $this->ajaxReturn(0, '该小说已在您的书架中');
            }

            $count = (new MemberLogic())->getUserBookCount((int)$this->user['id']);
            if ($count >= 5) {
                throw new NovelException('书架最多放置5本小说，请先删除再添加');
            }

            $row =(new MemberLogic())->addUserBook((int)$this->user['id'], $id);
            if (!$row) {
                throw new NovelException('加入书架失败');
            }

            return $this->ajaxReturn(0, 'ok');
        } catch (NovelException $e) {
            return $this->ajaxReturn(1, $e->getMessage());
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            return $this->ajaxReturn(500, '系统错误');
        }
    }

    public function logoutAction()
    {
        $this->cookies->get('token')->delete();
        Redis::getInstance()->del($this->token);
        return $this->ajaxReturn(0, 'ok');
    }

    public function bookAction()
    {
        $userBooks = (new MemberLogic())->getUserBook((int) $this->user['id'], $this->page, $this->size);

        $this->view->userBooks = $userBooks;
        $this->view->user = $this->user;
        if (true === $this->isMobile) {
            $this->view->pick('member/book-wap');
        }
    }

    public function delBookAction()
    {
        try {
            $id = (int) $this->post('id');
            $userBook = (new MemberLogic())->getUserBookById((int) $this->user['id'], $id);
            if (empty($userBook)) {
                throw new NovelException('小说不存在');
            }

            $row = (new MemberLogic())->delUserBook((int) $this->user['id'], $id);

            if (!$row) {
                throw new NovelException('移除失败');
            }

            return $this->ajaxReturn(0, 'ok');
        } catch (NovelException $e) {
            return $this->ajaxReturn(1, $e->getMessage());
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            return $this->ajaxReturn(500, '系统错误');
        }
    }

}