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
        $this->view->title = '会员中心';
    }

    public function indexAction()
    {
        try {
            if (true === $this->isMobile) {
                $this->mIndex();
                return;
            }

        } catch (NovelException $e) {
            return $this->ajaxReturn(1, $e->getMessage());
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            return $this->ajaxReturn(500, '系统错误');
        }
    }

    /**
     * 我的 wap
     *
     * @author woodlsy
     */
    private function mIndex()
    {
        $this->view->pick('m/member/index');

        $this->view->mMenu = 'member';
    }

    public function loginAction()
    {
        if ($this->request->isAjax()) {
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
                if (!$this->isMobile) {
                    $token = ['username' => $username];
                }
                return $this->ajaxReturn(0, '登录成功', $token);
            } catch (NovelException $e) {
                return $this->ajaxReturn(1, $e->getMessage());
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                return $this->ajaxReturn(500, '系统错误');
            }
        } else {
            if (true === $this->isMobile) {
                $this->mLogin();
                return;
            }

        }
    }

    private function mLogin()
    {
        $this->view->pick('m/member/login');

        $this->view->mMenu = 'none';
    }

    public function registerAction()
    {
        if ($this->request->isAjax()) {
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
                if (!$this->isMobile) {
                    $token = ['username' => $username];
                }
                return $this->ajaxReturn(0, '注册成功', $token);
            } catch (NovelException $e) {
                return $this->ajaxReturn(1, $e->getMessage());
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                return $this->ajaxReturn(500, '系统错误');
            }
        }else {
            if (true === $this->isMobile) {
                $this->mRegister();
                return;
            }
        }
    }

    private function mRegister()
    {
        $this->view->pick('m/member/register');

        $this->view->mMenu = 'none';
    }

    /**
     * 个人信息
     *
     * @author woodlsy
     * @return \Phalcon\Http\ResponseInterface
     */
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

            if (true === $this->isMobile) {
                $this->mInfo();
                return;
            }

        }
    }

    private function mInfo()
    {
        $this->view->pick('m/member/info');
        $this->view->mMenu = 'none';
    }

    /**
     * 修改密码
     *
     * @author woodlsy
     * @return mixed
     */
    public function passwordAction()
    {
        if ($this->request->isPost()) {

            try {
                $oldPassword = $this->post('old_password');
                $password = $this->post('password');
                $repassword = $this->post('repassword');
                if (crypt(md5($oldPassword), $this->user['salt']) !== $this->user['password']) {
                    throw new NovelException('原密码错误');
                }

                if (strlen($password) < 6 || strlen($password) > 20) {
                    throw new NovelException('新密码长度应大于6位小于20位');
                }

                if ($password !== $repassword) {
                    throw new NovelException('两次密码不一致');
                }

                $row = (new MemberLogic())->updatePassword($password, $this->user);
                if (!$row) {
                    throw new NovelException('更新失败');
                }

                $this->cookies->get('token')->delete();

                return $this->dispatcher->forward([
                    'controller' => 'index',
                    'action' => 'error',
                    'params' => ['密码更新成功，请重新登录', '/member/login', 2]
                ]);
            } catch (NovelException $e) {
                return $this->dispatcher->forward([
                    'controller' => 'index',
                    'action' => 'error',
                    'params' => [$e->getMessage(), '-1', 2]
                ]);
            } catch (Exception $e) {
                Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
                return $this->dispatcher->forward([
                    'controller' => 'index',
                    'action' => 'error',
                    'params' => ['系统错误', '-1', 2]
                ]);
            }
        } else {
            $this->view->menuHover = 'password';
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

            if (!$this->user) {
                return $this->ajaxReturn(201, '未登录');
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
        if ($this->request->isAjax()) {
            return $this->ajaxReturn(0, 'ok');
        }
        return $this->response->redirect('/');
    }

    public function bookAction()
    {
        if (true === $this->isMobile) {
            $this->mBook();
            return;
        }

        $userBooks = (new MemberLogic())->getUserBook((int) $this->user['id'], $this->page, $this->size);

        $this->view->userBooks = $userBooks;
        if (true === $this->isMobile) {
            $this->view->pick('member/book-wap');
        }

        $this->view->menuHover = 'book';
    }

    /**
     * 书架 wap
     *
     * @author woodlsy
     */
    private function mBook()
    {
        $userBooks = (new MemberLogic())->getUserBook((int) $this->user['id'], $this->page, $this->size);

        $this->view->userBooks = $userBooks;


        $this->view->pick('m/member/book');

        $this->view->mMenu = 'userBook';
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