<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/11
 * Time: 23:35
 */

namespace app\admin\model;

use think\Db;
use think\Model;

class ZhCategory extends Category {

    protected $prefix = "hx_";
    protected $table = "hx_category";
    protected $join_table = "hx_model";

}