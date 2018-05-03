<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 对用户的密码进行加密
 * @param $password
 * @param $encrypt //传入加密串，在修改密码时做认证
 * @return array/password
 */
function get_password($password, $encrypt='') {
    $pwd = array();
    $pwd['encrypt'] =  $encrypt ? $encrypt : get_randomstr();
    $pwd['password'] = md5(md5(trim($password)).$pwd['encrypt']);
    return $encrypt ? $pwd['password'] : $pwd;
}

/**
 * 获取客户端IP地址
 * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
 * @return mixed
 */
function get_client_ip($type = 0) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos    =   array_search('unknown',$arr);
        if(false !== $pos) unset($arr[$pos]);
        $ip     =   trim($arr[0]);
    }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip     =   $_SERVER['HTTP_CLIENT_IP'];
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}

function U($url='',$data=[],$suffix=true,$redirect=false,$domain=false) {
    if( strpos($url, "http") === false ) {
        $url = "/" . $url;
    }
    if( is_string($data) ) {
        return $url . "?" .$data;
    }
    if( is_array($data) ) {
        if( $data ) {
            $i = 0;
            foreach( $data as $k => $v ) {
                if( $i == 0 ) {
                    $url .= "?{$k}={$v}";
                } else {
                    $url .= "&{$k}={$v}";
                }
                $i++;
            }
        }
        return $url;
    }
}

function C($name) {
    return config($name);
}

//flag相加,返回数值，用于查询
function flag2sum($str, $delimiter = ',') {
    if (empty($str)) {
        return 0;
    }
    $tmp_arr = array_filter(explode($delimiter, $str));//去除空数组'',0,再使用sort()重建索引
    if (empty($tmp_arr)) {
        return 0;
    }

    $arr = array('a' => B_PIC, 'b' => B_TOP, 'c' => B_REC, 'd' => B_SREC, 'e' => B_SLIDE, 'f' => B_JUMP, 'g' => B_OTHER);
    $sum = 0;
    foreach ($arr as $k => $v) {
        if (in_array($k, $tmp_arr)) {
            $sum += $v;
        }
    }

    return $sum;


}

//清除分割符之间的空字符'',为'0'字符
//$flag 强制检测各成员是否为数字[true|false]
function string2filter($str, $delimiter = ',', $flag = false) {
    if (empty($str)) {
        return '';
    }

    $tmp_arr = array_filter(explode($delimiter, $str));//去除空数组'',0,再使用sort()重建索引
    $tmp_arr2 = array();

    //检验是不是数字
    if ($flag) {
        foreach ($tmp_arr as $v) {
            if (is_numeric($v)) {
                $tmp_arr2[] = $v;
            }
        }
    } else {
        $tmp_arr2 = $tmp_arr;
    }

    return implode($delimiter, $tmp_arr2);


}

function str2sub($str, $num, $flag = 0, $sp = '...') {
    if ($str == '' || $num <= 0) {
        return $str;
    }
    $strlen = mb_strlen($str, 'utf-8');
    $newstr ='';
    $newstr .= mb_substr($str, 0, $num, 'utf-8');//substr中国会乱码
    if ($num < $strlen && $flag) {
        $newstr .= $sp;
    }

    return $newstr;
}

/**
 *  获取枚举的值
 *
 * @access    public
 * @param     string    $group   联动组
 * @param     string    $evalue   联动值
 * @return    string
 */
function getValueOfItem($group, $value = 0) {
    //return $value.'--<br>';
    ${'item_'.$group} = getArrayOfItem($group);
    if(isset(${'item_'.$group}[$value])) {
        return ${'item_'.$group}[$value];
    }
    else {
        return "保密";
    }
}

function getArrayOfItem($group = 'animal', $update  = 0) {//S方法的缓存名都带's'

    $itme_arr = cache('sItem_'. $group);
    if ($update  || !$itme_arr) {
        $itme_arr = array();
        $lang = session("hkwcd_current_lang") ;
        $item_info_model = $lang == "zh-cn" ? "ZhItemInfo" : "EnItemInfo";
        $temp = model("admin/".$item_info_model)->where(array('group' => $group))->order('sort,id')->select();
        foreach ($temp as $key => $v) {
            $itme_arr[$v['value']] = $v['name'] ;
        }

        //cache(缓存名称,缓存值,缓存有效时间[秒]);
        cache('sItem_'. $group, $itme_arr, 48 * 60 * 60);
    }
    return $itme_arr;
}

//返回
function flag2Str($flag, $delimiter=' ', $iskey = false, $isarray = false) {
    if (empty($flag)) {
        return $isarray? array(): '';
    }
    $flagStr = array();
    $flagtype = getArrayOfItem('flagtype');//文档属性
    foreach ($flagtype as $k => $v) {
        if ($flag & $k) {
            $flagStr[] = $iskey? $k : $v;
        }
    }
    if ($isarray) {
        return $flagStr;
    } else {
        return implode($delimiter, $flagStr);
    }

}

function goLinkEncode($weburl = 'http://www.hoxinit.com/') {
    return U(C('DEFAULT_GROUP'). '/Go/link',array('url' => base64_encode($weburl)));
}


/*
*pic
*/
function get_picture($str, $width = 0, $height = 0, $rnd = false) {

    //$ext = end(explode('.', $str));
    $ext = 'jpg';//原文件后缀
    $ext_dest = 'jpg';//生成缩略图格式
    $height = $height == 0? '' : $height;
    if (!empty($str)) {
        $str = preg_replace('/!(\d+)X(\d+)\.'.$ext_dest.'$/i', '', $str);//清除缩略图的!200X200.jpg后缀

        $ext = explode('.', $str);
        $ext = end($ext);
    }
    if (empty($ext) || !in_array(strtolower($ext), array('jpg','gif','png','jpeg'))) {
        $str = '';
    }
    if (empty($str)) {
        $str =  __ROOT__.'/uploads/system/nopic.png' ;
        $ext = 'png';
        $ext_dest = 'png';
        $width = 0;
    }
    if ($width == 0) {
        return $str;
    }

    $rndstr = $rnd ? '?random='.time() : '';
    return $str.'!'.$width. 'X' .$height. '.'. $ext_dest. $rndstr ;
}

/**
 * 功能：计算文件大小
 * @param int $bytes
 * @return string 转换后的字符串
 */
function get_byte($bytes) {
    if (empty($bytes)) {
        return '--';
    }
    $sizetext = array(" B", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return round($bytes / pow(1024, ($i = floor(log($bytes, 1024)))), 2) . $sizetext[$i];
}