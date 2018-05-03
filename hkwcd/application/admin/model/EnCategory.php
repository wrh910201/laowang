<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/11
 * Time: 23:53
 */

namespace app\admin\model;

use think\Db;
use think\Model;

class EnCategory extends Category {

    protected $prefix = "en_";
    protected $table = "en_category";
    protected $join_table = "en_model";

}