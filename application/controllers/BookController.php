<?php

namespace application\controllers;

use application\basic\BaseController;
use application\library\AliyunOss;
use application\library\HelperExtend;
use application\library\NovelException;
use application\logic\BookLogic;
use application\logic\MemberLogic;
use Exception;
use woodlsy\phalcon\library\Log;
use woodlsy\phalcon\library\Redis;

class BookController extends BaseController
{
    public function indexAction()
    {
        if (true === $this->isMobile) {
            $this->mIndex();
            return;
        }

        try {

            $id = (int) $this->get('id');

            $book = (new BookLogic())->getById($id);
            if (empty($book)) {
                die('<script>alert("小说不存在");history.go(-1)</script>');
            }

            $this->view->likeBooks = (new BookLogic())->getList(['book_img' => ['!=', ''], 'book_intro' => ['!=', ''], 'id' => ['!=', $id], 'book_category' => $book['book_category']], '', 0, 4);
            $article               = (new BookLogic())->lastArticle($id);

            if (1 === (int) $article['is_oss']) {
                $article['content'] = (new BookLogic())->getArticleContent($id, (int) $article['id']);
            } else {
                $article['content'] = '章节内容待传';
            }

            $this->view->article = $article;

            $userBook = [];
            if ($this->user) {
                $userBook = (new MemberLogic())->getUserBookByBookId((int) $this->user['id'], $id);
                if (!empty($userBook)) {
                    $seeArticle                = (new BookLogic())->getArticleById($userBook['article_id']);
                    $userBook['article_title'] = $seeArticle['title'] ?? '';
                }
            }

            (new BookLogic())->saveClick($id);

            $this->view->userBook   = $userBook;
            $this->view->title      = $book['book_name'] . '最新章节无弹窗无广告-' . $this->config['host_name'];
            $this->view->book       = $book;
            $this->view->categoryId = $book['book_category'];
            $this->view->page       = $this->page;

            $this->view->keywords    = $book['book_name'] . ',' . $book['book_name'] . '最新章节,' . $book['book_name'] . '全文阅读,' . $book['book_name'] . '无弹窗无广告';
            $this->view->description = "{$book['book_author']}的{$book['book_name']}情节跌宕起伏、扣人心弦，是一本情节与文笔俱佳小说,斑竹9小说网提供{$book['book_name']}最新章节列表目录在线阅读。{$book['book_name']}最新章节内容{$book['book_author']}大大原创,网友收集并提供，转载至斑竹9小说网只是为了宣传小说让更多书友阅读。";

        } catch (NovelException $e) {
            die('<script>alert("' . $e->getMessage() . '");history.go(-1)</script>');
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            die('<script>alert("系统错误");history.go(-1)</script>');
        }
    }

    private function mIndex()
    {
        $id = (int) $this->get('id');

        $book = (new BookLogic())->getById($id);

        $userBook = [];
        if ($this->user) {
            $userBook = (new MemberLogic())->getUserBookByBookId((int) $this->user['id'], $id);
            if (!empty($userBook)) {
                $seeArticle                = (new BookLogic())->getArticleById($userBook['article_id']);
                $userBook['article_title'] = $seeArticle['title'] ?? '';
            }
        }

        $this->view->book       = $book;
        $this->view->count      = (new BookLogic())->getArticleListCount($id);
        $this->view->totalPage  = ceil($this->view->count / $this->size);
        $this->view->list       = (new BookLogic())->getArticleList($id, $this->page, $this->size);
        $this->view->userBook   = $userBook;
        $this->view->title      = $book['book_name'] . '最新章节无弹窗无广告-' . $this->config['host_name'];
        $this->view->book       = $book;
        $this->view->categoryId = $book['book_category'];
        $this->view->page       = $this->page;

        $this->view->keywords    = $book['book_name'] . ',' . $book['book_name'] . '最新章节,' . $book['book_name'] . '全文阅读,' . $book['book_name'] . '无弹窗无广告';
        $this->view->description = "{$book['book_author']}的{$book['book_name']}情节跌宕起伏、扣人心弦，是一本情节与文笔俱佳小说,斑竹9小说网提供{$book['book_name']}最新章节列表目录在线阅读。{$book['book_name']}最新章节内容{$book['book_author']}大大原创,网友收集并提供，转载至斑竹9小说网只是为了宣传小说让更多书友阅读。";


        $this->view->pick('m/book/index');

        $this->view->mMenu = 'none';
    }

    public function chapterAction()
    {
        try {
            $bookId = (int) $this->get('book_id');

            $book = (new BookLogic())->getById($bookId);
            if (empty($book)) {
                die('<script>alert("小说不存在");history.go(-1)</script>');
            }

            $chapter = (new BookLogic())->getChapterArticle($bookId);

            $this->view->title      = $book['book_name'] . '最新章节无弹窗无广告-' . $this->config['host_name'];
            $this->view->chapter    = $chapter;
            $this->view->book       = $book;
            $this->view->categoryId = $book['book_category'];

            $this->view->keywords    = $book['book_name'] . ',' . $book['book_name'] . '最新章节,' . $book['book_name'] . '全文阅读,' . $book['book_name'] . '无弹窗无广告';
            $this->view->description = mb_substr($book['book_intro'], 0, 150) . '...';

        } catch (NovelException $e) {
            die('<script>alert("' . $e->getMessage() . '");history.go(-1)</script>');
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            die('<script>alert("系统错误");history.go(-1)</script>');
        }
    }

