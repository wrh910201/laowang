<?php
namespace app\index\controller;

class Index extends Base
{
    public function index()
    {
        $this->assign('demo_time',$this->request->time());
        return $this->fetch();
    }
}
