<?php
#
# ****************  素拓系统后台 班级部分 *****************
#
namespace Home\Controller;
use Think\Controller;

header("Content-type: text/html; charset=utf-8");  
class ClassController extends Controller {
    public function index(){
  
    }
// //创建班级   
// public function create(){
// 	$classModel = new \Home\Model\ClassModel;
// 	$classData = file_get_contents("php://input");
// 	//测试
// 	$classData = [
// 			'year' => 2013,
// 			'className' => '信工二班'
// 	];
// 	$classId = $classModel->create($classData);
// 	//var_dump($classId);
// 	echo $classId;
// }
// //读取班级
// public function read(){
// 	$classModel = new \Home\Model\ClassModel;
// 	$classId = $_GET['classId'];
// 	$response = $classModel->read($classId);
// 	echo $response;
// }
//修改班级信息
public function update(){
	$classModel = new \Home\Model\ClassModel;
// 	$postData = $_POST['data'];
	$postData = file_get_contents("php://input");
	//测试
// 	$dataArr = [
// 			["year" => 2013,
//             "yearMethod" => 0,
//             "password" => 123, 
//             "classes" => [
//             		[
//             		"classMethod" =>  1,
//             		"className" => "信工一班",
//             		"classId" => "201301",
//             		"studentCount" => 0
//             		],
//             		[
//             		"classMethod" =>  1,
//             		"className" => "信工二班",
//             		"classId" => "201302",
//             		"studentCount" => 0
//             		],
//             		[	
//                 "classMethod" =>  1,
//                 "className" => "信工三班",
//                 "classId" => "201303",
//                 "studentCount" => 0
//             		]
// 				 ]],
// 				 ["year" => 2014,
// 				 "yearMethod" => 1,
// 				 "classes" => [
// 				 		[
// 				 				"classMethod" =>  1,
// 				 				"className" => "信工一班",
// 				 				"classId" => "201401",
// 				 				"studentCount" => 0
// 				 		],
// 				 		[
// 				 		"classMethod" =>  1,
// 				 		"className" => "信工二班",
// 				 		"classId" => "201402",
// 				 		"studentCount" => 0
// 				 		],
// 				 		[
// 				 				"classMethod" =>  1,
// 				 				"className" => "信工三班",
// 				 				"classId" => "201403",
// 				 				"studentCount" => 0
// 				 		]
				 		
// 				 ]]
// 				 ];
// 	$postData = json_encode($dataArr);
	$response = $classModel->update($postData);
	//echo $response;
	echo $response;
}
// //删除班级
// public function delete(){
// 	$classModel = new \Home\Model\ClassModel;
// 	$classData = file_get_contents("php://input");
// 	/*测试
// 	$classId = 201605;
// 	$classId = json_encode($classId);*/
// 	$response = $classModel->delete($classId);
// 	//echo $response;
// 	echo $response;
// }    
}