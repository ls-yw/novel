<?php

namespace application\controllers;

use application\basic\BaseController;
use application\logic\BookLogic;

class CategoryController extends BaseController
{
    public function indexAction()
    {
        $categoryId = (int) $this->get('id');

        $category = (new BookLogic())->getCategoryById($categoryId);
        if (empty($category)) {
            die('<script>alert("分类不存在");history.go(-1)</script>');
        }
        $this->view->books     = (new BookLogic())->getBookByCategory($categoryId, 'update_at desc', $this->page, $this->size);
        $this->view->pageCount = (new BookLogic())->getBookByCategoryCount($categoryId);
        $this->view->page      = $this->page;
        $this->view->pageTotal = ceil($this->view->pageCount / $this->size);

        $this->view->categoryId   = $categoryId;
        $this->view->title        = $category['seo_name'].'-'.$this->config['host_seo_name'];
        $this->view->catrgoryName = $this->view->category[$categoryId];
        $this->view->month        = (new BookLogic())->getBookByOrder('book_monthclick desc', 10, $categoryId);
        $this->view->newest       = (new BookLogic())->getBookByOrder('create_at desc', 11);

        $this->view->keywords    = $category['keyword'];
        $this->view->description = $category['description'];

    }

    public function searchAction()
    {
        die('<script>alert("暂不能搜索");history.go(-1)</script>');
    }
}