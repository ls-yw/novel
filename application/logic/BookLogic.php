<?php
namespace application\logic;

use application\models\Article;
use application\models\Book;

class BookLogic
{
    public function getList(int $page, int $size)
    {
        $offset = ($page - 1) * $size;
        return (new Book())->getList([], 'id desc', $offset, $size);
    }

    public function getListCount()
    {
        return (new Book())->getCount([]);
    }

    public function getById(int $id)
    {
        return (new Book())->getById($id);
    }

    /**
     * 获取小说第一张
     *
     * @author yls
     * @param int $bookId
     * @return array
     */
    public function firstArticle(int $bookId)
    {
        return (new Article())->getOne(['book_id' => $bookId], '', 'article_sort asc');
    }

    public function getArticleList(int $bookId, int $page, int $size)
    {
        $offset = ($page - 1) * $size;
        return (new Article())->getList(['book_id' => $bookId], 'article_sort asc', $offset, $size);
    }

    public function getArticleListCount(int $bookId)
    {
        return (new Article())->getCount(['book_id' => $bookId]);
    }

    public function getArticleById(int $id)
    {
        return (new Article())->getById($id);
    }

    /**
     * 获取上一张文章ID
     *
     * @author yls
     * @param int $bookId
     * @param int $articleSort
     * @return int
     */
    public function getArticlePrev(int $bookId, int $articleSort)
    {
        $article = (new Article())->getOne(['book_id' => $bookId, 'article_sort' => ['<', $articleSort]], 'id', 'article_sort desc');
        return $article ? $article['id'] : 0;
    }

    /**
     * 获取下一章文章ID
     *
     * @author yls
     * @param int $bookId
     * @param int $articleSort
     * @return int
     */
    public function getArticleNext(int $bookId, int $articleSort)
    {
        $article = (new Article())->getOne(['book_id' => $bookId, 'article_sort' => ['>', $articleSort]], 'id', 'article_sort asc');
        return $article ? $article['id'] : 0;
    }
}