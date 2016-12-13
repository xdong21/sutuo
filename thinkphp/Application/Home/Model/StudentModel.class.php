<?php
namespace Home\Model;
use Think\Model\MongoModel;
use Org\Util\String;
header("Content-type: text/html; charset=utf-8");
class StudentModel extends MongoModel{
	//单独创建学生
 	public  function createOne($studentData){
		$studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		$classModel = new \Home\Model\ClassModel('class');
		$classCollection = $classModel->getCollection();
		$studentData = json_decode($studentData);
		$status = 1;
		$student = [
				    'studentId' => $studentData->studentId,
					'studentName' => $studentData->studentName,
					'classId' => $studentData->classId,
				    'seatId' => $studentData->seatId,
				    'sutuoScore' => [
				    	'general' => 0,
				    	'humanity' => 0,
				    	'creative' => 0
				    ],
					'sutuoItems' => []
				];				
		//判断是否已经创建数据库
		if (!studentCollection){
			$result = $studentCollection->insert($student);
		    $response = [
			    		'status' => $status,
			    		'msg' => ''
			    ];
			    return json_encode($response);
			}else{
		    //查询是否存在该学号
			$result = $studentCollection->findOne(array('studentId' => $studentData->studentId));
			if ($result){
				$response = [
						'status' => $status = 0,
						'msg' => '该学号已存在'
				];
				return json_encode($response,JSON_UNESCAPED_UNICODE);
			}
			//如果不存在该学号，则导入学生和班级数据库，并返回数据
			else {
			   $result = $studentCollection->insert($student);
			   $response = [
			   		'status' => $status,
			   		'msg' => ''
			   ];
			   $where = array('classId' => $studentData->classId);
			   $student = [
			   		'studentId' => $studentData->studentId,
			   		'seatId' => $studentData->seatId
			   ];
			   $class = $classCollection->findOne($where);
			   $studentCount = $class['studentCount'];
			   $result = $classCollection->update($where,array('$push' => array('students' => $student)));
			   $result = $classCollection->update($where,array('$inc' => array('studentCount' => 1)));
			   return json_encode($response);
			}
			}
		}
public  function createMulti($studentData){
		$studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		$classModel = new \Think\Model\MongoModel("class");
		$classCollection = $classModel->getCollection();
		if (!studentCollection){
			foreach ($studentData as $student){
				$where = array('classId' => $student['classId']);	
	        	//将sutuoScore和sutuoItems添加进数组$student
				$student=[
							'studentId' => $student['studentId'],
							'studentName' => $student['studentName'],
						    'seatId' => $student['seatId'],
							'classId' => $student['classId'],
							'sutuoScore' => [
								'general' => 0,
								'humanity' => 0,
								'creative' => 0
											],
							'sutuoItems' => []
									];
				$student2 = [
						'studentId' => (string)$student['studentId'],
						'seatId' => $student['seatId']
				];
				$result = $studentCollection->insert($student);
				$class = $classCollection->findOne($where);
				//更新学生人数
				$result = $classCollection->update($where,array('$inc' => array('studentCount' => 1)));
				//将学生数据插入班级数据库中的学生数组中
				$result = $classCollection->update($where,array('$push' => array('students' => $student2)));
				}
				$response = [
						'status' => 1,
						'msg' => ''
				];
				return json_encode($response,JSON_UNESCAPED_UNICODE);
		 }else{
			foreach ($studentData as $student){				
					$where = array('classId' => $student['classId']);
					$student=[
								'studentId' => $student['studentId'],
								'studentName' => $student['studentName'],
							    'seatId' => $student['seatId'],
								'classId' => $student['classId'],
								'sutuoScore' => [
									'general' => 0,
									'humanity' => 0,
									'creative' => 0
												],
							    'sutuoItems' => []
								 ];
					$student2 = [
							'studentId' => (string)$student['studentId'],
							'seatId' => $student['seatId']
					];
					//将学生数据插入学生数据库中
					$result=$studentCollection->insert($student);
					$class = $classCollection->findOne($where);
					$studentCount = $class['studentCount'];
					//更新学生人数
					$result = $classCollection->update($where,array('$inc' => array('studentCount' => 1)));
					//将学生数据插入班级数据库中的学生数组中
					$result = $classCollection->update($where,array('$push' => array('students' => $student2)));
						
					}
				}
				$response = [
						'status' => 1,
						'msg' => ''
						   ];
				return json_encode($response,JSON_UNESCAPED_UNICODE);
			}
		//删除班级学生成员
		public function deleteStudentOne($studentData){
			$studentModel = new \Think\Model\MongoModel("student");
			$studentCollection = $studentModel->getCollection();
			$classModel = new \Think\Model\MongoModel("class");
			$classCollection = $classModel->getCollection();
			$userModel = new \Home\Model\UserModel();
			$studentData = json_decode($studentData);
			$studentId = $studentData->studentId;
			$classId = $studentData->classId;
			$seatId = $studentData->seatId;
			$psw = $studentData->password;
			$confirm = $userModel->confirmPsw($psw);
			if ($confirm['confirm'] == 1){
			$student = [
					"studentId" => $studentId,
					"seatId" => $seatId
			];
			$result1 = $studentCollection->remove(array('studentId' => $studentData->studentId));
			$result2 = $classCollection->update(array("classId" => $classId),array('$pull' => array("students" => $student)));
			if ($result1 && $result2){
				$status = 1;
				$msg = "删除成功";
			}else {
				$status = 0;
				$msg = "删除失败";
			}
			} else {
				$status = 0;
				$msg = "密码错误";
			}
			$response = [
					"confirm" => $confirm['confirm'],
					"status" => $status,
					"msg" => $msg
			];
			return json_encode($response,JSON_UNESCAPED_UNICODE);
		}
//从excel表格读取学生数据
	public  function createImport($classId){
		$studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		$importFileName = iconv("utf-8","gb2312",$_FILES["file"]["name"]);
		//文件名相同
		if (file_exists("./Public/importExcel/student/" . $importFileName))
		{
			$status = 0;
			$msg = '导入失败，文件已存在，请核查';
			$response = [
					'status' => $status,
					'msg' => $msg
			];
			echo json_encode($response,JSON_UNESCAPED_UNICODE);
		}
		else
		{
		//$fileName = "./Public/importExcel/student/" . $_FILES["file"]["name"];
		$fileName = $_FILES["file"]["tmp_name"];
		//防止中文文件名出现乱码
		$fileName = iconv("utf-8","gb2312",$fileName);
		Vendor('PHPExcel.PHPExcel.IOFactory');
		//指定文件类型
		$excelType = ['excel5',
					  'excel2007'
					];
		//注意添加\
		$fileType = \PHPExcel_IOFactory::identify($fileName);
		//判断文件是否为excel格式
		$result = in_array_case($fileType,$excelType);
		if($result){
			$objReader = \PHPExcel_IOFactory::createReader($fileType);
			//$objReader = new PHPExcel_Reader_Excel5();
			$objPHPExcel = $objReader->load($fileName);
			$data = $objPHPExcel->getActiveSheet()->toArray();
			//$row用于防止读取第一行
			if (!strcmp($data['0']['0'], '序号') && !strcmp($data['0']['1'], '姓名') && !strcmp($data['0']['2'], '学号')){
				//flag=1时，说明表格第一行格式正确，可进一步读取表格，否则不读取表格
				$flag = 1;
			}else {
				$msg = '表格第一行格式与要求不符,导入失败';
				$status = 0;
				$response = [
						'status' => $status,
						'msg' => $msg
				];
				return json_encode($response,JSON_UNESCAPED_UNICODE);
			}
			foreach ($data as $row => $stuData){
				if ($row == 0){
					continue;
				}
				$seatId = (int)$stuData['0'];
				$studentName = $stuData['1'];
				$studentId = (string)$stuData['2'];
				//如果序号，姓名，学号存在空则该列不导入，这里不报错
				if (!$seatId || !$studentName || !$studentId){
					continue;
				}
				$studentData = [
						'studentId' => $studentId,
						'studentName' => $studentName,
						'seatId' => $seatId,
						'classId' => (string)$classId
				];
				$student = $studentCollection->findOne(array('studentId' => $studentId));
				//修改学生数据
				if ($student){
         if ($student['studentName'] == $studentName){					
					$updateStudentData[] = $studentData;
         }else {
         	$response = [
         			'status' => 0,
         			'msg' => "学号:".$studentId."与姓名:".$studentName."存在冲突"
         	];
         	return json_encode($response,JSON_UNESCAPED_UNICODE);
         }
				}else{//插入学生数据
			   $newStudentData[] = $studentData;
			}
			}
		//储存文件到指定文件夹./Public/importExcel/student
		move_uploaded_file($_FILES["file"]["tmp_name"],"./Public/importExcel/student/" .$importFileName);
				}else{
			$status = 0;
			$msg = '文件格式错误，导入失败';
			$response = [
					'status' => $status,
					'msg' => $msg
			];
			return json_encode($response,JSON_UNESCAPED_UNICODE); 
		}
		if ($updateStudentData != null){
		$response = $this->updateMulti($updateStudentData,$classId);
		if (!$response['status']){
			return json_encode($response,JSON_UNESCAPED_UNICODE);
		}
		}
		if ($newStudentData != null){
		$response = $this->createMulti($newStudentData);
		if (!$response['status']){
			return json_encode($response,JSON_UNESCAPED_UNICODE);
		}
		
		}
		$response = [
				'status' => 1,
				'msg' => '导入成功'
		];
		return json_encode($response,JSON_UNESCAPED_UNICODE);
	    //var_dump($response);
		}
		}
		//单独读取学生信息
	public function readOne($studentData){
	   	$studentModel = new \Think\Model\MongoModel("student");
	   	$studentCollection = $studentModel->getCollection();
	    $activityModel = new \Home\Model\ActivityModel();
	    $activityCollection = $activityModel->getCollection();
	    $classModel = new \Home\Model\ClassModel();
	    $classCollection = $classModel->getCollection();
		$studentData = json_decode($studentData);
		$studentId = $studentData->studentId;
		$detail =  $studentData->detail;
		$student = $studentCollection->findOne(array('studentId' => $studentId));
		$studentName = $student['studentName'];
		//定义普通分，人文素质教育分和创新能力培养分的初始值
		$general = $student['sutuoScore']['general'];
	    $humanity = $student['sutuoScore']['humanity'];
		$creative = $student['sutuoScore']['creative'];
	    //$from暂定
		$from = $student['optionSorce']['optionSourceCode'];
        //sutuoAddingItems存放多组数据，所以要遍历
		foreach ($student['sutuoItems'] as $sutuoItems){	
			$activityId = $sutuoItems['addingFrom']['activityId'];
		    //var_dump($activityId);
		    $activity = $activityCollection->findOne(array('activityId' => $activityId));
		    $activityName = $activity['name'];
		    //$sutuoType即'思想政治与道德素养','社会实践与志愿服务','科技学术与创新创业','文体艺术与身心发展'，'科学创新分','文体创新分'
		    $sutuoType = $activity['sutuoType'];
		    //sutuo数组
		    $sutuo = $activity['sutuo'];
		    $sutuoName = $sutuo['sutuoName'];
			//$sutuoScore += $activity['sutuo']['0']['score'];
			//返回的sutuoItem数组,疑问：API里是time,是否要用addingTime,活动名称activivy是否要用name
			$sutuoItem = [
					'addingTime' => $sutuoItems['addingTime'],
					'score' => $sutuoItems['score'],
					'activityName' => $activityName,
					'sutuoName' => $sutuoName,
					'sutuoType' => $sutuoType,
					'from' => $from
			];
			$sutuoItems[] = $sutuoItem;
			//var_dump($sutuoItems);
		    }
		    
			$classId = $student['classId'];
		    $class = $classCollection->findOne(array('classId' => $classId));
		    $className = $class['className'];
		 	if ($detail){
				$response =[
						'studentId' => $studentId,
						'studentName' => $studentName,
						'classId' => $classId,
						'className' => $className,
						'sutuoScore' => [
								'general' => $general,
								'humanity' => $humanity,
								'creative' => $creative
								
				],
				$sutuoItems
				];
			}else {
				$response = [
						'studentId' => $studentId,
						'studentName' => $studentName,
						'classId' => $classId,
						'className' => $className,
						'sutuoScore' => [
								'general' => $general,
								'humanity' => $humanity,
								'creative' => $creative
						]
				];
			}
		 	return json_encode($response,JSON_UNESCAPED_UNICODE);
	   }
//批量读取学生基本信息
	   //注意API中的name不知道指的是studentName还是className
	   //API里返回的信息与单独读取有所减少，这里按照单独读取返回的信息返回.
	public function readMulti($classId){
	   	$studentModel = new \Think\Model\MongoModel("student");
	   	$studentCollection = $studentModel->getCollection();
	   	$activityModel = new \Think\Model\MongoModel("activity");
	   	$activityCollection = $activityModel->getCollection();
	   	$classModel = new \Home\Model\ClassModel();
	   	$responseNum = 0;
	   	$classCollection = $classModel->getCollection();
// 	   	$studentData = json_decode($studentData);
// 	   	$classId = $studentData['classId'];
	   	$class = $classCollection->findOne(array('classId' => $classId));
	   	$className = $class['className'];
	   	$students = $class['students'];
	   	foreach ($students as $stu){
	   		//初始化sutuoItems
	   		$sutuoItems = null;
	   		$studentId = $stu['studentId'];
	   		//var_dump($studentId);
	   		$student = $studentCollection->findOne(array('studentId' => $studentId));
	   		//获得普通分，人文素质教育分和创新能力培养分
	   		$general = $student['sutuoScore']['general'];
	   		$humanity = $student['sutuoScore']['humanity'];
	   		$creative = $student['sutuoScore']['creative'];
	   		$studentName = $student['studentName'];
	   		$seatId = $student['seatId'];
	   		//学生刷新，素拓项也刷新
	   		$items = null;
	   		//sutuoItems存放多组数据，所以要遍历
	   		foreach ($student['sutuoItems'] as $sutuoItems){	   			 
	   			//var_dump($sutuoAddingItems);
	   			$activityId = $sutuoItems['addingFrom']['activityId'];
	   			//var_dump($activityId);
	   			$activity = $activityCollection->findOne(array('activityId' => $activityId));
	   			$activityName = $activity['name'];
	   			$sutuoType = $activity['sutuoType'];
	   			//sutuo数组
	   			$sutuo = $activity['sutuo'];
	   			    $sutuoName = $sutuo['sutuoName'];
	   				$item = [
						'addingTime' => $sutuoItems['addingTime'],
	   					'score' => $sutuoItems['score'],
						'activityName' => $activityName,
						'sutuoName' => $sutuoName,
						'sutuoType' => $sutuoType
			                     ];
	   				$items[] = $item;
	   				//var_dump($sutuoItems);
	   			
	   		}
	   			$response =[
	   					'studentId' => $studentId,
	   					'seatId' => $seatId,
	   					'studentName' => $studentName,
	   					'classId' => $classId,
	   					'className' => $className,
	   					'sutuoScore' => [
	   							'general' => $general,
	   							'humanity' => $humanity,
	   							'creative' => $creative
	   		
	   					],
	   					"sutuoItems" => $items
	   			];          
	   		$seatIdArr[] = $seatId;	        	   
	   		$responses[] = $response;
	   	}
	   	//按照seatId从小到大排序
	   	array_multisort($seatIdArr, SORT_ASC, $responses);
	   	return json_encode($responses,JSON_UNESCAPED_UNICODE);
	   }

