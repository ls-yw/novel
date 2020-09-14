<?php

namespace application\controllers;

use application\basic\BaseController;
use application\library\HelperExtend;
use application\logic\BookLogic;
use Phalcon\Mvc\View;
use woodlsy\phalcon\library\Log;

class IndexController extends BaseController
{
    public function indexAction()
    {
        if (true === $this->isMobile) {
            return $this->mIndex();
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
        $this->view->recommend     = (new BookLogic())->getBookByIsFiled('is_recommend', 4, 'create_at desc', ['id', 'book_name', 'book_img', 'book_author', 'book_intro']);
        $this->view->week          = (new BookLogic())->getBookByOrder('book_weekclick desc', 20);
        $this->view->month         = (new BookLogic())->getBookByOrder('book_monthclick desc', 10);
        $this->view->newest        = (new BookLogic())->getBookByOrder('create_at desc', 11);

        $this->view->title = $this->config['host_seo_name'];

    }

    private function mIndex()
    {

        $latestBook = (new BookLogic())->getList('', 'id desc', ($this->page - 1) * $this->size, $this->size);
        if ('json' === $this->needResponse) {
            $data = [
                'recommend' => (new BookLogic())->getBookByIsFiled('is_recommend', 10, 'create_at desc', ['id', 'book_name', 'book_img', 'book_author', 'book_intro']),
                'book' => $latestBook,
            ];
            return  $this->ajaxReturn(0, 'ok', $data);
        }


        $this->view->pick('m/index/index');

        $this->view->data = $latestBook;
        $this->view->title = $this->config['host_seo_name'];
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

    public function updateAction()
    {
        if ($this->request->isPost()) {
            // {"appid":"__UNI__7890080","version":"1.0.0","platform":"Android"}
            $data = [
                'update'=> false,
                'url' => 'https://w.banzhu9.com/banzhu9.apk',
                'must' => false,
                'note' => '更新日志'
            ];
            return $this->ajaxReturn(0, 'ok', $data);
        }
    }

}