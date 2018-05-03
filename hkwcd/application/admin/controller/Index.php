<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/11
 * Time: 16:55
 */

namespace app\admin\controller;

class Index extends Base {

    public function index() {
//        var_dump($this->operator);exit;
        $category_model = $this->lang == "zh-cn" ? "ZhCategory" : "EnCategory";
        $menudoclist = model($category_model)
            ->getMenuDocList(['pid' => 0 , 'type' => 0], 'category.sort');
        $this->assign("menudoclist", $menudoclist);

        return $this->fetch("old_index");
    }

    public function main() {
        $os = PHP_OS;
        $php_version = $_SERVER["SERVER_SOFTWARE"];
        $mysql_version = \think\Db::query("SELECT VERSION();");
        $mysql_version = "MYSQL ".$mysql_version[0]["VERSION()"];
        $this->assign("os", $os);
        $this->assign("php_version", $php_version);
        $this->assign("mysql_version", $mysql_version);
        return $this->fetch("old_main");
    }


}