 //修改多个学生基本信息
	public function updateMulti($multiStudentData,$classId){
		$studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		$classModel = new \Think\Model\MongoModel("class");
		$classCollection = $classModel->getCollection();
		foreach ($multiStudentData as $studentData){
	    $studentId = $studentData['studentId'];
		$studentName = $studentData['studentName'];
		$seatId = $studentData['seatId'];
		//该学生新的学生数组，用于更新班级数据库
		$stu = [
				'studentId' => $studentId,
				'seatId' => $seatId
		];
		$student = $studentCollection->findOne(array('studentId' => $studentId));
		$oldSeatId = $student['seatId'];
		$oldClassId = $student['classId'];
		$where = array("studentId" =>  $studentId);
		//修改学生数据库中的学生信息
		$result1 = $studentCollection->update($where,array('$set' => array('studentName'=>$studentName,'classId' => $classId,'seatId' => $seatId)));
			if (!$result1){
				$response = [
						'status' => 0,
						'msg' => '修改失败'
				];
			return json_encode($response,JSON_UNESCAPED_UNICODE);	
			}
		//如果修改了classId,则删除班级数据库中的原班级该学生的节点，这里classId不修改的话传过来原classId
		if( ($oldClassId != $classId) || ($oldSeatId != $seatId) ){
			$where = array('classId' => $oldClassId);
			$class = $classCollection->findOne($where);
			$students = $class['students'];
			foreach ($students as $student){
				if ($student['studentId'] == $studentId){
					if ($oldClassId != $classId){
						//删除班级数据库中的原班级该学生的节点
						$result4 = $classCollection->update($where,array('$pull' => array('students' => $stu)));
						//更新学生人数
						$student2 = $classCollection->findOne($where);
						$studentCount = count($student2['students']);
						$result5 = $classCollection->update($where,array('$set' => array('studentCount' => $studentCount)));
						//班级数据库中在该学生新的班级添加新节点
						$newWhere = array('classId' => $classId);
						//该学生新的学生数组，用于更新班级数据库
						$stu = [
								'studentId' => $studentId,
								'seatId' => $seatId
						];
						$result6 = $classCollection->update($newWhere,array('$push' => array('students' => $stu)));
						//更新学生人数
						$student2 = $classCollection->findOne($newWhere);
						$studentCount = count($student2['students']);
						$result7 = $classCollection->update($newWhere,array('$set' => array('studentCount' => $studentCount)));
					}else{
					$result2 = $classCollection->update($where,array('$pull' => array('students' => $student)));
					$result3 = $classCollection->update($where,array('$push' => array('students' => $stu)));
				}
				}
				
			}
			
			}
		}
				$response = [
						'status' => 1,
						'msg' => ''
				];
		return json_encode($response);
		}	
		
		
//单活动从excel表格批量添加素拓分
		public function addSutuoImportOne($activityId){
			$studentModel = new \Think\Model\MongoModel("student");
			$studentCollection = $studentModel->getCollection();
			$activityModel = new \Think\Model\MongoModel("activity");
			$activityCollection = $activityModel->getCollection();
			//文件名相同
			$importFileName = iconv("utf-8","gb2312",$_FILES["file"]["name"]);
			if ($importFileName == null){
				$response = [
						'status' => 0,
						'msg' => '文件名为空'
				];
				return json_encode($response,JSON_UNESCAPED_UNICODE);
			}
			if (file_exists("./Public/importExcel/activity/one/" . $importFileName))
			{
				$response = [
						'status' => 0,
						'msg' => '导入失败，此文件已存在，请核查'
				];
				return json_encode($response,JSON_UNESCAPED_UNICODE);
			}
			else
			{
				//$fileName = "./Public/importExcel/activity/" . $_FILES["file"]["name"];
				$fileName = $_FILES["file"]["tmp_name"];
				//防止中文文件名出现乱码
				$fileName = iconv("utf-8","gb2312",$fileName);
				//导入PHPExcel类库中的IOFactory.php
				Vendor('PHPExcel.PHPExcel.IOFactory');
				//指定文件类型
				$excelType = ['excel5',
						'excel2007'
				];
				//注意添加\
				$fileType = \PHPExcel_IOFactory::identify($fileName);
				//判断文件是否为excel格式
				$result = in_array_case($fileType,$excelType);
				if($result){
					$objReader = \PHPExcel_IOFactory::createReader($fileType);
					//$objReader = new PHPExcel_Reader_Excel5();
					$objPHPExcel = $objReader->load($fileName);
					$key = null;
					$response = $this->excelReader($objPHPExcel,$key,$activityId,null);
					if (!$response['status']){
						return json_encode($response,JSON_UNESCAPED_UNICODE);
						}else {
						//将addSutuoData定义为与多活动格式一样的导入数组，方便函数addSutuoScore加分
						$addSutuoData[] = [
											'studentIdArr' => $response['studentIdArr'],
											'addScoreArr' => $response['addScoreArr'],
											'atyNameArr' => $response['atyNameArr']
									];
								}
					//添加素拓分
					$result = $this->addSutuoScore($addSutuoData);
					if ($result){
						$response = [
								'status' => 1,
								'msg' => '导入成功'
						];
						//储存文件到指定文件夹./Public/importExcel/activity
						move_uploaded_file($_FILES["file"]["tmp_name"],"./Public/importExcel/activity/one/" . $importFileName);
						$excelModel = new \Think\Model\MongoModel("excel");
						$excelCollection = $excelModel->getCollection();
						$excelName = substr($importFileName,0,strrpos($importFileName, "."));						
						//函数substr获取的字符串为utf-8格式，所以需要再次将字符串转换为gb2312，才可以正常导入中文文件名
// 						$excelName = iconv("utf-8","gb2312",$excelName);
						$excelData = [
								'addingTime' => date("y/m/d"),
								'excelName' => $excelName
						];
						$excelCollection->insert($excelData);
						return json_encode($response,JSON_UNESCAPED_UNICODE);
					}else {
						$response = [
								'status' => 0,
								'msg' => '导入失败'
						];
						return json_encode($response,JSON_UNESCAPED_UNICODE);
					}
				}
				else{
					$response = [
							'status' => 0,
							'msg' => '文件格式错误，导入失败'
					];
					return json_encode($response,JSON_UNESCAPED_UNICODE);
				}
				 
			}
		}
	//多活动从excel表格批量添加素拓分
	public function addSutuoImport(){
	    $studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		$activityModel = new \Think\Model\MongoModel("activity");
		$activityCollection = $activityModel->getCollection();
		//文件名相同
		$importFileName = iconv("utf-8","gb2312",$_FILES["file"]["name"]);
		if ($importFileName == null){
			$response = [
					'status' => 0,
					'msg' => '文件名为空'
			];
			return json_encode($response,JSON_UNESCAPED_UNICODE);
			}
	
		if (file_exists("./Public/importExcel/activity/multi/".$importFileName))
		{
		$response = [
			 						'status' => 0,
			 						'msg' => '导入失败，此文件已存在，请核查'
			 				];
			 	return json_encode($response,JSON_UNESCAPED_UNICODE);
		}
		else
		{
		//$fileName = "./Public/importExcel/activity/" . $_FILES["file"]["name"];
		$fileName = $_FILES["file"]["tmp_name"];
	    //防止中文文件名出现乱码
		$fileName = iconv("utf-8","gb2312",$fileName);
		//导入PHPExcel类库中的IOFactory.php
		Vendor('PHPExcel.PHPExcel.IOFactory');		
		//指定文件类型
		$excelType = ['excel5',
		    		  'excel2007'
		  			  ];
		//注意添加\
		$fileType = \PHPExcel_IOFactory::identify($fileName);
	    //判断文件是否为excel格式
		$result = in_array_case($fileType,$excelType);
	    if($result){
		$objReader = \PHPExcel_IOFactory::createReader($fileType);
	    //$objReader = new PHPExcel_Reader_Excel5();
		$objPHPExcel = $objReader->load($fileName);
		//获取sheet名称
		$sheetNames = $objPHPExcel->getSheetNames();
		foreach ($sheetNames as $key => $sheetName){
		switch ($sheetName){
			case "文体艺术与身心发展":
				$response = $this->excelReader($objPHPExcel, $key, "multi", $sheetName);
				if (!$response['status']){
					return json_encode($response,JSON_UNESCAPED_UNICODE);
				}else {
					$data1 = [
							'studentIdArr' => $response['studentIdArr'],
							'addScoreArr' => $response['addScoreArr'],
							'atyNameArr' => $response['atyNameArr']
					];
					$addSutuoData[] = $data1; 
				}
				break;
			case "思想政治与道德素养":
		$response = $this->excelReader($objPHPExcel, $key, "multi", $sheetName);
				if (!$response['status']){
					return json_encode($response,JSON_UNESCAPED_UNICODE);
				}else {
					$data2 = [
							'studentIdArr' => $response['studentIdArr'],
							'addScoreArr' => $response['addScoreArr'],
							'atyNameArr' => $response['atyNameArr']
					];
					$addSutuoData[] = $data2; 
				}
			 	break;
			case "科学技术与创新创业":				 			
				$response = $this->excelReader($objPHPExcel, $key, "multi", $sheetName);
				if (!$response['status']){
					return json_encode($response,JSON_UNESCAPED_UNICODE);
				}else {
					$data3 = [
							'studentIdArr' => $response['studentIdArr'],
							'addScoreArr' => $response['addScoreArr'],
							'atyNameArr' => $response['atyNameArr']
					];
					$addSutuoData[] = $data3; 
				}
			 	break;
			case "社会实践与志愿服务":
				$response = $this->excelReader($objPHPExcel, $key, "multi", $sheetName);
				if (!$response['status']){
					return json_encode($response,JSON_UNESCAPED_UNICODE);
				}else {
					$data4 = [
							'studentIdArr' => $response['studentIdArr'],
							'addScoreArr' => $response['addScoreArr'],
							'atyNameArr' => $response['atyNameArr']
					];
					$addSutuoData[] = $data4; 
				}
			   break;
			   
			 	}
			 			}
			 			var_dump($addSutuoData);
			 	//添加素拓分
			 	$result = $this->addSutuoScore($addSutuoData);
			 	if ($result){
			 				$response = [
			 						'status' => 1,
			 						'msg' => '导入成功'
			 				];
			 	//储存文件到指定文件夹./Public/importExcel/activity/multi/
 			 	move_uploaded_file($_FILES["file"]["tmp_name"],"./Public/importExcel/activity/multi/" . $importFileName);
			 	$excelModel = new \Think\Model\MongoModel("excel");
						$excelCollection = $excelModel->getCollection();
						//去除excel文件后缀，获取文件名,不需要使用iconv
 						$excelName = substr($_FILES['file']['name'],0,strrpos($_FILES['file']['name'], "."));
						$excelData = [
								'addingTime' => date("y/m/d"),
								'excelName' => $excelName
						];
						$excelCollection->insert($excelData);
 			 	return json_encode($response,JSON_UNESCAPED_UNICODE);
			 			} else {
			 	$response = [
			 						'status' => 0,
			 						'msg' => '加法出错，导入失败'
			 				];
			 	return json_encode($response,JSON_UNESCAPED_UNICODE);
			 			}
		
			 
			 } 
		    else{
		    	$response = [
		    			'status' => 0,
		    			'msg' => '文件格式错误，导入失败' 
		    	];
		    	return json_encode($response,JSON_UNESCAPED_UNICODE);
		    }
		   
		}
		}
	public function excelReader($objPHPExcel,$key = null,$activityId = "multi",$sheetName = null){
        $activityModel = new \Think\Model\MongoModel("activity");
        $activityCollection = $activityModel->getCollection();
        $studentModel = new \Think\Model\MongoModel("student");
        $studentCollection = $studentModel->getCollection();
        //导入PHPExcel类库中的IOFactory.php
        Vendor('PHPExcel.PHPExcel.IOFactory');
        if ($activityId === "multi"){
		$data = $objPHPExcel->getSheet($key)->toArray();
		$row = -1;
		//stuData为每一行的内容数组
		foreach ($data as $rowData){
			$row ++;
			//读取第一行内容
			if ($row == 0){
				if ($rowData['3'] == '活动名称'){
					continue;
				}else {
					$status = 0;
					$msg = "表格".$sheetName."第一行格式不符合要求";
					return $response = [
							'status' => $status,
							'msg' => $msg
					];
				}
			}
			//读取表格第二行的活动名称，判断要导入的活动是否存在于数据库中
			if ($row == 1){
				if (strcmp($rowData['0'], '学号') || strcmp($rowData['1'], '序号') || strcmp($rowData['2'], '姓名')){
					$status = 0;
					$msg = "表格".$sheetName."第二行格式不符合要求";
					return $response = [
							'status' => $status,
							'msg' => $msg
					];
				}
				for ($column = 3; $column < count($rowData); $column++){
					//将多个空格符转换为1个空格符,左右两边不留空格符
					$atyName = preg_replace( "/\s(?=\s)/","\\1", $rowData[$column]);
					$atyName = trim($atyName);
					//如果活动名称为空则结束本次循环，执行下次循环
					if ($atyName == null){
						continue;
					}else {
						$activityByAtyName = $activityCollection->findOne(array("name" => $atyName));
						if ($activityByAtyName){
							//查找活动名称对应的activityId
							$activityId = $activityByAtyName['activityId'];
						}else {
							$status = 0;
							$msg = "$atyName".'活动名错误';
							//退出两重循环，即退出excelReader
							return $response = [
									'status' => $status,
									'msg' => $msg
							];
						}
					}
				}
			}
			//读取表格第三行及第三行以后的内容
			else {
				$studentId = (string)$rowData['0'];
				//如果读取到的studentId为空则结束本次循环，重新开始执行下一次循环
				if ($studentId == null){
					continue;
				}
				$seatId = $rowData['1'];
				$studentName = $rowData['2'];
				//获得学号对应的学生数据
				$studentByStuId = $studentCollection->findOne(array('studentId' => $studentId));
				$studentByStuName = $studentCollection->findOne(array('studentName' => $studentName));
				//strcmp字符串相等返回0
				//判断表格上的姓名是否跟学号对应的姓名是否一样
				if ($studentByStuName == null){
					$status = 0;
					$msg = "表格".$sheetName."中学生".$studentName."不存在";
					//退出一重循环，即退出excelReader.php
					return $response = [
							'status' => $status,
							'msg' => $msg
					];
				}elseif ($studentByStuId == null){
					$status = 0;
					$msg = "表格".$sheetName."中学号".$studentId."不存在";
					//退出一重循环，即退出excelReader.php
					return $response = [
							'status' => $status,
							'msg' => $msg
					];
				}elseif (strcmp($studentName, $studentByStuId['studentName'])){
					$status = 0;
					$msg = "表格".$sheetName."中".$studentName."与学号不对应";
					//退出一重循环，即退出excelReader.php
					return $response = [
									'status' => $status,
									'msg' => $msg
							];
				}
				else {
					//查找该学生所参加的各项活动
					$status = 1;
					$msg = '';
					//每行所对应的studentId
					for ($col = 3; $col<count($rowData); $col++){
						//分数不为0，则加入分数数组
						if($rowData[$col] != null){
					 	//一开始$row=3,各列活动分数
					 	$studentIdArr[$row][] = $studentId;
					 	$addScoreArr[$row][] = $rowData[$col];
					 	//活动名称为第二列,分数对应的活动名称
					 	$atyNameArr[$row][] = $data[1][$col];
						}
					}
				}
			}
		}
		return $response = [
				'status' => $status,
				'msg' => $msg,
				'studentIdArr' => $studentIdArr,
				'addScoreArr' => $addScoreArr,
				'atyNameArr' => $atyNameArr
		];
        }
        elseif ($activityId === null){
        	$status = 0;
            $msg = "没有选择某个活动";
            return $response = [
                 			'status' => $status,
                 			'msg' => $msg
                 	];
        	
        }
        //单活动导入
        else  {
        	$data = $objPHPExcel->getActiveSheet()->toArray();
        	$row = -1;
        	//stuData为每一行的内容数组
        	foreach ($data as $rowData){
        		$row ++;
        		//不读取第一行内容
        		if ($row == 0){
        		if ($rowData['3'] == '活动名称'){
					continue;
				}else {
					$status = 0;
					$msg = "表格第一行格式不符合要求";
					return $response = [
							'status' => $status,
							'msg' => $msg
					];
				}
        		}
        		//读取表格第二行的活动名称，col=3,判断要导入的活动是否存在于数据库中
        		//或者读取表格第一个活动
        		if ($row == 1){
        		if (strcmp($rowData['0'], '学号') || strcmp($rowData['1'], '序号') || strcmp($rowData['2'], '姓名')){
        				$status = 0;
        				$msg = "表格第二行格式不符合要求";
        				return $response = [
        						'status' => $status,
        						'msg' => $msg
        				];
        			}
        		 $atyNum = 0;
                 for ($column=3;$column<count($rowData);$column++){
                 	$atyName = $rowData[$column];
                 	if ($atyName !== NULL){
                 		$atyNum++;
                 		if ($atyNum == 1){
                 			$selectColmn = $column;
                 		}
                 		
                 	}
                 }
                 if ($atyNum > 1){
                 	$status = 0;
                 	$msg = "表格不止一个活动，请删除多余的活动";
                 	return $response = [
                 			'status' => $status,
                 			'msg' => $msg
                 	];
                 }elseif ($atyNum == 0){
        				//忽略活动名称的空格
        				$status = 0;
        				$msg = "活动名为空";
        				return $response = [
        								'status' => $status,
        								'msg' => $msg
        						]; 
        				}else {
        					//将多个空格符转换为1个空格符
        					$atyName = preg_replace( "/\s(?=\s)/","\\1", $rowData[$selectColmn]);
        					$atyName = trim($atyName);        					 
        					$activityById = $activityCollection->findOne(array("activityId" => $activityId));
        					if ($activityById['name'] != $atyName){
        						$status = 0;
        						$msg = "$atyName".'活动名错误';
        						return $response = [
        								'status' => $status,
        								'msg' => $msg
        						];
        					}
        				}
        		
        			
        		}
        		//读取表格第三行及第三行以后的内容
        		else {
        			$studentId = (string)$rowData['0'];
        			//如果读取到的studentId为空则结束本次循环，重新开始执行下一次循环
        			if ($studentId == null){
        				continue;
        			}
        			$seatId = $rowData['1'];
        			$studentName = $rowData['2'];
        			//获得学号对应的学生数据
        			$studentByStuId = $studentCollection->findOne(array('studentId' => $studentId));
        			//strcmp字符串相等返回0
        			//判断表格上的姓名是否跟学号对应的姓名是否一样
        			if (strcmp($studentName, $studentByStuId['studentName'])){
        				$status = 0;
        				$msg = "表格".$sheetName."中".$studentName."与学号不对应";
        				return $response = [
        						'status' => $status,
        						'msg' => $msg
        				];
        			}
        			else {
        				//查找该学生所参加的各项活动
        				$status = 1;
        				$msg = '';
                       if ($rowData[$selectColmn] !=null){
        				$studentIdArr[$row][] = $studentId;
        		    	$addScoreArr[$row][] = $rowData[$selectColmn];
        				//活动名称为第二列,分数对应的活动名称
        				$atyNameArr[$row][] = $atyName;
                       }
        			}
        		}
        	}
        	return $response = [
        			'status' => $status,
        			'msg' => $msg,
        			'studentIdArr' => $studentIdArr,
        			'addScoreArr' => $addScoreArr,
        			'atyNameArr' => $atyNameArr
        	];
        
        }
	}
	//单活动批量添加素拓分
	public function addSutuoScoreOne($addSutuoData){
		$studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		$activityModel = new \Think\Model\MongoModel("activity");
		$activityCollection = $studentModel->getCollection();
		$studentIdArr = $addSutuoData['studentIdArr'];
		$addScoreArr = $value['addScoreArr'];
		$atyNameArr = $value['atyNameArr'];
		foreach ($studentIdArr as $equal_k1 => $studentIdArr_row){
			$addScoreArr_row = $addScoreArr[$equal_k1];
			$atyNameArr_row = $atyNameArr[$equal_k1];
			foreach ($studentIdArr_row as $equal_k2 => $studentId){
				$addScore = $addScoreArr_row[$equal_k2];
				$atyName = $atyNameArr_row[$equal_k2];
				$activity = $activityCollection->findOne(array("name" => $atyName));
				$activityId = $activity['activityId'];
				//根据studentId找到该学生
				$where = array('studentId' => $studentId);
				$student = $studentCollection->findOne($where);
				$general = $student['sutuoScore']['general'];
				$humanity = $student['sutuoScore']['humanity'];
				$creative = $student['sutuoScore']['creative'];
				//清空含有该activityId的sutuoItem,全覆盖式导入
				foreach ($student['sutuoItems'] as $sutuoItem){
	
					if ($sutuoItem['addingFrom']['activityId'] == $activityId){
						$delScore = $sutuoItem['score'];
						$delSutuoCode = $sutuoItem['sutuoCode'];
						switch ($delSutuoCode){
							case '0':
								$general = $general - $delScore;
								break;
							case '1':
								$humanity = $humanity - $delScore;
								break;
							case '2':
								$creative = $creative - $delScore;
								break;
							default: break;
						}
						$studentCollection->update($where,array('$pull' => array("sutuoItems" => $sutuoItem)));
					}
				}
				$sutuo = $activity['sutuo'];
				$sutuoCode = $sutuo['sutuoCode'];
				switch ($sutuoCode){
					case '0':
						$general = $addScore + $general;
						break;
					case '1':
						$humanity = $addScore + $humanity;
						break;
					case '2':
						$creative = $addScore + $creative;
						break;
					default: break;
				}
				$sutuoScore = [
						'general' => $general,
						'humanity' => $humanity,
						'creative' => $creative
				];
				$studentCollection->update($where, array('$set' => array("sutuoScore" => $sutuoScore)));
				//活动的类型，判断是普通分还是创新分
				$sutuoItem = [
						'addingTime' =>  date('y/m/d'),
						'score' => $addScore,
						'sutuoCode' => $sutuoCode,
						'optionSource' => [
								//optionSourceCode暂且定为excel文件名称,id暂定为时间
								'optionSourceCode' => $_FILES['file']['name'],
								'id' => date(ymdhis)
						],
						'addingFrom' =>[
								'activityId' => $activityId,
								'addingTypeCode' => 'excel'
								]
						];
						$result = $studentCollection->update($where,array('$push' => array("sutuoItems" => $sutuoItem)));
	
			}
			}
				
			if ($result){
			return 1;
	}else {
	return 0;
	}
	
	
	}
	//多活动批量添加素拓分
	public function addSutuoScore($addSutuoData){
	$studentModel = new \Think\Model\MongoModel("student");
	$studentCollection = $studentModel->getCollection();
	$activityModel = new \Think\Model\MongoModel("activity");
	$activityCollection = $studentModel->getCollection();
	foreach ($addSutuoData as $key => $value){
	$studentIdArr = $value['studentIdArr'];
	$addScoreArr = $value['addScoreArr'];
	$atyNameArr = $value['atyNameArr'];
	foreach ($studentIdArr as $equal_k1 => $studentIdArr_row){
				$addScoreArr_row = $addScoreArr[$equal_k1];
					$atyNameArr_row = $atyNameArr[$equal_k1];
					foreach ($studentIdArr_row as $equal_k2 => $studentId){
					$addScore = $addScoreArr_row[$equal_k2];
					$atyName = $atyNameArr_row[$equal_k2];
					$activity = $activityCollection->findOne(array("name" => $atyName));
					$activityId = $activity['activityId'];
					//根据studentId找到该学生
					$where = array('studentId' => $studentId);
				$student = $studentCollection->findOne($where);
				$general = $student['sutuoScore']['general'];
					$humanity = $student['sutuoScore']['humanity'];
					$creative = $student['sutuoScore']['creative'];
					//清空含有该activityId的sutuoItem,全覆盖式导入
					foreach ($student['sutuoItems'] as $sutuoItem){
	
					if ($sutuoItem['addingFrom']['activityId'] == $activityId){
					$delScore = $sutuoItem['score'];
					$delSutuoCode = $sutuoItem['sutuoCode'];
					switch ($delSutuoCode){
					case '0':
					$general = $general - $delScore;
							break;
							case '1':
							$humanity = $humanity - $delScore;
							break;
							case '2':
							$creative = $creative - $delScore;
							break;
							default: break;
							}
							$studentCollection->update($where,array('$pull' => array("sutuoItems" => $sutuoItem)));
							}
					}
					$sutuo = $activity['sutuo'];
					$sutuoCode = $sutuo['sutuoCode'];
							switch ($sutuoCode){
					case '0':
					$general = $addScore + $general;
					break;
					case '1':
					$humanity = $addScore + $humanity;
							break;
							case '2':
							$creative = $addScore + $creative;
							break;
							default: break;
					}
					$sutuoScore = [
					'general' => $general,
					'humanity' => $humanity,
					'creative' => $creative
					];
					$studentCollection->update($where, array('$set' => array("sutuoScore" => $sutuoScore)));
							//活动的类型，判断是普通分还是创新分
					$sutuoItem = [
							'addingTime' =>  date('y/m/d'),
							'score' => $addScore,
							'sutuoCode' => $sutuoCode,
							'optionSource' => [
									//optionSourceCode暂且定为excel文件名称,id暂定为时间
									'optionSourceCode' => $_FILES['file']['name'],
											'id' => date(ymdhis)
											],
											'addingFrom' =>[
								'activityId' => $activityId,
											'addingTypeCode' => 'excel'
											]
											];
											$result = $studentCollection->update($where,array('$push' => array("sutuoItems" => $sutuoItem)));
	
	}
	}
	}
	if ($result){
	return 1;
	}else {
				return 0;
	}
		
		
	}
	//导出班级学生数据
	public function classExport($classId){
		$studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		$classModel = new \Think\Model\MongoModel("class");
		$classCollection = $classModel->getCollection();
		$activityModel = new \Think\Model\MongoModel("activity");
		$activityColletion = $activityModel->getCollection();
		//引入PHPExcel类库
		Vendor('PHPExcel.PHPExcel');
		$activity = $activityColletion->find()->sort(array("holdTime" => -1));
		//定义6个内置表的初始活动数目
		$aty_key0=$aty_key1=$aty_key2=$aty_key3=$aty_key4=$aty_key5=-1;
		foreach ($activity as $aty_k => $aty_v){
			$sutuoName = $aty_v['sutuo']['sutuoName'];
			$sutuoType = $aty_v['sutuoType'];
			switch ($sutuoType){
				case '文体艺术与身心发展':
					if ($sutuoName == '普通分'){
					//计算活动数目
				    $aty_key0++;
					//获取列数并且将它变为字母，范围C-ZZ
					$atyIndex0[] = $this->getActivityCell($aty_key0);
					//该数组储存文体艺术与身心发展的所有活动名称
					$atyName0[] = $aty_v['name'];
					}
					break;
				case '思想政治与道德素养':
					if ($sutuoName == '普通分'){
					$aty_key1++;
					$atyIndex1[] = $this->getActivityCell($aty_key1);
					$atyName1[] = $aty_v['name'];
			        
					}
					break;
				case '科学技术与创新创业':
					if ($sutuoName == '普通分'){
					$aty_key2++;
					$atyIndex2[] = $this->getActivityCell($aty_key2);
					$atyName2[] = $aty_v['name'];
					}	
					break;
				case '社会实践与志愿服务':
					if ($sutuoName == '普通分'){
					$aty_key3++;
					$atyIndex3[] = $this->getActivityCell($aty_key3);
					$atyName3[] = $aty_v['name'];
					}
					break;	
				default:
			}
			switch ($sutuoName){
				case '人文素质教育学分':
					$aty_key4++;
					$atyIndex4[] = $this->getActivityCell($aty_key4);
					$atyName4[] = $aty_v['name'];
					break;
				case '创新能力培养学分':
					$aty_key5++;
					$atyIndex5[] = $this->getActivityCell($aty_key5);
					$atyName5[] = $aty_v['name'];
					break;
				default:
			}
		}
		//进入班级数据库
		$classData = $classCollection->findOne(array('classId' => $classId));
		$students = $classData['students'];
		//定义参加六类活动的学生的seatId[]键值,顺序为文，思，科，社，人，创
		$s_key0=$s_key1=$s_key2=$s_key3=$s_key4=$s_key5=-1;
		foreach ($students as $stu_k => $stu_v){
			$studentId = $stu_v['studentId'];
			$seatId = $stu_v['seatId'];
			$seatIdArr[] = $seatId;
			//进入学生数据库
			$student = $studentCollection->findOne(array('studentId' => (string)$studentId));
			//记录该班级所有学生姓名
			$stuNameArr[] = $student['studentName'];  
			$sutuoItems = $student['sutuoItems'];
			foreach ($sutuoItems as $sutuo_k => $sutuo_v){
				$activityId = $sutuo_v['addingFrom']['activityId'];
				$activity = $activityColletion->findOne(array('activityId' => $activityId));
				$stuAtyName = $activity['name'];
				$stuAtyScore = $sutuo_v['score'];
				//array_search()函数在一个数组中搜索一个指定的值，如果找到则返回相应的键，否则返回false。
				$match_atyName0_k = array_search($stuAtyName, $atyName0);
				$match_atyName1_k = array_search($stuAtyName, $atyName1);
				$match_atyName2_k = array_search($stuAtyName, $atyName2);
				$match_atyName3_k = array_search($stuAtyName, $atyName3);
				$match_atyName4_k = array_search($stuAtyName, $atyName4);
				$match_atyName5_k = array_search($stuAtyName, $atyName5);
				$activity2 = $activityColletion->findOne(array('activityId' => 7));
				if ($match_atyName0_k !== false){
					$s_key0++;
					$match_seatId0[$match_atyName0_k][] = $seatId;
					$match_score0[$match_atyName0_k][] = $stuAtyScore;
				}
				if($match_atyName1_k !== false) {
					$s_key1++;
					$match_seatId1[$match_atyName1_k][] = $seatId;
					$match_score1[$match_atyName1_k][] = $stuAtyScore;
				}
				if ($match_atyName2_k !== false){
					$s_key2++;
					$match_seatId2[$match_atyName2_k][] = $seatId;
					$match_score2[$match_atyName2_k][] = $stuAtyScore;
				}
				if ($match_atyName3_k !== false){
					$s_key3++;
					$match_seatId3[$match_atyName3_k][] = $seatId;
					$match_score3[$match_atyName3_k][] = $stuAtyScore;
				}
				if ($match_atyName4_k !== false){
					$s_key4++;
					$match_seatId4[$match_atyName4_k][] = $seatId;
					$match_score4[$match_atyName4_k][] = $stuAtyScore;
				}
				if ($match_atyName5_k !== false){
					$s_key5++;
					$match_seatId5[$match_atyName5_k][] = $seatId;
					$match_score5[$match_atyName5_k][] = $stuAtyScore;
				}
			}
		}
		//对数组进行递增排序并保持索引关系
		asort($seatIdArr);
		//实例化PHPEecel类，相当于新建一个excel文件
		$objPHPExcel = new \PHPExcel();
		//生成6个内置表
		for ($i=0;$i<6;$i++){
			//创建新的内置表，默认自动生成1个
			if ($i){
				$objPHPExcel->createSheet();
			}
			$objPHPExcel->setActiveSheetIndex($i); 			   //把新创建的sheet设置为当前操作的sheet
			$objSheet = $objPHPExcel->getActiveSheet();		   //获取当前操作的sheet
			//文字居中
			$objSheet->getDefaultStyle()->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER)
			->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			//设置字体
			$objSheet->getDefaultStyle()->getFont()->setName('宋体')->setSize("11");
			$objSheet->setCellValue("A2","序号")->setCellValue("B2","姓名");
			foreach ($seatIdArr as $s_key => $seatId){
				$objSheet->setCellValue("A".($seatId+2),$seatId)->setCellValue("B".($seatId+2),$stuNameArr[$s_key]);
			}
			switch ($i){
				case '0':
					$objSheet->setTitle('文体艺术与身心发展'); 
					if ($aty_key0!=-1){
					$last = $atyIndex0[$aty_key0];
					$objSheet->setCellValue("C1","活动名称");
					$objSheet->mergeCells("C1:".$last."1");
					for ($j=0;$j<=$aty_key0;$j++){
						//设置活动所在列自动换行
						$objSheet->getStyle($atyIndex0[$j])->getAlignment()->setWrapText(true);
						//导出活动名称
						$objSheet->setCellValue($atyIndex0[$j]."2",$atyName0[$j]."\n");
                     foreach ($match_seatId0[$j] as $match_seatId_k => $match_seatId){
						//导出参加活动的学生的活动分数
						$objSheet->setCellValue($atyIndex0[$j].($match_seatId+2),$match_score0[$j][$match_seatId_k]);
					}
				}
					}
				break;
				case '1':
					$objSheet->setTitle('思想政治与道德素养');
					if ($aty_key1!=-1){
					$last = $atyIndex1[$aty_key1];
					$objSheet->setCellValue("C1","活动名称");
					$objSheet->mergeCells("C1:".$last."1");
					for ($j=0;$j<=$aty_key1;$j++){
						$objSheet->getStyle($atyIndex1[$j])->getAlignment()->setWrapText(true);
						$objSheet->setCellValue($atyIndex1[$j]."2",$atyName1[$j]."\n");
						foreach ($match_seatId1[$j] as $match_seatId_k => $match_seatId){
						//导出参加活动的学生的活动分数
						$objSheet->setCellValue($atyIndex1[$j].($match_seatId+2),$match_score1[$j][$match_seatId_k]);
					}
					}
					}
				break;
				case '2':
					$objSheet->setTitle('科学技术与创新创业');	
					if ($aty_key2!=-1){
					$last = $atyIndex2[$aty_key2];
					$objSheet->setCellValue("C1","活动名称");
					$objSheet->mergeCells("C1:".$last."1");
					for ($j=0;$j<=$aty_key2;$j++){
						$objSheet->getStyle($atyIndex2[$j])->getAlignment()->setWrapText(true);
						$objSheet->setCellValue($atyIndex2[$j]."2",$atyName2[$j]."\n");
						foreach ($match_seatId2[$j] as $match_seatId_k => $match_seatId){
						//导出参加活动的学生的活动分数
						$objSheet->setCellValue($atyIndex2[$j].($match_seatId+2),$match_score2[$j][$match_seatId_k]);
					}
					}	
					}
				break;
				case '3':
					$objSheet->setTitle('社会实践与志愿服务');
					if ($aty_key3!=-1){
 					$last = $atyIndex3[$aty_key3];
					$objSheet->setCellValue("C1","活动名称");
 					$objSheet->mergeCells("C1:".$last."1");
					for ($j=0;$j<=$aty_key3;$j++){
						$objSheet->getStyle($atyIndex3[$j])->getAlignment()->setWrapText(true);
						$objSheet->setCellValue($atyIndex3[$j]."2",$atyName3[$j]."\n");
						foreach ($match_seatId3[$j] as $match_seatId_k => $match_seatId){
						//导出参加活动的学生的活动分数
						$objSheet->setCellValue($atyIndex3[$j].($match_seatId+2),$match_score3[$j][$match_seatId_k]);
					}
					}
					}
				break;
				case '4':
					$objSheet->setTitle('人文');
					if ($aty_key4!=-1){
					$last = $atyIndex4[$aty_key4];
					$objSheet->setCellValue("C1","活动名称");
					$objSheet->mergeCells("C1:".$last."1");
					for ($j=0;$j<=$aty_key4;$j++){
						$objSheet->getStyle($atyIndex4[$j])->getAlignment()->setWrapText(true);
						$objSheet->setCellValue($atyIndex4[$j]."2",$atyName4[$j]."\n");
						foreach ($match_seatId4[$j] as $match_seatId_k => $match_seatId){
						//导出参加活动的学生的活动分数
						$objSheet->setCellValue($atyIndex4[$j].($match_seatId+2),$match_score4[$j][$match_seatId_k]);
					}
					}
					}
				break;
				case '5':
					$objSheet->setTitle('创新');
					if ($aty_key5!=-1){
					$last = $atyIndex5[$aty_key5];
					$objSheet->setCellValue("C1","活动名称");
					$objSheet->mergeCells("C1:".$last."1");
					for ($j=0;$j<=$aty_key5;$j++){
						$objSheet->getStyle($atyIndex5[$j])->getAlignment()->setWrapText(true);
						$objSheet->setCellValue($atyIndex5[$j]."2",$atyName5[$j]."\n");
						foreach ($match_seatId5[$j] as $match_seatId_k => $match_seatId){
						//导出参加活动的学生的活动分数
						$objSheet->setCellValue($atyIndex5[$j].($match_seatId+2),$match_score5[$j][$match_seatId_k]);
					}
					}
					}
				break;
			}
 		} //生成6个内置表结束
		//生成excel文件
		$filename = date(ymdhis)."素拓数据导出";
		$filename2 = iconv("utf-8","gb2312",$filename);
		$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
 		$objWriter->save("./Public/exportExcel/".$filename2.".xls");
 		require('FileDownload.class.php');
 		$file = "./Public/exportExcel/$filename2.xls";
 		$obj = new \FileDownload();
 		//$flag = $obj->download($file, '');
 		$flag = $obj->download($file, $filename, true); // 断点续传
 		if(!$flag){
 			echo 'file not exists';
 		}
		//告诉浏览器将要输出Excel03
// 		header('Content-Type: application/vnd.ms-excel');
// 		告诉浏览器将要输出文件的名称
// 		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
// 		禁止缓存
// 		header('Cache-Control: max-age=0');
// 		$objWriter->save('php://output');
//  		if ($classId!=null){
//  			 $response =[
//  			 		'status' => 1,
//  			 		'msg' => '导出成功',
//  			 		'objWriter' => $objWriter,
//  			 		'filename' => $filename
//  			 ];
//  		}else {
//  			$response = [
//  					'status' => 0,
//  					'msg' => 'classId为空'
//  			];
//  		}
//  		return json_encode($response,JSON_UNESCAPED_UNICODE);
 		
	}	
