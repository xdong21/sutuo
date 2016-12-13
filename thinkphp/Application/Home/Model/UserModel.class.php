<?php
namespace Home\Model;
use Think\Model\MongoModel;

header("Content-type: text/html; charset=utf-8");
class UserModel extends MongoModel{
	public function createUser($userData){
		$userModel = new \Think\Model\MongoModel("user");
		$userCollection = $userModel->getCollection();
		$check = $userCollection->findOne(array('username' => $userData['username']));
		if ($check == null){
			$number = $userCollection->count();
			$userData['userId'] = $number;
			$userCollection->insert($userData);
		}else{
			$returnData = [
				'msg' => '用户名已存在'
			];
			return $returnData;
		}
	}

	/*验证密码登录说明：
	 *重新登录：islogin=0;
	 *填写验证码：islogin=1;
	 *验证码错误：islogin=2;
	 *请输入用户名或密码：islogin=3;
	 *用户名或密码不正确：$islogin=4;
	 *登陆成功：$islogin=5
	 */
	public function login($username,$psw,$validate){
		$userModel = new \Think\Model\MongoModel("user");
		$userCollection = $userModel->getCollection();
		session_start();
		$emptyPsw = md5(null);
		if ($username == "" || $psw == $emptyPsw) {
			$islogin = 3;
			$msg = '请输入用户名或密码';
			$returnData = [
					'islogin' => $islogin,
					'msg' => $msg
			];
			return $returnData;
		}
		else {
			//将用户输入的验证码转换为小写，因为$_SESSION["authnum_session"]也是小写格式的
			$validate=strtolower($validate);
			//判断session值与用户输入的验证码是否一致;
			if($validate!=$_SESSION["authnum_session"]){
				//不一致
				$islogin = 2;
				$msg = '验证码错误';
			} else {
				if ( $validate == "") {
			$islogin = 1;
			$msg = '填写验证码';
			}
				 else {
					$where = array("username" => $username, "password" => $psw);
					$user = $userCollection->findOne($where);
				    $userId = $user['userId'];
					if ($user) {
						//设置session的生存期为24小时,浏览器不能禁用cookie
						//如果客户端使用 IE 6.0 ， session_set_cookie_params(); 函数设置 Cookie 会有些问题
						$lifeTime = 24 * 3600;
						//setcookie(session_name(), session_id(), time() + $lifeTime, "/");
						session_set_cookie_params($lifeTime);
						//token=随机数+userId+浏览器user_agent
						$token = rand().$userId.$_SERVER['HTTP_USER_AGENT'];
						//生成session用于其他页面的登录权限
						$_SESSION['token'] = md5($token);
						setcookie('token', md5($token), time()+43200, '/');
						$userCollection->update($where,array('$set'=>array("token" => $_SESSION['token'])));
					    //防止session阻塞
					    session_write_close();    
					    $islogin = 5;
					    $msg = '登陆成功';
					} else {
						$islogin = 4;
						$msg = '用户名或密码不正确';
					}
				}
			}
		}
		$returnData = [
		    'islogin' => $islogin,
		    'msg' => $msg
		];
		return $returnData;
	}

	//密码验证
	//说明：重新登录：islogin=-1；密码错误：islogin=0；密码正确：islogin=1
	public function confirmPsw($psw){
		$userModel = new \Think\Model\MongoModel("user");
		$userCollection = $userModel->getCollection();
		session_start();
		$token = $_SESSION['token'];
		$user = $userCollection->findOne(array("token" => $token));
		if ($user){
			if ($user['password'] == $psw){
				$confirm = 1;
				$msg = '密码正确';
			}else {
				$confirm = 0;
				$msg = '密码错误';
			}
		}else {
			$confirm = -1;
			$msg = '重新登录';
			//session过期或重置，保存的token与数据库不一致，请重新登录
		}	
		$returnData = [
		    'confirm' => $confirm,
		    'msg' => $msg
		];
		return $returnData;
	}

	
	//用户登录状态验证，放到controller的函数里面
	//说明：登录成功：islogin=1；没有登录：islogin=0
	public function confirmUser(){
		session_start();
		if ($_SESSION['token'] != $_COOKIE['token']){
			$confirm = 0;
		}else {
			$userModel = new \Think\Model\MongoModel("user");
			$userCollection = $userModel->getCollection();
			//判断session携带的token与数据库中的token是否一致
			$user = $userCollection->findOne(array("token" => $_SESSION['token']));
			if ($user){
				//进入登录状态
				$islogin = 1;
				$msg = '登录成功';
			}else {
				$islogin = 0;
				$msg = '没有登录';
			}
		}
		session_write_close();
		$returnData = [
		    'islogin' => $islogin,
		    'msg' => $msg
		];
		return $returnData;
	}
}
?>