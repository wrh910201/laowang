<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/11
 * Time: 17:44
 */

namespace app\admin\controller;

class Block extends Base {

    public $block_model = "";

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->block_model = $this->lang == "zh-cn" ? "ZhBlock" : "EnBlock";
    }


    public function index() {
        //分页
        $list = model($this->block_model)->order("id desc")->paginate(10);
        $page = $list->render();


        $this->assign("vlist", $list);
        $this->assign("page", $page);

        $this->assign("type", "自由块列表");

        return $this->fetch();
    }
    //添加
    public function add() {

        if (request()->isPost()) {
            $this->addHandle();
            exit();
        }
        $this->assign("type", "添加自由块");

        $this->assign("blocktypelist", getArrayOfItem('blocktype'));
        return $this->fetch();
    }

    //
    public function addHandle() {
        //当前控制器名称
        $actionName = strtolower(request()->action());

        $data['name'] = input('name', '', 'htmlspecialchars,trim');
        $data['blocktype'] = input('blocktype', 0, 'intval');
        $data['remark'] = input('remark', '');
        $content = input('content','','');


        if (empty($data['name'])) {
            $this->error('请填写名称');
        }

        if (empty($data['blocktype'])) {
            $this->error('请选择类型');
        }

        if (model($this->block_model)->where(array('name' => $data['name']))->find()) {
            $this->error('自由块名称已经存在!');
        }

        $data['content'] = $content[$data['blocktype']];



        if($id = model($this->block_model)->insertGetId($data)) {

            //更新缓存
            getBlock($data['name'], 1);

            //图片类型
            if ($data['blocktype'] == 2) {

                $pic = preg_replace('/!(\d+)X(\d+)\.jpg$/i', '', $data['content']);//清除缩略图的!200X200.jpg后缀
                $attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
                if($attid){
                    M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => 0, 'desc' => $actionName));
                }

            } elseif ($data['blocktype'] == 3) {
                //内容中的图片
                $img_arr = array();
                $reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";
                preg_match_all($reg, $data['content'], $img_arr, PREG_PATTERN_ORDER);
                // 匹配出来的不重复图片
                $img_arr = array_unique($img_arr[1]);
                if (!empty($img_arr)) {
                    $attid = M('attachment')->where(array('filepath' => array('in', $img_arr)))->getField('id', true);
                    $dataAtt = array();
                    if ($attid) {
                        foreach ($attid as $v) {
                            $dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => 0, 'desc' => $actionName);
                        }
                        M('attachmentindex')->addAll($dataAtt);
                    }

                }
            }


            $this->success('添加成功',U(GROUP_NAME. '/Block/index'));
        }else {
            $this->error('添加失败');
        }
    }

    //编辑
    public function edit() {
        //当前控制器名称
        $id = I('id', 0, 'intval');
        $actionName = strtolower($this->getActionName());

        if (IS_POST) {
            $this->editHandle();
            exit();
        }

        $this->type = '添加自由块';
        $this->blocktypelist = getArrayOfItem('blocktype');

        $vo = M($actionName)->find($id);
        //非富文本,引号的问题
        $vo['content'] = str_replace("&#39;", "'", $vo['content']);	//只针对input,textarea,ueditor切换
        $vo['content'] = htmlspecialchars($vo['content']);
        $this->vo = $vo;
        $this->display();
    }


    //修改处理
    public function editHandle() {
        $actionName = strtolower($this->getActionName());

        $id = $data['id'] = I('id', 0, 'intval');
        $data['name'] = I('name', '', 'htmlspecialchars,trim');
        $data['blocktype'] = I('blocktype', 0, 'intval');
        $data['remark'] = I('remark', '');
        $content = I('content','','');


        if (empty($data['name'])) {
            $this->error('请填写名称');
        }

        if (empty($data['blocktype'])) {
            $this->error('请选择类型');
        }

        $data['content'] = $content[$data['blocktype']];


        if (M('block')->where(array('name' => $data['name'], 'id' => array('neq', $id)))->find()) {
            $this->error('自由块名称已经存在!');
        }


        if (false !== M('block')->save($data)) {

            //更新缓存
            getBlock($data['name'], 1);

            //del
            M('attachmentindex')->where(array('arcid' => $id, 'modelid' => 0, 'desc' => $actionName))->delete();

            //图片类型
            if ($data['blocktype'] == 2) {

                $pic = preg_replace('/!(\d+)X(\d+)\.jpg$/i', '', $data['content']);//清除缩略图的!200X200.jpg后缀
                $attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
                if($attid){
                    M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => 0, 'desc' => $actionName));
                }

            } elseif ($data['blocktype'] == 3) {
                //内容中的图片
                $img_arr = array();
                $reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";
                preg_match_all($reg, $data['content'], $img_arr, PREG_PATTERN_ORDER);
                // 匹配出来的不重复图片
                $img_arr = array_unique($img_arr[1]);
                if (!empty($img_arr)) {
                    $attid = M('attachment')->where(array('filepath' => array('in', $img_arr)))->getField('id', true);
                    $dataAtt = array();
                    if ($attid) {
                        foreach ($attid as $v) {
                            $dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => 0, 'desc' => $actionName);
                        }
                        M('attachmentindex')->addAll($dataAtt);
                    }

                }
            }


            $this->success('修改成功', U(GROUP_NAME. '/Block/index'));
        }else {

            $this->error('修改失败');
        }

    }



    //彻底删除
    public function del() {

        $id = I('id',0 , 'intval');
        $batchFlag = intval($_GET['batchFlag']);
        //批量删除
        if ($batchFlag) {
            $this->delBatch();
            return;
        }
        $name = M('block')->where(array('id' => $id))->getField('name');//清除F缓存用
        if (M('block')->delete($id)) {
            getBlock($name, 1);//清除缓存(更新)
            $this->success('彻底删除成功', U(GROUP_NAME. '/Block/index'));
        }else {
            $this->error('彻底删除失败');
        }
    }


    //批量彻底删除
    public function delBatch() {

        $idArr = I('key',0 , 'intval');
        if (!is_array($idArr)) {
            $this->error('请选择要彻底删除的项');
        }
        $where = array('id' => array('in', $idArr));
        $name = M('block')->where($where)->getField('name', true);//清除F缓存用

        if (M('block')->where($where)->delete()) {
            foreach ($name as $v) {
                getBlock($v, 1);//清除缓存(更新)
            }
            $this->success('彻底删除成功', U(GROUP_NAME. '/Block/index'));
        }else {
            $this->error('彻底删除失败');
        }
    }


}