<?php

namespace application\controllers;

use application\basic\BaseController;
use application\library\AliyunOss;
use application\library\NovelException;
use application\logic\BookLogic;
use Exception;
use woodlsy\phalcon\library\Log;

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

            $this->view->title     = $book['book_name'];
            $this->view->data      = $book;
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

    public function articleAction()
    {
        try {
            $id = (int) $this->get('id');

            $article = (new BookLogic())->getArticleById($id);
            if (empty($article)) {
                die('<script>alert("小说章节不存在");history.go(-1)</script>');
            }
            $book = (new BookLogic())->getById($article['book_id']);

            $content            = (new AliyunOss())->getString($article['book_id'], $article['id']);
            $article['content'] = $content;

            $prevId = (int)(new BookLogic())->getArticlePrev($article['book_id'], $article['article_sort']);
            $nextId = (int)(new BookLogic())->getArticleNext($article['book_id'], $article['article_sort']);

            if (true === $this->isMobile) {
                $this->view->pick('book/article-wap');
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