<?php
namespace app\common\controller;
use think\Controller;
use think\Request;

class Common extends Controller {
    public $param;

    public function _initialize() {
        parent::_initialize();
        /*跨域*/
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authKey, sessionId");
        $param =  Request::instance()->param();
        $this->param = $param;
    }
}