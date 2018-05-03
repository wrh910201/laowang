<?php
/**
 * Created by PhpStorm.
 * User: wrh42
 * Date: 2018/4/11
 * Time: 15:39
 */

namespace app\admin\controller;

use app\common\controller\Base;

class Login extends Base  {

    public function index() {


        return $this->fetch();
    }

    public function login() {
        if ( !request()->isPost() )  return $this->fetch('public/empty');

        $username = input('username','','trim');
        $password = input('password','','trim');
        $verify = input('code','');

        if(!captcha_check($verify)){
            //验证失败
            $this->error(lang("admin_login_captcha"));
        };

        if ($username == '' || $password == '') {
            $this->error(lang("admin_login_required"));
        }

        $user = model('Admin')->where(['username' => $username])->find();

        if( empty($user) ) {
            $this->error(lang("admin_login_error"));
        } elseif( $user['password'] != get_password($password, $user['encrypt']) )  {
            $this->error(lang("admin_login_error"));
        }

        if ($user['islock']) {
            $this->error(lang("admin_login_user_locked"));
        }
        //更新数据库
        $user->logintime = time();
        $user->loginip = get_client_ip();
        $user->save();

        //usertype = 9 是超管
        $user = $user->toArray();
        session("hkwcd_admin_operator", $user);

        if( 9 == $user["usertype"] )
        {
            $this->redirect('/admin/index/index');
        }else{
            $this->redirect('/admin/index/index');
        }



        //redirect(__GROUP__);
        //$this->success('登录成功',U(GROUP_NAME. '/Index/index'));
    }

    public function logout() {
        session("hkwcd_admin_operator", null);
        $this->redirect("/admin/login/index");
    }
}