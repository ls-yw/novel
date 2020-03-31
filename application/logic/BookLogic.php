<?php

namespace application\logic;

use application\library\AliyunOss;
use application\library\HelperExtend;
use application\library\NovelException;
use application\models\Article;
use application\models\Book;
use application\models\BookApply;
use application\models\Category;
use application\models\Chapter;
use Phalcon\DI;
use woodlsy\phalcon\library\Helper;
use woodlsy\phalcon\library\Redis;

class BookLogic
{
    public function getList($where, string $orderBy, int $offset, int $size)
    {
        return (new Book())->getList($where, $orderBy, $offset, $size);
    }

    public function getListCount($where = [])
    {
        return (new Book())->getCount($where);
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

    public function lastArticle(int $bookId)
    {
        return (new Article())->getOne(['book_id' => $bookId], '', 'article_sort desc');
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

    /**
     * 获取分类数组
     *
     * @author woodlsy
     * @return array
     */
    public function getCategoryPairs()
    {
        return HelperExtend::arrayToKeyValue((new Category())->getAll(['parent_id' => 0], ['id', 'name']), 'id', 'name');
    }

    /**
     * 获取分类详情
     *
     * @author woodlsy
     * @param int $categoryId
     * @return array|mixed
     */
    public function getCategoryById(int $categoryId)
    {
        return (new Category())->getById($categoryId);
    }

    /**
     * 获取小说通过是否字段
     *
     * @author woodlsy
     * @param string $field
     * @param int    $size
     * @param string $orderBy
     * @return array|bool
     */
    public function getBookByIsFiled(string $field, int $size, string $orderBy = '')
    {
        return (new Book())->getList([$field => 1], $orderBy, 0, $size);
    }

    /**
     * 获取小说通过字段排序
     *
     * @author woodlsy
     * @param string $orderBy
     * @param int    $size
     * @param int    $categoryId
     * @return array|bool
     */
    public function getBookByOrder(string $orderBy, int $size, int $categoryId = null)
    {
        $where = [];
        if (!empty($categoryId)) {
            $where['book_category'] = $categoryId;
        }
        return (new Book())->getList($where, $orderBy, 0, $size);
    }

    /**
     * 获取分类下的文章列表
     *
     * @author woodlsy
     * @param int    $categoryId
     * @param string $orderBy
     * @param int    $page
     * @param int    $size
     * @return array|bool
     */
    public function getBookByCategory(int $categoryId, string $orderBy, int $page, int $size)
    {
        $offset = ($page - 1) * $size;
        $books  = (new Book())->getList(['book_category' => $categoryId], $orderBy, $offset, $size);
        if (!empty($books)) {
            foreach ($books as &$val) {
                $article        = (new Article())->getOne(['book_id' => $val['id']], ['id', 'title', 'create_at'], 'article_sort desc');
                $val['article'] = $article;
            }
        }
        return $books;
    }

    public function getBookByCategoryCount(int $categoryId)
    {
        return (new Book())->getCount(['book_category' => $categoryId]);
    }

    /**
     * 从aliyun oss获取小说内容
     *
     * @author woodlsy
     * @param int $bookId
     * @param int $articleId
     * @return bool|string
     * @throws \application\library\NovelException
     */
    public function getArticleContent(int $bookId, int $articleId)
    {
        if (empty($articleId)) {
            return '';
        }
        $key = "content_{$bookId}_{$articleId}";
        if (!Redis::getInstance()->exists($key)) {
            try {
                $content = (new AliyunOss())->getString($bookId, $articleId);
                Redis::getInstance()->setex($key, 3600, $content);
            } catch (NovelException $e) {
                Redis::getInstance()->setex($key, 3600, '');
            }
        }
        $content = Redis::getInstance()->get($key);
        return $content;
    }

    /**
     * 获取章节和文章
     *
     * @author woodlsy
     * @param int $bookId
     * @return array|bool
     */
    public function getChapterArticle(int $bookId)
    {
        $a = (new Chapter());
        $chapter = $a->getAll(['book_id' => $bookId], null, 'chapter_order asc');
        if (!empty($chapter)){
            foreach ($chapter as &$val) {
                if($val['chapter_name'] == '默认章节'){
                    $val['chapter_name'] = '正文';
                }
                $val['article'] = (new Article())->getAll(['book_id' => $bookId, 'chapter_id' => $val['id']], null, 'article_sort asc');
            }
        }
        return $chapter;
    }

    /**
     * 保存点击次数
     *
     * @author woodlsy
     * @param int $bookId
     */
    public function saveClick(int $bookId)
    {
        $book = (new Book())->getById($bookId);
        $bookIds = DI::getDefault()->get('cookies')->get('bookClick')->getValue();
        $bookIds = explode('|', $bookIds);
        if ($book && !in_array($bookId, $bookIds)) {
            // 总点击
            (new Book())->updateData(['book_click' => ['+', 1]], ['id' => $bookId]);

            // 月点击
            if (Helper::now('m', strtotime($book['book_lasttime'])) !== Helper::now('m')) {
                (new Book())->updateData(['book_monthclick' => 0], ['id' => $bookId]);
            }
            (new Book())->updateData(['book_monthclick' => ['+', 1]], ['id' => $bookId]);

            // 周点击
            if (Helper::now('W', strtotime($book['book_lasttime'])) !== Helper::now('W')) {
                (new Book())->updateData(['book_weekclick' => 0], ['id' => $bookId]);
            }
            (new Book())->updateData(['book_weekclick' => ['+', 1]], ['id' => $bookId]);

            // 日点击
            if (Helper::now('z', strtotime($book['book_lasttime'])) !== Helper::now('z')) {
                (new Book())->updateData(['book_dayclick' => 0], ['id' => $bookId]);
            }
            (new Book())->updateData(['book_dayclick' => ['+', 1]], ['id' => $bookId]);

            (new Book())->updateData(['book_lasttime' => Helper::now()], ['id' => $bookId]);
            $bookIds[] = $bookId;
            DI::getDefault()->get('cookies')->set('bookClick', implode('|', $bookIds), time() + 3600);
        }
    }

    /**
     * 申请收录
     *
     * @author woodlsy
     * @param int    $uid
     * @param string $bookName
     * @param string $author
     * @return bool|int
     * @throws NovelException
     */
    public function apply(int $uid, string $bookName, string $author)
    {
        $bookCount = (new Book())->getCount(['book_name' => $bookName, 'book_author' => $author]);
        if (!empty($bookCount)) {
            throw new NovelException('该小说已收录');
        }

        $applyCount = (new BookApply())->getCount(['uid' => $uid, 'name' => $bookName, 'author' => $author]);
        if (!empty($applyCount)) {
            throw new NovelException('您已提交该小说的申请，请勿重复提交');
        }

        $data = [
            'uid' => $uid,
            'name' => $bookName,
            'author' => $author
        ];
        return (new BookApply())->insertData($data);
    }
}