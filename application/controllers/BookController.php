<?php

namespace application\controllers;

use application\basic\BaseController;
use application\library\AliyunOss;
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

        try {

            $id = (int) $this->get('id');

            $book = (new BookLogic())->getById($id);
            if (empty($book)) {
                die('<script>alert("小说不存在");history.go(-1)</script>');
            }

            if (true === $this->isMobile) {
                $this->view->pick('book/info-wap');
            }
            $userBook = [];
            if ($this->user) {
                $userBook = (new MemberLogic())->getUserBookByBookId((int) $this->user['id'], $id);

            }

            $this->view->userBook  = $userBook;
            $this->view->title     = $book['book_name'];
            $this->view->book      = $book;
            $this->view->page      = $this->page;
            $this->view->count     = (new BookLogic())->getArticleListCount($id);
            $this->view->totalPage = ceil($this->view->count / $this->size);
            $this->view->list      = (new BookLogic())->getArticleList($id, $this->page, $this->size);

        } catch (NovelException $e) {
            die('<script>alert("' . $e->getMessage() . '");history.go(-1)</script>');
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            die('<script>alert("系统错误");history.go(-1)</script>');
        }
    }

    public function chapterAction()
    {
        try {
            $bookId = (int) $this->get('book_id');

            $book = (new BookLogic())->getById($bookId);
            if (empty($book)) {
                die('<script>alert("小说不存在");history.go(-1)</script>');
            }

            $chapter = (new BookLogic())->getArticleList($bookId, $this->page, $this->size);
            $count   = (new BookLogic())->getArticleListCount($bookId);

            $this->view->title     = $book['book_name'];
            $this->view->chapter   = $chapter;
            $this->view->book      = $book;
            $this->view->totalPage = ceil($count / $this->size);
        } catch (NovelException $e) {
            die('<script>alert("' . $e->getMessage() . '");history.go(-1)</script>');
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            die('<script>alert("系统错误");history.go(-1)</script>');
        }
    }

    public function articleAction()
    {
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

            $key = "content_{$article['book_id']}_{$article['id']}";
            if (!Redis::getInstance()->exists($key)) {
                $content            = (new AliyunOss())->getString($article['book_id'], $article['id']);
                Redis::getInstance()->setex($key, 3600, $content);
            }
            $content = Redis::getInstance()->get($key);
            $article['content'] = $content;

            $prevId = (int) (new BookLogic())->getArticlePrev($article['book_id'], $article['article_sort']);
            $nextId = (int) (new BookLogic())->getArticleNext($article['book_id'], $article['article_sort']);

            if (true === $this->isMobile) {
                $this->view->pick('book/article-wap');
            }

            if ($this->user) {
                (new MemberLogic())->updateUserBook((int) $this->user['id'], $bookId, $id);
            }

            $this->view->title   = $article['title'];
            $this->view->article = $article;
            $this->view->book    = $book;
            $this->view->prevId  = $prevId;
            $this->view->nextId  = $nextId;
        } catch (NovelException $e) {
            die('<script>alert("' . $e->getMessage() . '");history.go(-1)</script>');
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            die('<script>alert("系统错误");history.go(-1)</script>');
        }
    }
}