<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/11
 * Time: 17:45
 */

namespace app\admin\controller;

use app\admin\model\Category;

class Article extends Base {

    public $category_model = "";
    public $attachment_index_model = "";
    public $attachment_model = "";
    public $article_model = "";

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        $this->category_model = $this->lang == "zh-cn" ? "ZhCategory" : "EnCategory";
        $this->attachment_index_model = $this->lang == "zh-cn" ? "ZhAttachmentIndex" : "EnAttachmentIndex";
        $this->attachment_model = $this->lang == "zh-cn" ? "ZhAttachment" : "EnAttachment";
        $this->article_model = $this->lang == "zh-cn" ? "ZhArticle" : "EnArticle";
    }

    public function index() {

        $pid = input('pid', 0, 'intval');//类别ID
        $keyword = input('keyword', '', 'htmlspecialchars,trim');//关键字


        //所有子栏目列表
        //$cate = D('CategoryView')->order('category.sort')->select();
        $cate = model($this->category_model)->getCategory();//全部分类

        $poscate = Category::getParents($cate, $pid);
        $subcate = Category::getChildren($cate, $pid);


        $this->assign("subcate", $subcate);
        $this->assign("poscate", $poscate);


        if ($pid) {
            $idarr = Category::getChildrenId($cate, $pid, 1);//所有子类ID
            $where = array('article.status' => 0, 'cid' => array('in', $idarr));
        }else {
            $where = array('article.status' => 0);
        }

        if (!empty($keyword)) {
            $where['article.title'] = array('LIKE', "%{$keyword}%");
        }

        $paginate_config = [
            'query'=>['pid'=> $pid],
        ];
        $list = model($this->article_model)
            ->getArticleByPage($where, "article.id DESC", 10, $paginate_config);
        $page = $list->render();

        $this->assign("pid", $pid);
        $this->assign("keyword", $keyword);
        $this->assign("vlist", $list);
        $this->assign("page", $page);

        $this->assign("type", "文章列表");

        return $this->fetch();
    }
    //添加文章
    public function add() {

        //当前控制器名称
        $actionName = strtolower(request()->controller());
        $pid = input('pid', 0, 'intval');

        if (request()->isPost()) {
            $this->addPost();
            exit();
        }


        //$cate = D('CategoryView')->where(array('type' => 0))->order('category.sort')->select();
        $cate = model($this->category_model)->getCategory(2);
        $cate = Category::unlimitedForLevel($cate);
        $flagtypelist = getArrayOfItem('flagtype');//文档属性

        $cate = Category::getLevelOfModel($cate, $actionName);

        $this->assign("pid", $pid);
        $this->assign("cate", $cate);
        $this->assign("flagtypelist", $flagtypelist);

        return $this->fetch();
    }

    //
    public function addPost() {

        $pid = input('pid', 0, 'intval');
        $cid = input('cid', 0, 'intval');
        $title = input('title', '', 'htmlspecialchars,rtrim');
        $flags = input('flags', array(),'intval');
        $jumpurl = input('jumpurl', '');
        $description = input('description', '', 'htmlspecialchars');
        $content = input('content', '', '');


        $pic = input('litpic', '', 'htmlspecialchars,trim');

        if (empty($title)) {
            $this->error('标题不能为空');
        }
        if (!$cid) {
            $this->error('请选择栏目');
        }
        $pid = $cid;//转到自己的栏目
        if (empty($description)) {
            $description = str2sub(strip_tags($content), 120);
        }

        //图片标志
        if (!empty($pic) && !in_array(B_PIC, $flags)) {
            $flags[] = B_PIC;
        }
        $flag = 0;
        foreach ($flags as $v) {
            $flag += $v;
        }

        //获取属于分类信息,得到modelid
        import('Class.Category', APP_PATH);
        $selfCate = Category::getSelf(getCategory(0), $cid);//当前栏目信息
        $modelid = $selfCate['modelid'];

        $data =array(
            'title' => $title ,
            'shorttitle' => input('shorttitle', '', 'htmlspecialchars,trim'),
            'color' => input('color'),
            'cid'	=> $cid,
            'litpic'	=> $pic,
            'keywords' => input('keywords','','htmlspecialchars,trim'),
            'description' => $description,
            'content' => $content,
            'publishtime' =>strtotime(input('publishtime', '', 'trim')),
            'updatetime' => time(),
            'click' => rand(10,95),
            'status' => 0,
            'commentflag' => input('commentflag', 0,'intval'),
            'flag'	=> $flag,
            'jumpurl' => $jumpurl,
            'aid'	=> $_SESSION[C('USER_AUTH_KEY')]

        );


        if($id = M('article')->add($data)) {

            //更新上传附件表
            if (!empty($pic)) {
                //更新3个小时内的.即10800秒
                $pic = preg_replace('/!(\d+)X(\d+)\.jpg$/i', '', $pic);//清除缩略图的!200X200.jpg后缀
                $attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
                if($attid){
                    M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => $modelid));
                }
                //halt(M('attachment')->getlastsql());
            }


            //内容中的图片
            $img_arr = array();
            $reg = "/<img[^>]*src=\"((.+)\/(.+)\.(jpg|gif|bmp|png))\"/isU";
            preg_match_all($reg, $content, $img_arr, PREG_PATTERN_ORDER);
            // 匹配出来的不重复图片
            $img_arr = array_unique($img_arr[1]);
            if (!empty($img_arr)) {
                $attid = M('attachment')->where(array('filepath' => array('in', $img_arr)))->getField('id', true);
                $dataAtt = array();
                if ($attid) {
                    foreach ($attid as $v) {
                        $dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => $modelid);
                    }
                    M('attachmentindex')->addAll($dataAtt);
                }

            }
            //$this->display('/Test:empty');exit();


            //更新静态缓存
            delCacheHtml('List/index_'.$cid, false, 'list:index');
            delCacheHtml('Index_index', false, 'index:index');

            $this->success('添加文章成功',U(GROUP_NAME. '/Article/index', array('pid' => $pid)));
        }else {
            $this->error('添加文章失败');
        }
    }

    //编辑文章
    public function edit() {
        //当前控制器名称
        $id = input('id', 0, 'intval');
        $actionName = strtolower(request()->controller());
        $pid = input('pid', 0, 'intval');

        if (request()->isPost()) {
            $this->editPost();
            exit();
        }

        //$cate = D('CategoryView')->where(array('type' => 0))->order('category.sort')->select();
        $cate = model($this->category_model)->getCategory(2);
        $cate = Category::unlimitedForLevel($cate);
        $cate = Category::getLevelOfModel($cate, $actionName);
        $flagtypelist = getArrayOfItem('flagtype');//文档属性

        $this->assign("pid", $pid);
        $this->assign("cate", $cate);

        $vo = model($this->article_model)->where(["id" => $id])->find();
        $vo = $vo ? $vo->toArray() : $vo;
        $vo['content'] = htmlspecialchars($vo['content']);//ueditor
        $this->assign("vo", $vo);
        $this->assign("flagtypelist", $flagtypelist);
        return $this->fetch();
    }


    //修改文章处理
    public function editPost() {

        $data =array(
            'id' => input('id', 0, 'intval'),
            'title' => input('title', '', 'htmlspecialchars,rtrim'),
            'shorttitle' => input('shorttitle', '', 'htmlspecialchars,rtrim'),
            'color' => input('color'),
            'cid'	=> input('cid', 0, 'intval'),
            'litpic'	=> input('litpic', ''),
            'keywords' => input('keywords', '', 'htmlspecialchars,trim'),
            'description' =>  input('description', ''),
            'publishtime' =>strtotime(input('publishtime', '', 'trim')),
            'content' => input('content', '', ''),
            'updatetime' => time(),
            'commentflag' => input('commentflag', 0,'intval'),
            'jumpurl' => input('jumpurl', ''),

        );
        $id = $data['id'];
        $pid = input('pid', 0, 'intval');
        $flags = input('flags', array(),'intval');
        $pic = $data['litpic'];

        if (empty($data['title'])) {
            $this->error('标题不能为空');
        }
        if (!$data['cid']) {
            $this->error('请选择栏目');
        }
        $pid = $data['cid'];//转到自己的栏目

        if (empty($data['description'])) {
            $data['description'] = str2sub(strip_tags($data['content']), 120);
        }


        //图片标志
        if (!empty($pic) && !in_array(B_PIC, $flags)) {
            $flags[] = B_PIC;
        }
        $data['flag'] = 0;
        foreach ($flags as $v) {
            $data['flag'] += $v;
        }




        //获取属于分类信息,得到modelid
        import('Class.Category', APP_PATH);
        $selfCate = Category::getSelf(getCategory(0), $data['cid']);//当前栏目信息
        $modelid = $selfCate['modelid'];



        if (false !== M('article')->save($data)) {
            //del
            M('attachmentindex')->where(array('arcid' => $id, 'modelid' => $modelid))->delete();

            //更新上传附件表
            if (!empty($pic)) {

                //$pic = preg_replace('/_(s|m)\.(jpg|gif|bmp|png)$/i', '.$2', $pic);//清除缩略图的_m,_s后缀
                $pic = preg_replace('/!(\d+)X(\d+)\.jpg$/i', '', $pic);//清除缩略图的!200X200.jpg后缀
                $attid = M('attachment')->where(array('filepath' => $pic))->getField('id');
                if($attid){
                    M('attachmentindex')->add(array('attid' => $attid,'arcid' => $id, 'modelid' => $modelid));
                }
                //hetlastsql());
            }


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
                        $dataAtt[] = array('attid' => $v,'arcid' => $id, 'modelid' => $modelid);
                    }
                    M('attachmentindex')->addAll($dataAtt);
                }

            }

            //更新静态缓存
            delCacheHtml('List/index_'.$data['cid'].'_', false, 'list:index');
            delCacheHtml('List/index_'.$selfCate['ename'], false, 'list:index');//还有只有名称
            delCacheHtml('Show/index_*_'. $id, false, 'show:index');//不太精确，会删除其他模块同id文档

            $this->success('修改成功', U(GROUP_NAME. '/Article/index', array('pid' => $pid)));
        }else {

            $this->error('修改失败');
        }

    }


    //回收站文章列表
    public function trach() {

        $where = array('article.status' => 1);

        $pid = input("pid", "", "intval");

        $list = model($this->article_model)
            ->getArticleByPage($where, "article.id DESC", 10);
        $page = $list->render();

        $subcate = "";

        $this->assign("pid", $pid);
        $this->assign("vlist", $list);
        $this->assign("page", $page);
        $this->assign("subcate", $subcate);
        $this->assign("type", "文章回收站");

        return $this->fetch("index");
    }

    //删除文章到回收站
    public function del() {

        $id = input('id',0 , 'intval');
        $batchFlag = input('get.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $this->delBatch();
            return;
        }

        $pid = input('pid',0 , 'intval');//单纯的GET没问题
        if (false !== M('article')->where(array('id' => $id))->setField('status', 1)) {

            delCacheHtml('Show/index_*_'. $id.'.', false, 'show:index');
            $this->success('删除成功', U(GROUP_NAME. '/Article/index', array('pid' => $pid)));

        }else {
            $this->error('删除失败');
        }
    }

    //批量删除到回收站
    public function delBatch() {

        $idArr = input('key',0 , 'intval');
        $pid = input('get.pid',0 , 'intval');

        if (!is_array($idArr)) {
            $this->error('请选择要删除的项');
        }


        if (false !== M('article')->where(array('id' => array('in', $idArr)))->setField('status', 1)) {

            //更新静态缓存
            foreach ($idArr as $v) {
                delCacheHtml('Show/index_*_'. $v.'.', false, 'show:index');
            }
            //. M('article')->getlastsql();
            $this->success('批量删除成功', U(GROUP_NAME. '/Article/index', array('pid' => $pid)));

        }else {
            $this->error('批量删除文失败');
        }
    }

    //还原文章
    public function restore() {

        $id = input('id',0 , 'intval');
        $batchFlag = input('get.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $this->restoreBatch();
            return;
        }

        $pid = input('get.pid', 0, 'intval');

        if (false !== M('article')->where(array('id' => $id))->setField('status', 0)) {

            $this->success('还原成功', U(GROUP_NAME. '/Article/trach', array('pid' => $pid)));

        }else {
            $this->error('还原失败');
        }
    }

    //批量还原文章
    public function restoreBatch() {

        $idArr = input('key',0 , 'intval');
        $pid = input('get.pid', 0, 'intval');
        if (!is_array($idArr)) {
            $this->error('请选择要还原的项');
        }

        if (false !== M('article')->where(array('id' => array('in', $idArr)))->setField('status', 0)) {

            $this->success('还原成功', U(GROUP_NAME. '/Article/trach', array('pid' => $pid)));

        }else {
            $this->error('还原失败');
        }
    }

    //彻底删除文章
    public function clear() {

        $id = input('id',0 , 'intval');
        $batchFlag = input('get.batchFlag', 0, 'intval');
        //批量删除
        if ($batchFlag) {
            $this->clearBatch();
            return;
        }

        $pid = input('get.pid', 0, 'intval');
        $modelid = D('ArticleView')->where(array('id' => $id))->getField('modelid');

        if (M('article')->delete($id)) {
            // delete picture index
            if ($modelid) {
                M('attachmentindex')->where(array('arcid' => $id , 'modelid' => $modelid ))->delete();//test
            }
            $this->success('彻底删除成功', U(GROUP_NAME. '/Article/trach', array('pid' => $pid)));
        }else {
            $this->error('彻底删除失败');
        }
    }


    //批量彻底删除文章
    public function clearBatch() {

        $idArr = input('key',0 , 'intval');
        $pid = input('get.pid', 0, 'intval');
        if (!is_array($idArr)) {
            $this->error('请选择要彻底删除的项');
        }
        $where = array('id' => array('in', $idArr));
        $modelid = D('ArticleView')->where(array('id' => $idArr[0]))->getField('modelid');//

        if (M('article')->where($where)->delete()) {
            // delete picture index
            if ($modelid) {
                M('attachmentindex')->where(array('arcid' => array('in', $idArr) , 'modelid' => $modelid ))->delete();
            }
            $this->success('彻底删除成功', U(GROUP_NAME. '/Article/trach', array('pid' => $pid)));
        }else {
            $this->error('彻底删除失败');
        }
    }

}