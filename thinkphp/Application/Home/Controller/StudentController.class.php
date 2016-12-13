<?php
#
# ****************  素拓系统后台 学生部分 *****************
#
namespace Home\Controller;
use Think\Controller;
header("Content-type: text/html; charset=utf-8");  
class StudentController extends Controller {
    public function index(){

    }
//单独创建学生  
//注意：API没有seatId，这里补充进去
public function createOne(){
	$studentModel = new \Home\Model\StudentModel;
	$studentData = file_get_contents("php://input");
	//测试,注意使用studentName
	/*$studentData = [
			'studentId' => '201600000001',
			'seatId' => 1,
			'studentName' => '小明',
			'classId' => '201601'
	];
	$studentData = json_encode($studentData);*/
	$response = $studentModel->createOne($studentData);
	//var_dump($response);
	echo $response;
}
//批量创建学生
public function createMulti(){
	$studentModel = new \Home\Model\StudentModel;
	$studentData = file_get_contents("php://input");
	//测试
	$studentData = [
			[
			'studentId' => '201312341000',
			'seatId' => 1,
			'studentName' => '曹汀',
			'classId' => '201301'
			],
			[
			'studentId' => '201312341001',
			'seatId' => 2,
			'studentName' => '陈熹',
			'classId' => '201301'
			],
			[
			'studentId' => '201312341002',
		    'seatId' => 3,		
			'studentName' => '陈朝宾',
			'classId' => '201301'
			],
			[
			'studentId' => '201312341003',
			'seatId' => 4,
			'studentName' => '陈晓健',
			'classId' => '201301'
			],
			[
			'studentId' => '201312341004',
			'seatId' => 5,
			'studentName' => '陈新蔚',
			'classId' => '201301'
			],
			[
			'studentId' => '201312341005',
			'seatId' => 6,
			'studentName' => '方加鹏',
			'classId' => '201301'
			],
			[
			'studentId' => '201312341006',
			'seatId' => 7,
			'studentName' => '冯天健',
			'classId' => '201301'
			],
			[
			'studentId' => '201312341007',
			'seatId' => 8,
			'studentName' => '符子轩',
			'classId' => '201301'
			],
			[
			'studentId' => '201312341008',
			'seatId' => 9,
			'studentName' => '郭凯同',
			'classId' => '201301'
			]
	];
	$studentData = json_encode($studentData);
	$response = $studentModel->createMulti($studentData);
	//var_dump($response);	
	echo $response;
}

//从excel表格读取学生数据
//注意，只是从excel读取数据，返回供在前端显示，确认无误或修改后，需要调用上一个API实现创建学生操作
public function createImport(){
	$studentModel = new \Home\Model\StudentModel;
// 	$classId = $_POST['classId'];
//  $classId = file_get_contents("php://input");
    $classId = (string)$_POST['data'];
//  $classId = json_decode($classId);
//  $classId = $classId->classId;
	//测试
// 	include 'stuExcelTest.html';
// 	echo $classId = $_POST['classId'];
	$response = $studentModel->createImport($classId);
	echo $response;
}
//单独读取学生信息
public function readOne(){
	$studentModel = new \Home\Model\StudentModel;
	$studentId = $_GET['studentId'];
	$detail = $_GET['detail'];
	//测试
	/*
	$studentId = '201312341007';
	$detail = 1;
	$studentData = [
			'studentId' => $studentId,
			'detail' => $detail
	];
	$studentData = json_encode($studentData);*/
	$response = $studentModel->readOne($studentData);
	//var_dump($response);
	echo $response;
}
//批量读取学生信息
public function readMulti(){
	$studentModel = new \Home\Model\StudentModel;
 	$classId = $_GET['classId'];
// 	$detail = $_GET['detail'];
	//测试
   // $classId = "201301";
//  	$detail = 1;
// 	$studentData = [
// 			'classId' => $classId,
// 			'detail' => $detail
// 	];
// 	$studentData = json_encode($studentData);
	$response = $studentModel->readMulti($classId);
	//var_dump($response);
	echo $response;
}

//删除班级学生成员
public function deleteStudentOne(){
	$studentModel = new \Home\Model\StudentModel;
	$studentData = file_get_contents("php://input");
// 	$studentData = [
// 			'studentId' => "201312341000",
// 			'classId' => "201301",
// 			'seatId' => 1
// 	];
// 	$studentData = json_encode($studentData);
	$response = $studentModel->deleteStudentOne($studentData);
	echo $response;
}


//多活动excel表格批量读入素拓分
public function addSutuoImport(){
	$studentModel = new \Home\Model\StudentModel;
	include 'atyExcelTest.html';
	$response = $studentModel->addSutuoImport();
	echo $response;
}
//单活动excel表格批量读入素拓分
public function addSutuoImportOne(){
	$studentModel = new \Home\Model\StudentModel;
	$activityId = (int)$_POST['data'];
	$response = $studentModel->addSutuoImportOne($activityId);
	echo $response;
}
//单独删除素拓分
public function deleteSutuoOne(){
	$studentModel = new \Home\Model\StudentModel;
	$studentData = file_get_contents("php://input");
	//测试
	/*$studentData = [
			'studentId' => 201412345671,
			'studentName' => '小李',
			'sutuoCode' => ''
	];
	$studentData = json_encode($studentData);*/
	$response = $studentModel->deleteSutuoOne($studentData);
	//echo $response;
	echo $response;
}
//导出班级学生数据
public function classExport(){
	$studentModel = new \Home\Model\StudentModel;
	$classId = (string)file_get_contents("php://input");
// 	$classId = '201301';
	$response = $studentModel->classExport($classId);
 	echo $response;
} 
public function classExport2(){
	$data = json_encode(file_get_contents("php://input"));
	$objWriter = $data->objWriter;
	$filename = $data->filename;
	//告诉浏览器将要输出Excel03
	header('Content-Type: application/vnd.ms-excel');
	//告诉浏览器将要输出文件的名称
	header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
	//禁止缓存
	header('Cache-Control: max-age=0');
	$objWriter->save('php://output');
	}	
public function getAtyExcel(){
	$excelModel = new \Home\Model\StudentModel();
	$response = $excelModel->getAtyExcel();
	echo $response;
}

}