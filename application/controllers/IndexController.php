<?php

namespace application\controllers;

use application\basic\BaseController;
use application\logic\BookLogic;

class IndexController extends BaseController
{
    public function indexAction()
    {
        if (true === $this->isMobile) {
            $this->view->pick('index/index-wap');

            $this->view->data = (new BookLogic())->getList('', 'id desc', $this->page, $this->size);
        } else {
            $category = $this->view->category;

            $categoryBooks = [];
            if (!empty($category)) {
                foreach ($category as $key => $val) {
                    $categoryBooks[$key]['img_book']  = (new BookLogic())->getList(['book_category' => $key, 'book_img' => ['!=', '']], 'id desc', 0, 3);
                    $categoryBooks[$key]['list_book'] = (new BookLogic())->getList(['book_category' => $key], 'id desc', 3, 36);
                }
            }
            $this->view->categoryBooks = $categoryBooks;
            $this->view->recommend     = (new BookLogic())->getBookByIsFiled('is_recommend', 4);
            $this->view->week          = (new BookLogic())->getBookByOrder('book_weekclick desc', 20);
            $this->view->month         = (new BookLogic())->getBookByOrder('book_monthclick desc', 10);
            $this->view->newest        = (new BookLogic())->getBookByOrder('create_at desc', 11);
        }

        $this->view->title = '小说列表';
    }

}