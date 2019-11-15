<?php

namespace application\models;

use woodlsy\phalcon\basic\BasicModel;

class UserBook extends BasicModel
{
    /**
     * 表名
     *
     * @var string
     */
    protected $_targetTable = '{{user_book}}';

    protected $_targetDb = 'novel';

    public function attribute()
    {
        return [
            'id'         => 'ID',
            'uid'        => '用户ID',
            'book_id'    => '小说ID',
            'article_id' => '章节ID',
            'create_at'  => '创建时间',
            'update_at'  => '更新时间',
            'create_by'  => '创建人（记录管理员）',
            'update_by'  => '更新人（记录管理员）',
        ];
    }
}