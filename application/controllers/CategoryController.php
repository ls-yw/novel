<?php

namespace application\controllers;

use application\basic\BaseController;
use application\logic\BookLogic;

class CategoryController extends BaseController
{
    public function indexAction()
    {
        $categoryId = (int) $this->get('id');

        $this->view->books     = (new BookLogic())->getBookByCategory($categoryId, 'update_at desc', $this->page, $this->size);
        $this->view->pageCount = (new BookLogic())->getBookByCategoryCount($categoryId);
        $this->view->page      = $this->page;
        $this->view->pageTotal = ceil($this->view->pageCount / $this->size);

        $this->view->categoryId   = $categoryId;
        $this->view->title        = $this->view->category[$categoryId];
        $this->view->catrgoryName = $this->view->category[$categoryId];
        $this->view->month        = (new BookLogic())->getBookByOrder('book_monthclick desc', 10, $categoryId);
        $this->view->newest       = (new BookLogic())->getBookByOrder('create_at desc', 11);
    }

    public function searchAction()
    {
        die('<script>alert("暂不能搜索");history.go(-1)</script>');
    }
}