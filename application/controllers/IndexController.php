<?php

namespace application\controllers;

use application\basic\BaseController;
use application\logic\BookLogic;
use Phalcon\Mvc\View;

class IndexController extends BaseController
{
    public function indexAction()
    {
        if (true === $this->isMobile) {
            $this->mIndex();
            return;
        }
        $category = $this->view->category;

        $categoryBooks = [];
        if (!empty($category)) {
            foreach ($category as $key => $val) {
                $categoryBooks[$key]['img_book']  = (new BookLogic())->getList(['book_category' => $key, 'book_img' => ['!=', '']], 'id desc', 0, 3);
                $categoryBooks[$key]['list_book'] = (new BookLogic())->getList(['book_category' => $key], 'id desc', 3, 36);
            }
        }
        $this->view->categoryBooks = $categoryBooks;
        $this->view->recommend     = (new BookLogic())->getBookByIsFiled('is_recommend', 4, 'create_at desc');
        $this->view->week          = (new BookLogic())->getBookByOrder('book_weekclick desc', 20);
        $this->view->month         = (new BookLogic())->getBookByOrder('book_monthclick desc', 10);
        $this->view->newest        = (new BookLogic())->getBookByOrder('create_at desc', 11);

        $this->view->title = $this->config['host_seo_name'];;
    }

    private function mIndex()
    {
        $this->view->pick('m/index/index');

        $this->view->data = (new BookLogic())->getList('', 'id desc', ($this->page - 1) * $this->size, $this->size);
        $this->view->title = $this->config['host_seo_name'];;
    }

    public function errorAction($message = '404', $url = '/', $waitSecond = 2)
    {

        $this->view->disableLevel(
            View::LEVEL_MAIN_LAYOUT
        );
        if ('-1' === $url) {
            $url = 'javascript:history.go(-1)';
        }
        $this->view->message    = $message;
        $this->view->url        = $url;
        $this->view->waitSecond = $waitSecond;
    }

}