<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/11
 * Time: 15:49
 */

namespace app\common\controller;

use think\Controller;
use think\Lang;

class Base extends Controller {

    protected $lang = "";
    protected $site_config;

    public function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub

        define('B_PIC',1);// 图片
        define('B_TOP',2);// 头条 (置顶)
        define('B_REC',4);// 推荐
        define('B_SREC',8);// 特荐
        define('B_SLIDE',16);// 幻灯
        define('B_JUMP',32);// 跳转
        define('B_OTHER',64);//其他

        define("GROUP_NAME", request()->module());
        define("ACTION_NAME", request()->action());

        $lang_list = ['zh-cn','en-us'];
        Lang::setAllowLangList($lang_list);
        $this->lang = input("lang", "", "trim");

        if( empty($this->lang) ) {
             $session_lang = session("hkwcd_current_lang");
            if( $session_lang ) {
                $this->lang = $session_lang;
            }
        }
        $this->lang = !in_array($this->lang, $lang_list) ? "zh-cn" : $this->lang;
        session("hkwcd_current_lang", $this->lang);


        $this->site_config = include APP_PATH."config.site.{$this->lang}.php";

        $this->assign("current_lang", $this->lang);
        $this->assign("chinese_lang", "zh-cn");
        $this->assign("english_lang", "en-us");
        $this->assign("site_config", $this->site_config);

    }

}