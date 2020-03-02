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
                $this->view->likeBooks = (new BookLogic())->getList(['book_img' => ['!=', ''], 'book_intro' => ['!=', ''], 'id' => ['!=', $id], 'book_category' => $book['book_category']], '', 0, 4);
                $article               = (new BookLogic())->lastArticle($id);

                $article['content'] = (new BookLogic())->getArticleContent($id, (int) $article['id']);

                $this->view->article = $article;
            }

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
            $this->view->title      = $book['book_name'].'-'.$this->config['host_seo_name'];
            $this->view->book       = $book;
            $this->view->categoryId = $book['book_category'];
            $this->view->page       = $this->page;

            $this->view->keywords    = (!empty($book['book_keyword']) ? str_replace(' ', ',', $book['book_keyword']).',' : '') .
                $this->config['host_seo_name'];
            $this->view->description = mb_substr($book['book_intro'], 0, 150) . '...';

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

            $this->view->title      = $book['book_name'].'-'.$this->config['host_seo_name'];
            $this->view->chapter    = $chapter;
            $this->view->book       = $book;
            $this->view->categoryId = $book['book_category'];

            $this->view->keywords    = (!empty($book['book_keyword']) ? str_replace(' ', ',', $book['book_keyword']).',' : '') .
                $this->config['host_seo_name'];
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

            $content            = (new BookLogic())->getArticleContent((int) $article['book_id'], (int) $article['id']);
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

            $this->view->title      = $article['title'].'-'.$book['book_name'].'-'.$this->config['host_seo_name'];
            $this->view->article    = $article;
            $this->view->book       = $book;
            $this->view->prevId     = $prevId;
            $this->view->nextId     = $nextId;
            $this->view->categoryId = $book['book_category'];

            $this->view->keywords    = (!empty($book['book_keyword']) ? str_replace(' ', ',', $book['book_keyword']).',' : '') .
                $this->config['host_seo_name'];
            $this->view->description = mb_substr($content, 0, 150) . '...';
        } catch (NovelException $e) {
            die('<script>alert("' . $e->getMessage() . '");history.go(-1)</script>');
        } catch (Exception $e) {
            Log::write($this->controllerName . '|' . $this->actionName, $e->getMessage() . $e->getFile() . $e->getLine(), 'error');
            die('<script>alert("系统错误");history.go(-1)</script>');
        }
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