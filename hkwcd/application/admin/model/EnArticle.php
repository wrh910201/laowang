<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/23
 * Time: 11:25
 */

namespace app\admin\model;

class EnArticle extends Article {

    protected $prefix = "en_";
    protected $table = "en_article";
    protected $join_table = "en_category";
}