<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/12
 * Time: 1:29
 */

namespace app\admin\controller;
use dir\Dir;
use think\Image;

/**
 * 工具类，目前主要是文件上传
 * Class Util
 * @package app\admin\controller
 */
class Util extends Base{


    //上传图片
    public function upload() {
        header("Content-Type:text/html; charset=utf-8");//不然返回中文乱码
        $tb = input('get.tb', 0, 'intval'); //缩略图地址前缀/,1:_s,2:_m,0默认


        //百度编辑新版要求--start
        //获取存储目录--对应百度编辑器
        $imgSavePathConfig = array (
            'uploads',
        );
        if ( isset( $_GET[ 'fetch' ] ) ) {

            header( 'Content-Type: text/javascript' );
            return 'updateSavePath('. json_encode($imgSavePathConfig) .');';

        }
        //百度编辑要求--end

        //文件上传地址提交给他，并且上传完成之后返回一个信息，让其写入数据库
        if(empty($_FILES)){
            //$this->error('必须选择上传文件');
            echo json_encode(array(
                'url' => '', 'title' => '',	'original' => '',
                'state' => '必须选择上传文件'
            ));
        }else{
            $info = $this->_uploadPicture();//获取图片信息

//            var_dump($info);exit();

            if(isset($info) && is_array($info)){
                //写入数据库的自定义c方法
                if(!$this->_uploadData($info)){
                    //echo '上传入库失败';
                    echo json_encode(array(
                        'url' => '',
                        'title' => '',
                        'original' => '',
                        'state' => '上传入库失败'
                    ));
                    exit();
                }
                //$picture_url = ltrim($info[0]['savepath'],'.').$info[0]['savename'];
                $picture_url = $info[0]['savepath'].$info[0]['savename'];
                //返回缩略图地址

                $picture_turl = $picture_url;
                //if ($tb == 2 || $tb == 1)
                {

                    //$picture_url = preg_replace('/\.(.+)$/', '_m.$1', $picture_url);//缩略图的_m,_s后缀
                    $imgtbSize = explode(',', $this->site_config['cfg_imgthumb_size']);//配置缩略图第一个参数
                    $imgTSize = explode('X', $imgtbSize[0]);


                    if (!empty($imgTSize)) {
                        $picture_turl = get_picture($picture_url, $imgTSize[0], $imgTSize[1]);
                    }
                }

                echo json_encode(array(
                    'url' => $picture_url,
                    'turl' => $picture_turl,
                    'title' => $info[0]['name'],
                    'original' => $info[0]['name'],
                    'state' => 'SUCCESS',
                    'size' => round($info[0]['size']/1024,2)
                ));
                exit;


            }else{
                //echo "{'url':'','title':'','original':'','state':'". $info ."'}";
                echo json_encode(array(
                    'url' => '',  'title' => '', 'original' => '',
                    'state' => '失败:'. $info
                ));
                exit;
            }
        }

    }


    //上传文件
    public function uploadFile() {
        header("Content-Type:text/html; charset=utf-8");//不然返回中文乱码


        //文件上传地址提交给他，并且上传完成之后返回一个信息，让其写入数据库
        if(empty($_FILES)){
            //$this->error('必须选择上传文件');
            echo json_encode(array(
                'url' => '', 'title' => '',	'original' => '',
                'state' => '必须选择上传文件'
            ));
        }else{
            $info = $this->_uploadFile();//获取图片信息

            //p($info);exit();

            if(isset($info) && is_array($info)){
                //写入数据库的自定义c方法
                if(!$this->_uploadData($info)){
                    //echo '上传入库失败';
                    echo json_encode(array(
                        'url' => '',
                        'title' => '',
                        'original' => '',
                        'state' => '上传入库失败'
                    ));
                    exit();
                }

                $file_url = $info[0]['savepath'].$info[0]['savename'];
                //返回地址


                echo json_encode(array(
                    'url' => $file_url,
                    'title' => $info[0]['name'],
                    'original' => $info[0]['name'],
                    'state' => 'SUCCESS',
                    'size' => round($info[0]['size']/1024,2)
                ));


            }else{
                //echo "{'url':'','title':'','original':'','state':'". $info ."'}";
                echo json_encode(array(
                    'url' => '',  'title' => '', 'original' => '',
                    'state' => '失败:'. $info
                ));

            }
        }

    }





