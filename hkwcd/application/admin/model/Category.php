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

class Category extends Model {

    protected $join_table = "";

    public function getMenuDocList($map, $order) {
        $fields = "category.*,model.description,model.tablename,model.status,";
        $fields .= "model.template_category,model.template_list,model.template_show,model.sort";
        $data = Db::table($this->table)
            ->alias("category")
            ->field($fields)
            ->join(["{$this->join_table}" => "model"], "model.id = category.modelid", "left")
            ->where($map)
            ->order($order)
            ->select();
        return $data;
    }

    public function getCategory($status = 0,$update = 0){//
        $cate_sname = "fCategery_{$this->prefix}" . $status;
        $cate_arr = cache($cate_sname);
        if ($update || !$cate_arr) {
            $fields = "category.*,model.description,model.tablename,model.status,";
            $fields .= "model.template_category,model.template_list,model.template_show,model.sort";
            if ($status == 1) {
                $cate_arr = Db::table($this->table)
                    ->alias("category")
                    ->field($fields)
                    ->join(["{$this->join_table}" => "model"], "model.id = category.modelid", "left")
                    ->where(['category.status' => 1])
                    ->order('category.sort')
                    ->select();
            } else if ($status == 2) {//后台栏目专用
                $cate_arr = Db::table($this->table)
                    ->alias("category")
                    ->field($fields)
                    ->join(["{$this->join_table}" => "model"], "model.id = category.modelid", "left")
                    ->where(['category.type' => 0])
                    ->order('category.sort')
                    ->select();
            } else {
                $cate_arr = Db::table($this->table)
                    ->alias("category")
                    ->field($fields)
                    ->join(["{$this->join_table}" => "model"], "model.id = category.modelid", "left")
                    ->order('category.sort')
//                    ->fetchSql(true)
                    ->select();
            }
            if (!isset($cate_arr)) {
                $cate_arr = [];
            }

            //S(缓存名称,缓存值,缓存有效时间[秒]);
            //S($cate_sname, $cate_arr, 48 * 60 * 60);
            cache($cate_sname, $cate_arr, 60 * 60 * 48);
        }

        return $cate_arr;
    }

    //传递一个父级分类ID返回所有子级分类
    static public function getChildren($cate, $pid) {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['pid'] == $pid) {
                $arr[] = $v;
                $arr = array_merge($arr, self::getChildren($cate, $v['id']));
            }
        }
        return $arr;
    }

    //传递一个子分类ID返回他的所有父级分类
    static public function getParents($cate, $id) {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['id'] == $id) {
                $arr[] = $v;
                $arr = array_merge(self::getParents($cate, $v['pid']), $arr);
            }
        }
        return $arr;
    }

    //传递一个分类ID返回该分类相当信息
    static public function getSelf($cate, $id) {
        $arr = array();
        foreach ($cate as $v) {
            if ($v['id'] == $id) {
                $arr = $v;
                return $arr;
            }
        }
        return $arr;
    }

    //传递一个父级分类ID返回所有子分类ID
    /**
     *@param $cate 全部分类数组
     *@param $pid 父级ID
     *@param $flag 是否包括父级自己的ID，默认不包括
     **/
    static public function getChildrenId($cate, $pid, $flag = 0) {
        $arr = array();
        if ($flag) {
            $arr[] = $pid;
        }
        foreach ($cate as $v) {
            if ($v['pid'] == $pid) {
                $arr[] = $v['id'];
                $arr = array_merge($arr , self::getChildrenId($cate, $v['id']));
            }
        }

        return $arr;
    }

    //一维数组
    static public function unlimitedForLevel($cate, $delimiter = '———', $pid = 0, $level = 0) {

        $arr = array();
        foreach ($cate as $v) {
            if ($v['pid'] == $pid) {
                $v['level'] = $level + 1;
                $v['delimiter'] = str_repeat($delimiter, $level);
                $arr[] = $v;
                $arr = array_merge($arr, self::unlimitedForLevel($cate, $delimiter, $v['id'], $v['level']));
            }
        }

        return $arr;

    }

    //一维数组(同模型)(model = tablename相同)，删除其他模型的分类
    static public function getLevelOfModel($cate, $tablename = 'article') {

        $arr = array();
        foreach ($cate as $v) {
            if ($v['tablename'] == $tablename) {
                $arr[] = $v;
            }
        }

        return $arr;

    }
}