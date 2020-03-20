<?php

namespace application\controllers;

use application\basic\BaseController;
use application\logic\BookLogic;

class CategoryController extends BaseController
{
    public function indexAction()
    {
        if (true === $this->isMobile) {
            $this->mIndex();
            return;
        }

        $this->size = 27;

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
        $this->view->title        = $category['seo_name'] . '-' . $this->config['host_seo_name'];
        $this->view->catrgoryName = $this->view->category[$categoryId];
        $this->view->month        = (new BookLogic())->getBookByOrder('book_monthclick desc', 10, $categoryId);
        $this->view->newest       = (new BookLogic())->getBookByOrder('create_at desc', 11);

        $this->view->keywords    = $category['keyword'];
        $this->view->description = $category['description'];

    }

    public function searchAction()
    {
        $keywords   = $this->get('keywords', 'string', '');
        $searchType = $this->get('searchtype', 'string', 'name');
        if ('author' === $searchType) {
            $type = 'book_author';
        } else {
            $type = 'book_name';
        }

        if (empty($keywords)) {
            die('<script>alert("搜索内容不能为空");history.go(-1)</script>');
        }

        $where = [$type => ['like', '%' . $keywords . '%']];
        $books = (new BookLogic())->getList($where, 'update_at desc', ($this->page - 1) * $this->size, $this->size);

        if (!empty($books)) {
            foreach ($books as &$val) {
                $article        = (new BookLogic())->lastArticle($val['id']);
                $val['article'] = $article;
            }
        }

        $this->view->title     = "搜索“{$keywords}”的结果-" . $this->config['host_name'];
        $this->view->books     = $books;
        $this->view->pageCount = (new BookLogic())->getListCount($where);
        $this->view->page      = $this->page;
        $this->view->pageTotal = ceil($this->view->pageCount / $this->size);

        $this->view->month      = (new BookLogic())->getBookByOrder('book_monthclick desc', 10);
        $this->view->newest     = (new BookLogic())->getBookByOrder('create_at desc', 11);
        $this->view->keywords   = $keywords;
        $this->view->searchtype = $searchType;
    }

    /**
     * 所有分类 wap
     *
     * @author woodlsy
     */
    public function mAllAction()
    {
        $this->view->pick('m/category/all');

        $this->view->mMenu = 'category';
    }

    /**
     * 分类首页 wap
     *
     * @author woodlsy
     */
    private function mIndex()
    {
        $this->view->pick('m/category/index');

        $categoryId = (int) $this->get('id');

        $category = (new BookLogic())->getCategoryById($categoryId);
        if (empty($category)) {
            $this->setAlertMsg('error', '分类不存在');
        }

        $this->view->data        = (new BookLogic())->getBookByCategory($categoryId, 'update_at desc', $this->page, $this->size);
        $this->view->pageCount    = (new BookLogic())->getBookByCategoryCount($categoryId);
        $this->view->page         = $this->page;
        $this->view->pageTotal    = ceil($this->view->pageCount / $this->size);
        $this->view->categoryId   = $categoryId;
        $this->view->title        = $category['seo_name'] . '-' . $this->config['host_seo_name'];
        $this->view->catrgoryName = $this->view->category[$categoryId];
        $this->view->keywords     = $category['keyword'];
        $this->view->description  = $category['description'];

        $this->view->mMenu = 'category';
    }
}