    /**
    //图片(上传后)数组入库
    filearr:图片数组
     **/
    public function _uploadData($filearr) {

        $attachement_model = $this->lang == "zh-cn" ? "ZhAttachment" : "EnAttachment";

        $db = model($attachement_model);
        $num  = 0;

        for($i = 0; $i < count($filearr); ++$i) {
            $savepath = $filearr[$i]['savepath'];

            /*
            if (!empty($savepath) && substr($savepath,0,1)  == '.') {//判断首字符是否是'.'
                $savepath = substr($savepath,1,(strlen($savepath)-1));//去掉第一个字符
            }
            */

            $data['filepath'] = $savepath .$filearr[$i]['savename'];
            $data['title'] = $filearr[$i]['name'];
            $data['haslitpic'] = empty($filearr[$i]['haslitpic']) ? 0 : 1;
            $filetype =1;
            //后缀
            switch ($filearr[$i]['extension']) {
                case 'gif':
                    $filetype =1;
                    break;
                case 'jpg':
                    $filetype =1;
                    break;
                case 'png':
                    $filetype =1;
                    break;
                case 'bmp':
                    $filetype =1;
                    break;
                case 'swf'://flash
                    $filetype =2;
                    break;
                case 'mp3'://音乐
                    $filetype =3;
                    break;
                case 'wav':
                    $filetype =3;
                    break;
                case 'rm'://电影
                    $filetype =4;
                    break;

                case 'doc'://
                    $filetype =5;
                    break;
                case 'docx'://
                    $filetype =5;
                    break;
                case 'xls'://
                    $filetype =5;
                    break;
                case 'ppt'://
                    $filetype =5;
                    break;
                case 'zip'://
                    $filetype =6;
                    break;
                case 'rar'://
                    $filetype =6;
                    break;
                case 'pptx'://
                    $filetype =6;
                    break;
                case 'pdf'://
                    $filetype =6;
                    break;
                case 'xlsx'://
                    $filetype =6;
                    break;
                case '7z'://
                    $filetype =6;
                    break;

                default://其他
                    $filetype = 0;
                    break;
            }
            $data['filetype'] = $filetype;
            $data['filesize'] = $filearr[$i]['size'];
            $data['uploadtime'] = time();
            $data['aid'] = session('hkwcd_admin_operator.id');//管理员ID
            if( $db->insert($data))
            {
                ++$num;
            }
            //echo $db->getLastSql();
        }

        if($num==count($filearr))
        {
            return true;
        }else
        {
            return false;
        }


    }

