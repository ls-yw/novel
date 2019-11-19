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
        }

        $this->view->title = 'aa';
        $this->view->data  = (new BookLogic())->getList($this->page, $this->size);
    }

}