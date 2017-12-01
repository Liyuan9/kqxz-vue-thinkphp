<?php
/***
 *
 *
 * $role = {1:'超级管理员', 2:'管理员', 0:'普通'}
 * $step = {'attence': 'kaoqin', 'money': 'xinzi', 'people': 'user']
 *
 *
 ***/
namespace app\admin\controller;
use app\common\controller\Common;
use PHPExcel_IOFactory;
use PHPExcel;
use think\Db;

class Base extends Common {
    // 登录
    public function login() {
        $params = $this -> param;
        isset($params['username']) ? $map['username'] = $params['username'] : $map['username'] = '';
        isset($params['password']) ? $map['password'] = $params['password'] : $map['password'] = '';
        $info = db('user')->where($map)->find();
        if($info) {
            $token = md5($info['id'].$info['username'].time());
            session('token', $token);
            $oldPass = md5('Aa123456');
            if($oldPass === $info['password']) {
                $data['set'] = true;
            }else{
                $data['set'] = false;
            }
            $data['code'] = '200';
            $data['message'] = '登录成功';
            $data['token'] = $token;
            $data['userID'] = $info['id'];
            $data['role'] = $info['role'];
            session('username', $info['username']);
            session('userID', $info['id']);
            session('role', $info['role']);
        }else{
            $e = db('user')->where('username', $map['username'])->select();
            $data['code'] = '201';
            if ($e){
                $data['message'] = '密码错误';
            }else{
                $data['message'] = '用户名不存在';
            }
        }
        return json_encode($data);
    }

    // 用户信息完善
    public function userSet() {
        $params = $this -> param;
        isset($params['userID']) ? $userID = $params['userID'] : $userID = '';
        unset($params['userID']);
        $e = db('user') -> where('id', $userID) -> update($params);
        if($e) {
            $data['code'] = '200';
            $data['message'] = '人员信息更新成功,即将进入主页';
        }else{
            $data['code'] = '201';
            $data['message'] = '信息保存失败';
        }
        return json_encode($data);
    }

    // 获取用户信息
    public function user() {
        $params = $this -> param;
        isset($params['id']) ? $userID = $params['id'] : $userID = '';
        isset($params['type']) ? $type = $params['type'] : $type = '';
        if($userID){
            $info = db('user') -> where('id', $userID) -> find();
        }elseif($type == 'all') {
            $info = db('user') -> select();
        }
        if($info) {
            $data['code'] = '200';
            $data['message'] = '成功获取信息';
            $data['info'] = $info;
        }else{
            $data['code'] = '201';
            $data['message'] = '用户不存在';
        }
        return json_encode($data);
    }

    // 考勤列表
    public function attence() {
        $params = $this -> param;
        $list = array();
        $thead = Db::query("SELECT column_name,column_comment FROM information_schema.columns WHERE table_name = 'jk_kaoqin' and table_schema='kaoqinandxinzi'");
        foreach($thead as $key=>$va) {
            if($va['column_name'] == 'id' || strstr($va['column_name'], 'beizhu') || strstr($va['column_name'],'status')) {
                unset($thead[$key]);
            }
        }
        $title[0]['column_name'] = 'username';
        $title[0]['column_comment'] = '姓名';
        $list['thead'] = array_merge($title, $thead);
        isset($params['user']) ? $userID = $params['user'] : $userID = '';
        if(isset($params['step'])) {
            if ($params['step'] == 'all') {
                $attence = db('kaoqin') -> alias('a') -> join('jk_user b', 'a.userid = b.id', 'left') ->field('a.*, b.username') -> order('a.id desc') -> select();
            }elseif($params['step'] == 'person'){
                $attence = db('kaoqin') -> alias('a') -> join('jk_user b', 'a.userid = b.id', 'left') -> where('a.userid', $userID)->field('a.*, b.username') -> order('a.id desc') -> select();
            }else{
                if($userID){
                    $map['a.userid'] = $userID;
                    $map['a.status'] = 2;
                    $attence = db('kaoqin') -> alias('a') -> join('jk_user b', 'a.userid = b.id', 'left') -> where($map)->field('a.*, b.username') -> order('a.id desc') -> select();
                }else{
                    $attence = db('kaoqin') -> alias('a') -> join('jk_user b', 'a.userid = b.id', 'left') -> where('a.status', 2)->field('a.*, b.username') -> order('a.id desc') -> select();
                }
            }
            $list['list'] = $attence;
        }
        $month = db('kaoqin') -> field('distinct month') -> order('id desc') ->select();
        $list['month'] = $month;
        return json_encode($list);
    }