    //上传图片
    public function _uploadPicture() {
        $ext = '';//原文件后缀
        $ext_dest = 'jpg';//生成缩略图格式

        $file = request()->file("upfile");
        if( empty($file) ) {
            $file = request()->file("mypic");
        }
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads/img1');
            if( $info ) {
                //缩略图设置
                $imgtbSize = explode(',', $this->site_config['cfg_imgthumb_size']);
                $imgtbArray = array();
                foreach( $imgtbSize as $v ) {
                    $t_size = explode("X", $v);
                    if (empty($t_size) || empty($t_size[0]) || empty($t_size[1])) {
                        continue;
                    }
                    $imgtbArray[] = array('w' => intval($t_size[0]), 'h' => intval($t_size[1]));
                }
                $real_path = './uploads/img1/'.$info->getSaveName();
                //读取配置文件固定宽等比缩略
                $imgtbFixWidth = explode(',', $this->site_config['cfg_imgthumb_width']);
                $imgtbFixArray = array();
                foreach ($imgtbFixWidth as $v) {
                    if (empty($v) || intval($v) == 0) {
                        continue;
                    }
                    $imgtbFixArray[] = array('w' => intval($v), 'h' => intval($v * 100));
                }

                if (!empty($imgtbFixArray) || !empty($imgtbArray)) {
                    $thumbType = $this->site_config['cfg_imgthumb_type'] ? 3:1;//配置大小
                    //生成缩略图,固定大小
                    foreach ($imgtbArray as $i => $v) {
                        $strSuffix = '!'.$v['w'].'X'.$v['h'];
                        $img = Image::open($real_path)->thumb($v['w'],$v['h'], $thumbType)
                            ->save($real_path.$strSuffix.'.'.$ext_dest,$ext_dest);

                    }
                    //生成缩略图，不放大，等宽，高度不限
                    foreach ($imgtbFixArray as $v) {
                        $strSuffix = '!'.$v['w'].'X';
                        $img = Image::open($real_path)->thumb($v['w'],$v['h'], 1)
                            ->save($real_path.$strSuffix.'.'.$ext_dest,$ext_dest);
                    }

                }


                //转换成网站根目录绝对路径,.Uploads 转成 /目录/Uploads
                $result[0]["size"] = $info->getInfo("size");
                $result[0]["name"] = $info->getInfo("name");
                $result[0]["extension"] = $info->getExtension();
                $result[0]["savename"] = $info->getFilename();
                $result[0]['savepath'] = str_replace($info->getFilename(), "", "/uploads/img1/".$info->getSaveName());//去掉第一个"."字符
                $result[0]['savepath'] = str_replace('\\', "/", $result[0]['savepath']);//去掉第一个"."字符
                $result[0]['haslitpic'] = 1;

                return $result;

            } else {
                return  $file->getError();
            }
        } else {

        }
    }



    //上传文件
    public function _uploadFile() {
        $ext = '';//原文件后缀
        foreach ($_FILES as $key => $v) {
            $strtemp = explode('.', $v['name']);
            $ext = end($strtemp);//获取文件后缀，或$ext = end(explode('.', $_FILES['fileupload']['name']));
            break;
        }

        import('ORG.Net.UploadFile');//导入ThinkPHP的上传类
        //..这里可以配置上传类的参数config，设置N个配置项，可在这里设置new UploadFile($config)
        $upload = new UploadFile();
        //只修改几个配置项，可在这里设置
        $upload->autoSub =true;//是否使用子目录保存图片
        $upload->subType = 'date';//子目录保存规则
        $upload->dateFormat = 'Ymd';
        $upload->maxSize = getUploadMaxsize();//设置上传文件大小
        //设置上传文件类型
        $upload->allowExts = explode(',', 'jpg,gif,png,jpeg,doc,docx,xls,ppt,zip,rar,mp3,pdf,pptx,xlsx');
        //上传目录
        $upload->savePath ='./uploads/file1/';

        //$upload->saveRule = 'time';
        $upload->saveRule = 'uniqid';//设置上传文件规则
        $upload->uploadReplace = true; //是否存在同名文件是否覆盖


        //$upload->upload('./uploads/file1/')
        if($upload->upload()) {
            $info = $upload->getUploadFileInfo();//获取文件信息
            //转换成网站根目录绝对路径,.Uploads 转成 /目录/Uploads
            $info[0]['savepath'] = __ROOT__.ltrim($info[0]['savepath'],'.');//去掉第一个"."字符
            $info[0]['haslitpic'] = 0;//没有缩略图
            return $info;

        }else {

            return $upload->getErrorMsg();
        }


    }


    public function getFileOfImg() {

        header("Content-Type: text/html; charset=utf-8");

        $action = input('action', '', 'trim');
        if (request()->isPost() && $action != 'get') {
            exit();
        }


        //需要遍历的目录列表，最好使用缩略图地址，否则当网速慢时可能会造成严重的延时
        //$paths = './uploads/img1';

        //显示有缩略图　文件
        $files = M('attachment')->where(array('filetype' => 1, 'haslitpic' => 1))->order('uploadtime DESC')->getField('filepath',50);//最新50条

        if ( !count($files) ) return;
        rsort($files,SORT_STRING);
        $str = "";
        foreach ( $files as $file ) {
            $str .= $file . "ue_separate_ue";
            $file = preg_replace('/\.(.+)$/', '_m.$1', $file);//缩略图
            $str .= $file . "ue_separate_ue";
        }
        echo $str;

    }


    //文件/夹管理
    function browseFile($spath = '', $stype = 'file') {
        $base_path = '/uploads/img1';
        $enocdeflag = input('encodeflag', 0, 'intval');
        switch ($stype) {
            case 'picture':
                $base_path = '/uploads/img1';
                break;
            case 'file':
                $base_path = '/uploads/file1';
                break;
            case 'ad':
                $base_path = '/uploads/abc1';
                break;
            default:
                exit('参数错误');
                break;
        }

        if ($enocdeflag) {
            $spath = base64_decode($spath);
        }

        $spath = str_replace('.', '', ltrim($spath,$base_path));

        $path = $base_path . '/'. $spath;

        $dir = new Dir('.'. $path);//加上.
        $list = $dir->toArray();
        for ($i=0; $i < count($list); $i++) {

            $list[$i]['isImg'] = 0;
            if ($list[$i]['isFile']) {
                $url =  rtrim($path,'/') . '/'. $list[$i]['filename'];
                $ext = explode('.', $list[$i]['filename']);
                $ext = end($ext);
                if (in_array($ext, array('jpg','png','gif'))) {
                    $list[$i]['isImg'] = 1;
                }
            }else {
                //为了兼容URL_MODEL(1、2)
//                if (C('URL_MODEL') == 1 || C('URL_MODEL') == 2) {
//                    $url = U(GROUP_NAME. '/Public/browseFile', array('stype' => $stype, 'encodeflag' => 1 ,'spath'=>base64_encode(rtrim($path,'/') . '/'. $list[$i]['filename'])));
//                }else {
                    $url = U(GROUP_NAME. '/Util/browseFile', array('stype' => $stype, 'spath'=> rtrim($path,'/') . '/'. $list[$i]['filename']));
//                }

            }
            $list[$i]['url'] = $url;
            $list[$i]['size'] = get_byte($list[$i]['size']);
        }
        //p($list);
        $parentpath = substr($path, 0, strrpos($path, '/'));
        //为了兼容URL_MODEL(1、2)
//        if (C('URL_MODEL') == 1 || C('URL_MODEL') == 2) {
//            $this->purl = U(GROUP_NAME. '/Public/browseFile', array('spath'=> base64_encode($parentpath),'encodeflag' => 1, 'stype' => $stype));
//        }else {
            $purl = U(GROUP_NAME. '/Util/browseFile', array('spath'=> $parentpath, 'stype' => $stype));
//        }
        $this->assign("purl", $purl);
        $this->assign("type", "浏览文件");
        $this->assign("vlist", $list);
        $this->assign("stype", $stype);
        $this->assign("type", "");

        return $this->fetch("public/browse_file");
    }

}