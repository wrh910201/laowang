<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/23
 * Time: 11:24
 */

namespace app\admin\model;

class ZhArticle extends Article {

    protected $prefix = "hx_";
    protected $table = "hx_article";
    protected $join_table = "hx_category";
}