    // 详情
    public function detail() {
        $params = $this -> param;
        isset($params['id']) ? $id = $params['id'] : $id = '';
        isset($params['step']) ? $step = $params['step'] : $step = '';
        if($id == '' || $step == '') {
            $data['code'] = '201';
            $data['message'] = '请求出错';
        }else{
            switch($step) {
                case 'attence':
                    $title = Db::query("SELECT column_name, column_comment FROM information_schema.columns WHERE table_name = 'jk_kaoqin' and table_schema='kaoqinandxinzi'");
                    $info = db('kaoqin') -> alias('a') ->join('jk_user b', 'a.userid = b.id') -> where('a.id', $id) ->field('a.*, b.username as userid') -> find();
                    break;
                case 'money':
                    $info = db('xinzi') -> where('id', $id) -> find();
                    break;
                case 'people':
                    $info = db('user') -> where('id', $id) -> find();
                    break;
                default: break;
            }
            foreach($title as $key=>$vo){
                if(strstr($vo['column_name'], 'status') || strstr($vo['column_name'], 'beizhu')){
                    $comment[$vo['column_name']] = $info[$vo['column_name']];
                }else{
                    $arr[$vo['column_comment']] = $info[$vo['column_name']];
                }
            }
            if($info) {
                $data['code'] = '200';
                $data['message'] = '数据请求成功';
                $data['info'] = $arr;
                $data['comment'] = $comment;
                $data['title'] = $info['month'].'月';
                $data['name'] = $info['userid'];
            }else{
                $data['code'] = '201';
                $data['message'] = '请求数据不存在';
            }
        }
        return json_encode($data);
    }

    // 修改更新
    public function edit() {
        $params = $this -> param;
        if(isset($params['id'])){
            $id = $params['id'];
            unset($params['id']);
        }else{
            $id = '';
        }
        if(isset($params['type'])){
            $type = $params['type'];
            unset($params['type']);
        }else{
            $type = '';
        }
        if(isset($params['show'])){
            unset($params['show']);
        }
        if($id == '' || $type == ''){
            $data['code'] = '201';
            $data['message'] = '数据传输错误';
        }else{
            $e = db($type)->where('id',$id)->update($params);
            if($e){
                $data['code'] = '200';
                $data['message'] = '修改成功';
            }else{
                $data['code'] = '201';
                $data['message'] = '修改失败';
            }
        }
        return json_encode($data);
    }

