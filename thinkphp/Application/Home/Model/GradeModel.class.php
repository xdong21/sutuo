<?php
namespace Home\Model;
use Think\Model\MongoModel;
header("Content-type: text/html; charset=utf-8");
class GradeModel extends MongoModel{
	//创建年级
	public function create($year){
		//$year = json_decode($year);
		$gradeModel = new \Think\Model\MongoModel("grade");
		$gradeCollection = $gradeModel->getCollection();
//  		$maxGrade = $gradeCollection->find()->sort(array("gradeId" => -1))->limit(1);
//  		$maxGradeId = $maxGrade->gradeId+1;
		$year = (int)$year;
		$grade = $gradeCollection->findOne(array("year" => $year));
		$yearMethod = $grade['yearMethod'];
		$classes = array();
		$gradeData = [
		// 				"gradeId" => $maxGradeId,
				"year" => $year,
				"yearMethod" => 2,
				"classes" => $classes
		];
		if ($grade == null){
		    $status = 1;
		    $msg = '创建成功';
			$gradeCollection->insert($gradeData);
			$response = [
					"status" => $status,
					"msg" => $msg
			];
			return $response;
		}elseif ($yearMethod !== 0){
			$status = 0;
			$msg = "已存在".$year."级";
		}else{
				$gradeCollection->update(array("year" => $year),array('$set' => array("yearMethod" => 2)));
				$status = 1;
				$msg = '创建成功';
			}
		$response = [
				"status" => $status,
				"msg" => $msg
		];
		return $response;
	}
	//读取年级
public function read(){
		$gradeModel = new \Think\Model\MongoModel("grade");
		$gradeCollection = $gradeModel->getCollection();
		$classModel = new \Think\Model\MongoModel("class");
		$classCollection = $classModel->getCollection();
		//get到的year在测试时是string，所以转化为int
// 		$year = (int)$year;
	
// 		foreach ($gradeDataCursor as $gradeData2){
// 			//本处不能进行操作，无操作，将cursor转换为数组形式
// 			break;
// 		}		
		$gradeData2 = $gradeCollection->findOne(array("yearMethod" => 2));
		//如果数据库为空，返回空数组
		if ($gradeData2 == null){
		$classData2[] = [
					"className" => null,
					"classId" => null,
					"studentCount" => null,
					"classMethod" => 0
			];
		$allData[] = [
// 				"gradeId" => null,
				"year" => null,
				"yearMethod" => 0, 
				"classes" => $classData2
		];
		$allData = json_encode($allData,JSON_UNESCAPED_UNICODE);
		return $allData;
		}
		$gradeDataCursor = $gradeCollection->find(array("yearMethod" => 2))->sort(array("year" => 1));
		foreach ($gradeDataCursor as $gradeData){
			$year = $gradeData['year'];
			$classes = $gradeData['classes'];
			//如果该年级里面没有班级
			if ($classes == array()){
				$classData2[] = [
						"className" => null,
						"classId" => null,
						"studentCount" => null,
						"classMethod" => 0
				];
				$allData[] = [
				// 				"gradeId" => $gradeId,
						"year" => $year,
						"yearMethod" => 2,
						"classes" => $classData2
				];
				$classData2 = null;
				//执行下一个年级
				continue;
			}
// 			$gradeId = $gradeData['gradeId'];
			foreach ($classes as $classKey => $classId){
			$classData = $classCollection->findOne(array("classId" => $classId,"classMethod" => 2));				
			//如果该年级没有classMethod为2的班级
			if ($classData == null){
				$classData2[] = [
						"className" => null,
						"classId" => null,
						"studentCount" => null,
						"classMethod" => 0
				];
				//执行下一个班级
				continue;
			}else{
		    $classData2[] = [
		    		"className" =>  $classData['className'],
		    		"classId" => $classId,
		    		"studentCount" => $classData['studentCount'],
		    		"classMethod" => 2
		    ];
		    }
		}
		$allData[] = [
// 				"gradeId" => $gradeId,
				"year" => $year,
				"yearMethod" => 2, 
				"classes" => $classData2
		];
		$classData2 = null;
		}
		$allData = json_encode($allData,JSON_UNESCAPED_UNICODE);
		return $allData;
		
	}
	//添加班级Id
	public function addClassId($year){
		$gradeModel = new \Think\Model\MongoModel("grade");
		$gradeCollection = $gradeModel->getCollection();
		//查询条件为年级year
		$where = array("year" => $year);
		$arr = $gradeCollection->findOne($where);
 		//现有班级数量
 		$count = count($arr['classes']);
		//定义新的classId
		$arr['classes'][$count] = (string)($year.(str_pad($count,2,0,STR_PAD_LEFT))+1);
		//var_dump($arr['classes'][$count]);
		//添加年级新的classId到数组中去
		$result = $gradeCollection->update($where,array('$push'=>array("classes" => $arr['classes'][$count])));
		//返回新创建班级的classId
		return $arr['classes'][$count];
	}
	
	//删除年级
	public function delete($year){
		$gradeModel = new \Think\Model\MongoModel("grade");
		$gradeCollection = $gradeModel->getCollection();
		$where = array("year" => $year);
// 		$result = $gradeCollection->remove($where);
        $result = $gradeCollection->update($where,array('$set' => array("yearMethod" => 0)));
		if ($result){
			$status = 1;
			$msg = '';
		}else {
			$status = 0;
			$msg= '删除'.$year.'级失败';
		}
		$response = [
				'status' => $status,
				'msg' => $msg
		];
		return $response;
	}
}