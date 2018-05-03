<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/23
 * Time: 11:24
 */

namespace app\admin\model;

use think\Model;
use think\Db;

class Article extends Model {


    public function getArticleByPage($map, $order = "", $paginate = 10, $paginate_config = []) {
        $fields = "article.*";
        $fields .= ",category.name catename,category.ename ename,category.modelid";
        $data = Db::table($this->table)
            ->alias("article")
            ->field($fields)
            ->join([$this->join_table => "category"], "article.cid = category.id", "LEFT")
            ->where($map)
            ->order($order)
            ->paginate($paginate,false,$paginate_config);
        return $data;
    }


}