    // 删除
    public function delete () {
        $params = $this -> param;
        isset($params['id']) ? $id = $params['id'] : $id = '';
        isset($params['type']) ? $type = $params['type'] : $type = '';
        isset($params['role']) ? $role = $params['role'] : $role = '';
        if($id == '' || $type == ''){
            $data['code'] = '201';
            $data['message'] = '数据传输错误';
        }else{
            if($type == 'user'){
                if($role != '1'){
                    $data['code'] = '201';
                    $data['message'] = '你没有删除权限';
                }else{
                    if(is_array($id)){
                        Db::startTrans();
                        try {
                            foreach($id as $v){
                                Db::table('jk_user')->where('id', $v)->delete();
                                Db::table('jk_kaoqin')->where('userid', $v)->delete();
                                Db::table('jk_xinzi')->where('userid', $v)->delete();
                                // 提交事务
                                Db::commit();
                                return json_encode(array('code'=>'200','message'=>'已成功删除'));
                            }
                        }catch (\Exception $e){
                            // 回滚事务
                            Db::rollback();
                            return json_encode(array('code'=>'201','message'=>'删除失败'));
                        }
                    }else{
                        Db::startTrans();
                        try {
                            Db::table('jk_user')->where('id', $id)->delete();
                            Db::table('jk_kaoqin')->where('userid', $id)->delete();
                            Db::table('jk_xinzi')->where('userid', $id)->delete();
                            // 提交事务
                            Db::commit();
                            return json_encode(array('code'=>'200','message'=>'已成功删除'));
                        }catch (\Exception $e){
                            // 回滚事务
                            Db::rollback();
                            return json_encode(array('code'=>'201','message'=>'删除失败'));
                        }
                    }
                }
            }elseif($type == 'kaoqin' || $type == 'xinzi'){
                if($role == 0) {
                    $data['code'] = '201';
                    $data['message'] = '你没有删除权限';
                }else{
                    if(is_array($id)){
                        Db::startTrans();
                        try {
                            foreach($id as $v){
                               Db::table('jk_'.$type)->where('id', $v)->delete();
                               // 提交事务
                               Db::commit();
                               return json_encode(array('code'=>'200','message'=>'已成功删除'));
                            }
                        }catch (\Exception $e){
                            // 回滚事务
                            Db::rollback();
                            return json_encode(array('code'=>'201','message'=>'删除失败'));
                        }
                    }else{
                        $e = db($type)->where('id', $id) ->delete();
                        if($e){
                            $data['code'] = '200';
                            $data['message'] = '已成功删除';
                        }else{
                            $data['code'] = '201';
                            $data['message'] = '删除失败';
                        }
                    }
                }
            }
        }
        return json_encode($data);
    }

    // 获取角色
    public function getrole() {
        $user = db('role') ->field('id,name,role') -> select();
        foreach($user as $key=>$v){
            $v['user'] = db('user') ->where('role', $v['role']) ->field('id as userid, username') -> select();
            $user[$key] = $v;
        }
        return json_encode($user);
    }

    // 文件导入
    public function upload() {
        if($_FILES) {
            $data = self::loadexcel($_FILES["file"]["tmp_name"], $_POST['step'], $_FILES["file"]["name"]);
        }else{
            $data['code'] = '400';
            $data['message'] = '服务器未收到上传文件！';
        }
        return json_encode($data);
    }

