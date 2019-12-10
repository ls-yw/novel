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

                $this->view->count     = (new BookLogic())->getArticleListCount($id);
                $this->view->totalPage = ceil($this->view->count / $this->size);
                $this->view->list      = (new BookLogic())->getArticleList($id, $this->page, $this->size);
            } else {
                $this->view->likeBooks  = (new BookLogic())->getList(['book_img' => ['!=', ''], 'book_intro' => ['!=', ''], 'id' => ['!=', $id], 'book_category' => $book['book_category']], '', 1, 4);
                $this->view->article = (new BookLogic())->lastArticle($id);

                $content = (new BookLogic())->getArticleContent($id, (int)$this->view->article['id']);
                $this->view->article['content'] = $content;
            }

            $userBook = [];
            if ($this->user) {
                $userBook = (new MemberLogic())->getUserBookByBookId((int) $this->user['id'], $id);
            }

            $this->view->userBook  = $userBook;
            $this->view->title     = $book['book_name'];
            $this->view->book      = $book;
            $this->view->categoryId      = $book['book_category'];
            $this->view->page      = $this->page;

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

            $chapter = (new BookLogic())->getChapterArticle($bookId);

            $this->view->title     = $book['book_name'];
            $this->view->chapter   = $chapter;
            $this->view->book      = $book;
            $this->view->categoryId      = $book['book_category'];
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

            $content = (new BookLogic())->getArticleContent((int)$article['book_id'], (int)$article['id']);
            $article['content'] = $content;

            $prevId = (int) (new BookLogic())->getArticlePrev($article['book_id'], $article['article_sort']);
            $nextId = (int) (new BookLogic())->getArticleNext($article['book_id'], $article['article_sort']);

            if (true === $this->isMobile) {
                $this->view->pick('book/article-wap');
            }
            if ($this->user) {
                (new MemberLogic())->updateUserBook((int) $this->user['id'], $article['book_id'], $id);
            }

            $this->view->title   = $article['title'];
            $this->view->article = $article;
            $this->view->book    = $book;
            $this->view->prevId  = $prevId;
            $this->view->nextId  = $nextId;
            $this->view->categoryId      = $book['book_category'];
        } catch (NovelException $e) {
            die('<script>alert("' . $e->getMessage() . '");history.go(-1)</script>');
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            die('<script>alert("系统错误");history.go(-1)</script>');
        }
    }
}