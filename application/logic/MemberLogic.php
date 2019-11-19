<?php

namespace application\logic;

use application\library\Helper;
use application\library\NovelException;
use application\models\Article;
use application\models\Book;
use application\models\User;
use application\models\UserBook;
use Phalcon\DI;
use woodlsy\phalcon\library\Redis;

class MemberLogic
{
    /**
     * 注册
     *
     * @author yls
     * @param string $username
     * @param string $password
     * @return string
     * @throws NovelException
     */
    public function register(string $username, string $password)
    {
        $user = $this->getUserByUsername($username);
        if ($user) {
            throw new NovelException('该用户名已存在');
        }

        $salt = Helper::randString(8);

        $data = [
            'username' => $username,
            'password' => crypt(md5($password), $salt),
            'salt'     => $salt,
        ];

        $userId = (new User())->insertData($data);
        if (!$userId) {
            throw new NovelException('注册失败');
        }

        $user = (new User())->getById($userId);

        $token = md5($user['id'] . time());
        Redis::getInstance()->setex($token, 86400 * 7, \woodlsy\phalcon\library\Helper::jsonEncode($user));
        DI::getDefault()->get('cookies')->set('token', $token, time() + 7 * 86400);
        return $token;
    }

    /**
     * 登录
     *
     * @author yls
     * @param string $username
     * @param string $password
     * @return string
     * @throws NovelException
     */
    public function login(string $username, string $password)
    {
        $user = $this->getUserByUsername($username);
        if (!$user) {
            throw new NovelException('用户名不存在');
        }

        if (crypt(md5($password), $user['salt']) !== $user['password']) {
            throw new NovelException('密码错误');
        }

        (new User())->updateData(['last_ip' => DI::getDefault()->get('request')->getClientAddress(), 'last_time' => \woodlsy\phalcon\library\Helper::now(), 'count' => 'count + 1'], ['id' => $user['id']]);

        $token = md5($user['id'] . time());
        Redis::getInstance()->setex($token, 86400 * 7, \woodlsy\phalcon\library\Helper::jsonEncode($user));
        DI::getDefault()->get('cookies')->set('token', $token, time() + 7 * 86400);
        return $token;
    }

    /**
     * 根据用户名获取会员
     *
     * @author yls
     * @param string $username
     * @return array
     */
    public function getUserByUsername(string $username)
    {
        return (new User())->getOne(['username' => $username]);
    }

    /**
     * 更新浏览记录
     *
     * @author yls
     * @param int $uid
     * @param int $bookId
     * @param int $articleId
     */
    public function updateUserBook(int $uid, int $bookId, int $articleId)
    {
        $userBook = (new UserBook())->getOne(['uid' => $uid, 'book_id' => $bookId]);
        if ($userBook) {
            (new UserBook())->updateData(['article_id' => $articleId], ['id' => $userBook['id']]);
        }
    }

    /**
     * 获取书架书本信息
     *
     * @author yls
     * @param int $uid
     * @param int $bookId
     * @return array
     */
    public function getUserBookByBookId(int $uid, int $bookId)
    {
        return (new UserBook())->getOne(['uid' => $uid, 'book_id' => $bookId]);
    }

    /**
     * 根据ID获取书架小说
     *
     * @author yls
     * @param int $uid
     * @param int $id
     * @return array
     */
    public function getUserBookById(int $uid, int $id)
    {
        return (new UserBook())->getOne(['uid' => $uid, 'id' => $id]);
    }

    /**
     * 书架新增小说
     *
     * @author yls
     * @param int $uid
     * @param int $bookId
     * @return bool|int
     */
    public function addUserBook(int $uid, int $bookId)
    {
        return (new UserBook())->insertData(['uid' => $uid, 'book_id' => $bookId]);
    }

    /**
     * 删除书架小说
     *
     * @author yls
     * @param int $uid
     * @param int $id
     * @return bool|int
     */
    public function delUserBook(int $uid, int $id)
    {
        return (new UserBook())->delData(['uid' => $uid, 'id' => $id]);
    }

    /**
     * 获取书架列表
     *
     * @author yls
     * @param int $uid
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getUserBook(int $uid, int $page, int $size)
    {
        $offset = ($page - 1) * $size;
        $userBooks  = (new UserBook())->getList(['uid' => $uid], 'update_at desc', $offset, $size);
        if (!empty($userBooks)) {
            $bookIds = $articleIds = [];
            foreach ($userBooks as $val) {
                $bookIds[]    = $val['book_id'];
                $articleIds[] = $val['article_id'];
            }
            $books = (new Book())->getAll(['id' => $bookIds], ['id', 'book_name', 'book_img']);
            $books = Helper::setIndexArray($books, 'id');
            $articles = (new Article())->getAll(['id' => $articleIds], ['id', 'title', 'book_id']);
            $articles = Helper::setIndexArray($articles, 'id');

            foreach ($userBooks as &$v) {
                $v['book_name'] = isset($books[$v['book_id']]) ? $books[$v['book_id']]['book_name'] : '';
                $v['book_img'] = isset($books[$v['book_id']]) ? $books[$v['book_id']]['book_img'] : '';
                $v['article_title'] = isset($articles[$v['article_id']]) ? $articles[$v['article_id']]['title'] : '';
            }
        }
        return $userBooks;
    }

    /**
     * 书架小说数量
     *
     * @author yls
     * @param int $uid
     * @return array|int
     */
    public function getUserBookCount(int $uid)
    {
        return (new UserBook())->getCount(['uid' => $uid]);
    }
}