    public function loadexcel($file, $step, $name){

        vendor("PHPExcel.PHPExcel");
        $objReader = PHPExcel_IOFactory::createReaderForFile($file);
        $objPHPExcel = $objReader->load($file, $encode='utf-8');
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();           //取得总行数
        //$highestColumn = $sheet->getHighestColumn(); //取得总列数
        $data = array();
        $info = array();
        if($step == 'attence') {
            $table = db('kaoqin');
            $title = $objPHPExcel->getActiveSheet()->getCell("G1")->getValue();
            $msg = array();
            if($title != '打卡天数'){
                $msg['code'] = '201';
                $msg['message'] = '请上传正确的考勤表';
                return $msg;
            }else{
                for($j=2;$j<=$highestRow;$j++)                        //从第二行开始读取数据
                {
                    /*部门*/               $data[$j]['branch'] = $objPHPExcel->getActiveSheet()->getCell("D".$j)->getValue();
                    /*上班地点*/           $data[$j]['area'] = $objPHPExcel->getActiveSheet()->getCell("F".$j)->getValue();
                    /*工号*/               $data[$j]['id'] = $objPHPExcel->getActiveSheet()->getCell("A".$j)->getValue();
                    /*姓名*/               $data[$j]['username'] = $objPHPExcel->getActiveSheet()->getCell("B".$j)->getValue();
                    /*员工状态*/           $data[$j]['status'] = $objPHPExcel->getActiveSheet()->getCell("AD".$j)->getValue();
                    /*入职时间*/           $data[$j]['adddate'] = $objPHPExcel->getActiveSheet()->getCell("C".$j)->getValue();


                    /*工号*/               $info[$j]['userid'] = $objPHPExcel->getActiveSheet()->getCell("A".$j)->getValue();
                    /*考勤时间*/           $info[$j]['month'] = $objPHPExcel->getActiveSheet()->getCell("E".$j)->getValue();

                    /*打卡天数*/            $info[$j]['cardday'] = $objPHPExcel->getActiveSheet()->getCell("G".$j)->getValue();
                    /*公休天数*/            $info[$j]['restday'] = $objPHPExcel->getActiveSheet()->getCell("H".$j)->getValue();
                    /*加班天数*/            $info[$j]['overday'] = $objPHPExcel->getActiveSheet()->getCell("I".$j)->getValue();
                    /*异常天数*/            $info[$j]['unusualday'] = $objPHPExcel->getActiveSheet()->getCell("J".$j)->getValue();
                    /*有薪假天数*/          $info[$j]['paidday'] = $objPHPExcel->getActiveSheet()->getCell("K".$j)->getValue();
                    /*无薪假天数*/          $info[$j]['unpaidday'] = $objPHPExcel->getActiveSheet()->getCell("L".$j)->getValue();
                    /*无薪假小时*/          $info[$j]['unpaidhour'] = $objPHPExcel->getActiveSheet()->getCell("M".$j)->getValue();
                    /*迟到次数*/            $info[$j]['latetime'] = $objPHPExcel->getActiveSheet()->getCell("N".$j)->getValue();
                    /*早退次数*/            $info[$j]['leaveearly'] = $objPHPExcel->getActiveSheet()->getCell("O".$j)->getValue();
                    /*旷工次数*/            $info[$j]['absenteeism'] = $objPHPExcel->getActiveSheet()->getCell("P".$j)->getValue();
                    /*旷工时间*/            $info[$j]['absentime'] = $objPHPExcel->getActiveSheet()->getCell("Q".$j)->getValue();
                    /*请假次数*/            $info[$j]['leavetime'] = $objPHPExcel->getActiveSheet()->getCell("R".$j)->getValue();
                    /*C班次数*/             $info[$j]['Ctime'] = $objPHPExcel->getActiveSheet()->getCell("S".$j)->getValue();
                    /*D班次数*/             $info[$j]['Dtime'] = $objPHPExcel->getActiveSheet()->getCell("T".$j)->getValue();
                    /*N班次数*/             $info[$j]['Ntime'] = $objPHPExcel->getActiveSheet()->getCell("U".$j)->getValue();
                    /*C/D/N班补贴*/         $info[$j]['cdnsubsidy'] = $objPHPExcel->getActiveSheet()->getCell("V".$j)->getValue();
                    /*迟到早退扣款*/         $info[$j]['latededuct'] = $objPHPExcel->getActiveSheet()->getCell("W".$j)->getValue();
                    /*水电费扣款*/          $info[$j]['waterdeduct'] = $objPHPExcel->getActiveSheet()->getCell("Y".$j)->getValue();
                    /*门禁/钥匙扣款*/        $info[$j]['keydeduct'] = $objPHPExcel->getActiveSheet()->getCell("Z".$j)->getValue();
                    /*门禁/钥匙退款*/        $info[$j]['keyrefund'] = $objPHPExcel->getActiveSheet()->getCell("AA".$j)->getValue();
                }
            }
        }else{
            $table = db('xinzi');
            $month = substr($name,0,7);
            $title = $objPHPExcel->getActiveSheet()->getCell("H1")->getValue();
            $msg = array();
            $title = substr($title,0,12);
            if($title != '基本工资'){
                $msg['code'] = '201';
                $msg['message'] = '您上传的工作表格式错误，请选择正确的薪资表';
                return $msg;
            }else{
                for($j=2;$j<=$highestRow;$j++)                        //从第三行开始读取数据
                {
                    /*工号*/             $data[$j]['id'] = $objPHPExcel->getActiveSheet()->getCell("A".$j)->getValue();
                    /*姓名*/            $data[$j]['username'] = $objPHPExcel->getActiveSheet()->getCell("B".$j)->getValue();
                    /*大部门*/          // $data[$j]['bigbranch'] = $objPHPExcel->getActiveSheet()->getCell("B".$j)->getValue();
                    /*部门*/             $data[$j]['branch'] = $objPHPExcel->getActiveSheet()->getCell("C".$j)->getValue();
                    /*职位*/             $data[$j]['position'] = $objPHPExcel->getActiveSheet()->getCell("D".$j)->getValue();
                    /*入职日期*/         $data[$j]['adddate'] = $objPHPExcel->getActiveSheet()->getCell("E".$j)->getValue();
                    /*转正日期*/         $data[$j]['officialtime'] = $objPHPExcel->getActiveSheet()->getCell("F".$j)->getValue();
                    /*离职日期*/         $data[$j]['leavedate'] = $objPHPExcel->getActiveSheet()->getCell("G".$j)->getValue();
                    if($data[$j]['adddate'] != ''){
                        $data[$j]['adddate'] = gmdate("Y-m-d",$exceldate::ExcelToPHP($data[$j]['adddate']));
                    }
                    if($data[$j]['officialtime'] != ''){
                        $data[$j]['officialtime'] = gmdate("Y-m-d",$exceldate::ExcelToPHP($data[$j]['officialtime']));
                    }
                    if($data[$j]['leavedate'] != ''){
                        $data[$j]['leavedate'] = gmdate("Y-m-d",$exceldate::ExcelToPHP($data[$j]['leavedate']));
                    }
                    /*工号*/                   $info[$j]['userid'] = $objPHPExcel->getActiveSheet()->getCell("A".$j)->getValue();
                    /*'基本薪资*/              $info[$j]['basepay'] = $objPHPExcel->getActiveSheet()->getCell("H".$j)->getValue();
                    /*绩效薪资*/               $info[$j]['meritpay'] = $objPHPExcel->getActiveSheet()->getCell("I".$j)->getValue();
                    /*住房补贴标准*/            $info[$j]['housefee'] = $objPHPExcel->getActiveSheet()->getCell("J".$j)->getValue();
                    /*考核分*/                 $info[$j]['grade'] = $objPHPExcel->getActiveSheet()->getCell("K".$j)->getValue();
                    /*应发基本工资*/            $info[$j]['money'] = $objPHPExcel->getActiveSheet()->getCell("L".$j)->getValue();
                    /*应发绩效工资*/            $info[$j]['checkmerit'] = $objPHPExcel->getActiveSheet()->getCell("M".$j)->getValue();
                    /*计件/提成*/              $info[$j]['commission'] = $objPHPExcel->getActiveSheet()->getCell("N".$j)->getValue();
                    /*加班工资*/               $info[$j]['overtimepay'] = $objPHPExcel->getActiveSheet()->getCell("O".$j)->getValue();
                    /*房补*/                   $info[$j]['housepay'] = $objPHPExcel->getActiveSheet()->getCell("P".$j)->getValue();
                    /*夜班补贴*/               $info[$j]['nightfee'] = $objPHPExcel->getActiveSheet()->getCell("Q".$j)->getValue();
                    /*介绍费*/                 $info[$j]['intropay'] = $objPHPExcel->getActiveSheet()->getCell("R".$j)->getValue();
                    /*其他补贴*/               $info[$j]['otherpay'] = $objPHPExcel->getActiveSheet()->getCell("S".$j)->getValue();
                    /*迟到早退扣款*/           $info[$j]['latededuct'] = $objPHPExcel->getActiveSheet()->getCell("T".$j)->getValue();
                    /*其他应发应扣(税前)*/      $info[$j]['otherdeductbefore'] = $objPHPExcel->getActiveSheet()->getCell("U".$j)->getValue();
                    /*离职补偿金*/             $info[$j]['compensation'] = $objPHPExcel->getActiveSheet()->getCell("V".$j)->getValue();
                    /*应发工资*/               $info[$j]['payable'] = $objPHPExcel->getActiveSheet()->getCell("W".$j)->getValue();
                    /*社保*/                  $info[$j]['socialmoney'] = $objPHPExcel->getActiveSheet()->getCell("X".$j)->getValue();
                    /*住房公积金*/             $info[$j]['publicmoney'] = $objPHPExcel->getActiveSheet()->getCell("Y".$j)->getValue();
                    /*上月应扣个税*/           $info[$j]['income'] = $objPHPExcel->getActiveSheet()->getCell("Z".$j)->getValue();
                    /*门禁/钥匙()*/           $info[$j]['keydeduct'] = $objPHPExcel->getActiveSheet()->getCell("AA".$j)->getValue();
                    /*上月应缴水电费*/         $info[$j]['waterdeduct'] = $objPHPExcel->getActiveSheet()->getCell("AB".$j)->getValue();
                    /*上月应缴房费*/           $info[$j]['housededuct'] = $objPHPExcel->getActiveSheet()->getCell("AC".$j)->getValue();
                    /*其他应发应扣（税后）*/    $info[$j]['otherdeduct'] = $objPHPExcel->getActiveSheet()->getCell("AD".$j)->getValue();
                    /*实发工资*/               $info[$j]['realmoney'] = $objPHPExcel->getActiveSheet()->getCell("AE".$j)->getValue();
                    /*备注*/                  $info[$j]['remark'] = $objPHPExcel->getActiveSheet()->getCell("AF".$j)->getValue();
                    /*年月*/                  $info[$j]['month'] = $month;
                }
            }
        }
        $b = 0;
        $c = 0;
        foreach($data as $vo){
            $res = db('user')->where(array('id'=>$vo['id']))->find();
            if(!$res){
                $g = db('user')->where(array('username'=>$vo['username'],'branch'=>$vo['branch']))->find();
                if($g){
                    $result =  db('user')->where(array('username'=>$vo['username'],'branch'=>$vo['branch']))->update($vo);
                    if($result){
                        $c++;
                    }
                }else{
                    $a = 'Aa123456';
                    $a = md5($a);
                    $vo['password'] = $a;
                    $result = db('user')->insert($vo);
                    if($result){
                        $b++;
                    }
                }
            }else{
                $result =  db('user')->where(array('id'=>$vo['id']))->update($vo);
                if($result){
                    $c++;
                }
            }
        }

        $i = 0;
        $j = 0;
        foreach($info as $v){
            $re = $table->where(array('month'=>$v['month'],'userid'=>$v['userid']))->find();
            if($re){
                $result = $table->where(array('month'=>$v['month'],'userid'=>$v['userid']))->update($v);
            }else{
                $result = $table->insert($v);
            }
            if($result){
                $i++;
            }else{
                $j++;
            }
        }
        $msg['message'] = '';
        if($b>0){
            $msg['message'] = '新增用户：'.$b.'个；';
        }
        if($c>0){
            $msg['message'] = $msg['message'].'更新用户'.$c.'个；';
        }
        if($i>0){
            $msg['message'] = $msg['message'].'导入成功'.$i.'条；';
        }
        if($j>0){
            $msg['message'] = $msg['message'].'导入失败'.$j.'条,原因：该数据已经存在！。';
        }
        $msg['code'] = '200';
        return $msg;
    }
}