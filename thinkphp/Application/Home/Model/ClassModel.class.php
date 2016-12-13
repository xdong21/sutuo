<?php
namespace Home\Model;
use Think\Model\MongoModel;
header("Content-type: text/html; charset=utf-8");
class ClassModel extends MongoModel{
	//创建班级
	public  function create($classData){
			$classModel = new \Think\Model\MongoModel("class");
			$classCollection = $classModel->getCollection();
			$gradeModel = new \Home\Model\GradeModel();
			$year = $classData['year'];			
			$className = $classData['className'];
			$classId = $gradeModel->addClassId($year);
			$class = [
					'classId' => $classId, 
					'className' => $className,
					'studentCount' => 0,
					'classMethod' => 2,
					'students' =>[
							/*
							 '0' => [
							'studentId' => 0,
							'seatId' => 0]
							*/
								]
			];
			$result = $classCollection->insert($class);
			if ($result){
				$status = 1;
				$msg = '';
			}else {
				$status = 0;
				$msg = "新增".$className."失败";
			}
			$response = [
					"status" => $status,
					"msg" => $msg
			];
			return $response;
		}
	//读取班级	
//     public function read($classId){
// 	   		$classModel = new \Think\Model\MongoModel("class");
// 			$classCollection = $classModel->getCollection();
// 			$classId = (int)$classId;
// 		 	$cursor = $classCollection->find(array("classId"=>$classId));//findone不用遍历
// 		 	//var_dump($cursor);
// 		 	foreach ($cursor as $classData){
		 		
// 		 	} 
// 		 	//var_dump($classData);
// 		 	return json_encode($classData);
// 	   }
	//修改班级信息
	public function update($postData){
		    $classModel = new \Think\Model\MongoModel("class");
			$classCollection = $classModel->getCollection();
			$gradeModel = new \Home\Model\GradeModel();
			$gradeCollection = (new \Think\Model\MongoModel("grade"))->getCollection();
			$userModel = new \Home\Model\UserModel();
			$dataObj1 = json_decode($postData);
			$dataObj2 = json_decode($postData);
			foreach ($dataObj1 as $key=>$data){
				$password = $data->password;
				break;
			}
		    $confirm = $userModel->confirmPsw($password);
		    if ($confirm['confirm'] == 1){
			foreach ($dataObj2 as $key=>$data){
				$yearMethod = $data->yearMethod;
				$year = (int)$data->year;
				$classes = $data->classes;
				switch ($yearMethod){
					//删除
					case '0':
						$response = $gradeModel->delete($year);
						if (!$response['status']){
								$response = [
										'confirm' =>  1,
										'status' => $response['status'],
										'msg' => $response['msg']
										
								];
							return json_encode($response,JSON_UNESCAPED_UNICODE);
						}
						break;
					//新增	
					case '1':
						$response = $gradeModel->create($year);
						if (!$response['status']){
								$response = [
										'confirm' =>  1,
										'status' => $response['status'],
										'msg' => $response['msg']
										
								];
							return json_encode($response,JSON_UNESCAPED_UNICODE);
						}
						break;
					//不变	
					case '2':
						break;
					//修改	
					case '3':
						$gradeId = $data->gradeId;
 						$response = $gradeCollection->update(array("gradeId" => $gradeId),array('$set' => array("year" => $year)));
					    break;
 					default:break;
				}
				foreach ($classes as $class){
					if ($yearMethod == 0){
						$classMethod = 0;	
					}else {
					$classMethod = $class->classMethod;
					}
					switch ($classMethod){
						//删除
						case '0':
							$classId = $class->classId;
							$response = $this->delete($classId);
							if (!$response['status']){
									$response = [
										'confirm' =>  1,
										'status' => $response['status'],
										'msg' => $response['msg']
										
								];
								return json_encode($response,JSON_UNESCAPED_UNICODE);
							}
							break;
						//新增	
						case '1':
							$className = $class->className;
							$classData = [
								"className" => $className,
								"year" => $year
							];
							$response = $this->create($classData);
							if (!$response['status']){
								$response = [
										'confirm' =>  1,
										'status' => $response['status'],
										'msg' => $response['msg']
										
								];
								return json_encode($response,JSON_UNESCAPED_UNICODE);
							}
							break;
						//不变	
						 case '2':
							break;
						//覆盖	
						 case '3':
						 	$classId = $class->classId;
						 	$className = $class->className;
						 	$classData = [
						 			"className" => $className,
						 			"year" => $year
						 	];
						 	$classExist = $classCollection->findOne(array("classId" => $classId));
						 	if ($classExist){
						 		$result = $classCollection->update(array("classId" => $classId),array('$set' => array("className" => $className,"classMethod" => 2)));
						 		if (!$result){
						 			$response = [
						 					'confirm' =>  1,
						 					'status' => 0,
						 					'msg' => '修改失败'
						 		
						 			];
						 			return json_encode($response,JSON_UNESCAPED_UNICODE);
						 		}
						 	}else {
						 	$response = $this->create($classData);
							if (!$response['status']){
								$response = [
										'confirm' =>  1,
										'status' => $response['status'],
										'msg' => $response['msg']
										
								];
								return json_encode($response,JSON_UNESCAPED_UNICODE);
							}
						 	}
						 	break;	
						default:break;
						}
				}
			}
			$status = 1;
			$msg = '修改成功';
		    }
		
			$response = [
					'status' => $status,
					'msg' => $msg,
					'confirm' => $confirm['confirm']
			];
			return json_encode($response,JSON_UNESCAPED_UNICODE);
		}	
		//删除班级
		public function delete($classId){
			$classModel = new \Think\Model\MongoModel("class");
			$classCollection = $classModel->getCollection();
			$where = array("classId" =>$classId);
			//班级数据库中删除classId对应的班级，但是依旧保留年级数据库中的classId
			//因为假如在年级数据库删除classId的话，会影响到addClassId
 			//$result = $classCollection->remove($where);
            $result = $classCollection->update($where,array('$set' => array("classMethod" => 0)));
            $class=  $classCollection->findOne($where);
            $className = $class['className'];
			if ($result){
				 $status = true;
				 $msg = '';
			}else{
				 $status = false;
				 $msg = "删除".$className."失败";
			}
			$response = [
					'status' => $status,
					'msg' => $msg
			];
			return $response;
		}	
	}
?>