public function classExport2($fileName){
	 		require('FileDownload.class.php');
	 		$file = "./Public/exportExcel/$fileName.xls";
	 		$obj = new \FileDownload();
	 		//$flag = $obj->download($file, '');
	 		$flag = $obj->download($file, $name, true); // 断点续传
	 		if(!$flag){
	 			echo 'file not exists';
	 		}
}	
public function getAtyExcel(){
	$excelModel = new \Think\Model\MongoModel("excel");
	$excelCollection = $excelModel->getCollection();
	$allAtyExcelData = $excelCollection->find()->sort(array("adddingTime" => -1));
	foreach ($allAtyExcelData as $atyExcelData){
		$excelData[] = $atyExcelData;
	}
	return json_encode($excelData,JSON_UNESCAPED_UNICODE);
	
}	
public  function getActivityCell($key){
	//excel列数为A-Z,AA-AZ,BA-BZ···
	$row = $this->getcolumnrange('C', 'ZZ');
	return $row[$key];   	
}
public function getcolumnrange($min,$max){
	$pointer=strtoupper($min);
	$output=array();
	while($this->positionalcomparison($pointer,strtoupper($max))<=0){
		array_push($output,$pointer);
		$pointer++;
	}
	return $output;
}
public function positionalcomparison($a,$b){
	$a1=$this->stringtointvalue($a); $b1=self::stringtointvalue($b);
	if($a1>$b1)return 1;
	else if($a1<$b1)return -1;
	else return 0;
}
/*
 * e.g. A=1 - B=2 - Z=26 - AA=27 - CZ=104 - DA=105 - ZZ=702 - AAA=703
 */
public function stringtointvalue($str){
	$amount=0;
	$strarra=array_reverse(str_split($str));

	for($i=0;$i<strlen($str);$i++){
		$amount+=(ord($strarra[$i])-64)*pow(26,$i);
	}
	return $amount;
}
	}
?>