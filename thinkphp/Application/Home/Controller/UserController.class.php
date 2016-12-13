<?php

namespace Home\Controller;
use Think\Controller;

class UserController extends Controller {
    //添加用户
    public function createUser(){
        $userModel = new \Home\Model\UserModel();
        $userData = [
            'username' => 'abc',
            'password' => md5('123')
            //'signal' => 'super' //超级用户？
        ];
        $returnData = $userModel->createUser($userData);
        //注意用户名已存在的情况
//         var_dump($returnData);
    }

    //生成验证码
    public function createValidateCode(){
        Vendor('ValidateCode.ValidateCode');
        session_start();
        ob_clean();
        $_vc = new \ValidateCode();  //实例化一个对象
        $_vc->createImg();
        $_SESSION['authnum_session'] = $_vc->getCode();//文字验证码保存到SESSION中
    }

    //验证登录
    public function login(){
        $userModel= new \Home\Model\UserModel();
        /*
        //thinkphp的I函数，相当于$_POST,可自动进行htmlspecial的过滤
        //addslashes进行字符的转义
        $username = addslashes(I('post.username'));
        $psw = addslashes(I('post.password'));
        $validate = addslashes(I('post.validate'));
        */
        $data = file_get_contents("php://input");
        $data = (array)json_decode($data);
        $username = addslashes(htmlspecialchars($data['username']));
        $psw = addslashes(htmlspecialchars($data['password']));
        $validate = addslashes(htmlspecialchars($data['validate']));
        //最大长度为20,防止「缓冲区溢出攻击」
        $username = substr($username,0,20);
        $psw = substr($psw,0,50);
        $validate = substr($validate,0,20);
        //implode()函数将数组元素组合成字符串。防止数组的注入
        // $username = implode($username);
        // $psw = implode($psw);
        // $validate = implode($validate);
        // echo $validate;
        $returnData = $userModel->login($username,$psw,$validate);
        echo json_encode($returnData,JSON_UNESCAPED_UNICODE);
    }

    //登出
    public function logout(){
        session_start();
        //销毁整个 session 文件
        session_destroy();
        //删除cookie
        setcookie('token','',1,'/');
    }
    
    //密码验证
    public function confirmPsw(){
        $userModel = new \Home\Model\UserModel();
//      $data = file_get_contents("php://input");
        $data = (array)json_decode($data);
        $psw = $data['password'];
        $returnData = $userModel->confirmUser($psw);
        echo json_encode($returnData,JSON_UNESCAPED_UNICODE);
    }

    //特殊页面验证是否登录
    public function confirmUser(){
        $userModel = new \Home\Model\UserModel();
        $returnData = $userModel->confirmUser();
        echo json_encode($returnData,JSON_UNESCAPED_UNICODE);
    }
}