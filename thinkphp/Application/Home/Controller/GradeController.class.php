<?php
#
# ****************  素拓系统后台 年级部分 *****************
#
namespace Home\Controller;
use Think\Controller;

header("Content-type: text/html; charset=utf-8");  
class GradeController extends Controller {
    public function index(){
       
    }
//创建年级
// public function create(){
// 	$gradeModel = new \Home\Model\GradeModel();
// 	$year = file_get_contents("php://input");
// 	//测试
// 	$year = 2013;
// 	$response = $gradeModel->create($year);
// 	//var_dump($response);
// 	echo $response;
// }
//读取年级
public function read(){
	$gradeModel = new \Home\Model\GradeModel();
	$response = $gradeModel->read();
	//var_dump($response);
	echo $response;
}

// //删除年级
// public function delete(){
// 	$year = file_get_contents("php://input");
// 	/*测试
// 	$year = 2017;
// 	*/
// 	$gradeModel = new \Home\Model\GradeModel();
// 	$response = $gradeModel->delete($year);
// 	//var_dump($response);
// 	echo $response;
// }
}