    public function articleAction()
    {
        if (true === $this->isMobile) {
            $this->mArticle();
            return;
        }
        try {
            $id     = (int) $this->get('id');
            $bookId = (int) $this->get('book_id');

            if (!empty($bookId) && empty($id)) {
                $id = (new BookLogic())->firstArticle($bookId)['id'] ?? 0;
            }

            $article = (new BookLogic())->getArticleById($id);
            if (empty($article)) {
                die('<script>alert("小说章节不存在");history.go(-1)</script>');
            }
            $book = (new BookLogic())->getById($article['book_id']);

            if (1 === (int) $article['is_oss']) {
                $content = (new BookLogic())->getArticleContent((int) $article['book_id'], (int) $article['id']);
            } else {
                $content = '章节内容待传';
            }

            $article['content'] = $content;

            $prevId = (int) (new BookLogic())->getArticlePrev($article['book_id'], $article['article_sort']);
            $nextId = (int) (new BookLogic())->getArticleNext($article['book_id'], $article['article_sort']);

            if (true === $this->isMobile) {
                $this->view->pick('book/article-wap');
            }
//            if ($this->user) {
//                (new MemberLogic())->updateUserBook((int) $this->user['id'], $article['book_id'], $id);
//            }

            (new BookLogic())->saveClick($bookId);

            $this->view->title      = $article['title'] . '-' . $book['book_name'] . '-' . $this->config['host_name'];
            $this->view->article    = $article;
            $this->view->book       = $book;
            $this->view->prevId     = $prevId;
            $this->view->nextId     = $nextId;
            $this->view->categoryId = $book['book_category'];

            $this->view->keywords    = "{$book['book_name']},{$article['title']},{$book['book_author']},{$this->config['host_name']}";
            $this->view->description = "{$this->config['host_name']}提供{$book['book_author']}的{$book['book_name']}TXT小说免费下载,{$article['title']}最新章节免费阅读,热门小说{$book['book_name']}最新章节免费阅读，{$this->config['host_name']}是您值得收藏的免费小说阅读网无弹窗无广告";
        } catch (NovelException $e) {
            die('<script>alert("' . $e->getMessage() . '");history.go(-1)</script>');
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            die('<script>alert("系统错误");history.go(-1)</script>');
        }
    }

    /**
     * 文章内容 wap
     *
     * @author woodlsy
     */
    public function mArticle()
    {
        $this->view->pick('m/book/article');

        $this->view->mMenu = 'none';

        try {
            $id     = (int) $this->get('id');
            $bookId = (int) $this->get('book_id');

            if (!empty($bookId) && empty($id)) {
                $id = (new BookLogic())->firstArticle($bookId)['id'] ?? 0;
            }

            $article = (new BookLogic())->getArticleById($id);
            if (empty($article)) {
                throw new NovelException('小说章节不存在');
            }

            $book = (new BookLogic())->getById($article['book_id']);

            if (1 === (int) $article['is_oss']) {
                $content = (new BookLogic())->getArticleContent((int) $article['book_id'], (int) $article['id']);
            } else {
                $content = '章节内容待传';
            }
            $article['content'] = $content;

            $prevId = (int) (new BookLogic())->getArticlePrev($article['book_id'], $article['article_sort']);
            $nextId = (int) (new BookLogic())->getArticleNext($article['book_id'], $article['article_sort']);

            $this->view->title      = $article['title'] . '-' . $book['book_name'] . '-' . $this->config['host_name'];
            $this->view->article    = $article;
            $this->view->book       = $book;
            $this->view->prevId     = $prevId;
            $this->view->nextId     = $nextId;
            $this->view->categoryId = $book['book_category'];

            $this->view->keywords    = "{$book['book_name']},{$article['title']},{$book['book_author']},{$this->config['host_name']}";
            $this->view->description = "{$this->config['host_name']}提供{$book['book_author']}的{$book['book_name']}TXT小说免费下载,{$article['title']}最新章节免费阅读,热门小说{$book['book_name']}最新章节免费阅读，{$this->config['host_name']}是您值得收藏的免费小说阅读网无弹窗无广告";

        } catch (NovelException $e) {
            $this->setAlertMsg('error', $e->getMessage());
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            $this->setAlertMsg('系统错误', $e->getMessage());
        }
        $this->loadAlert();
    }

    public function userBookAction()
    {
        $id     = (int) $this->post('id');
        $bookId = (int) $this->post('book_id');

        if (empty($id) || empty($bookId)) {
            return $this->ajaxReturn(1, 'fail');
        }

        if (empty($this->user)) {
            return $this->ajaxReturn(1, 'fail');
        }

        (new MemberLogic())->updateUserBook((int) $this->user['id'], $bookId, $id);

        return $this->ajaxReturn(0, 'ok');
    }
}