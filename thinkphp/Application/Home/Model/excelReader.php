<?php 
use Org\Util\String;
//$objReader->setLoadSheetsOnly($sheetName);
//将表格数据放入数组$data
$data = $objPHPExcel->getSheet($key)->toArray();
//var_dump($data);
$a = -1;
foreach ($data as $stuData){
$a ++;
//不读取第一行内容
 if($a > 0){
//读取表格第二行的活动名称
if ($a == 1){ 
for ($i = 3; $i < count($stuData); $i++){
$atyName = $stuData[$i];
$activity = $activityCollection->findOne(array("name" => $atyName));
	if ($activity){
	//查找活动名称对应的activityId
	$activityId = $activity['activityId'];
	//活动的类型，判断是普通分还是创新分		
	}else {
	//记录错误的活动名称
	$errAtyName[] = $atyName; 
	//var_dump($errAtyName);
	//记录错误的活动名称所在的列，防止后面的操作导入该活动
	$errNum[] = $i;
	//var_dump($errNum);
	}
				 }
			 }
			 //读取表格其他列的内容
else {
$studentId = (string)$stuData['0'];
$seatId = $stuData['1'];
$studentName = $stuData['2'];
//var_dump($studentName);
//获得学号对应的学生数据
$student = $studentCollection->findOne(array('studentId' => $studentId));
//获得姓名对应的学生数据
$student2 = $studentCollection->findOne(array('studentName' => $studentName));
//记录错误的studentId
	if (!$student){
		//注意不记录数值为null的studentId.
		//因为这里存在bug，即使excel表格学号都正确也会读入数值为null的studentId
		//所以当studentId为null时，不计入errStudentId
	if ($studentId!=null){
	$errStudentId[] = $studentId;
	//studentId错误，studentName也错误
	if (!$student2){
		$errStudentName[] = $studentName;
		//var_dump($errStudentName);
	}
		}
		
	}
	//studentId正确，但studentName错误
	else if (!$student2){
		$errStudentName[] = $studentName;
	}
	//如果是正确的studentId和studentName
	else
	{
		//判断学号与姓名是否对应,规定两者不对应的话返回的是studentName错误。
		if (strcmp($studentName, $student['studentName'])){
			$errStudentName[] = $studentName;
		}
		else {
		//判断表格上的姓名是否跟学号对应的姓名一样
		//strcmp字符串相等返回0
	    //查找该学生所参加的各项活动
			$student = $studentCollection->findOne(array('studentId' => $studentId));
			$general = $student['sutuoScore']['general'];
			$humanity = $student['sutuoScore']['humanity'];
			$creative = $student['sutuoScore']['creative'];
			for ($i = 3; $i<count($stuData); $i++){
				//注意刷新$student,防止下面分数导入时总分未能刷新
				//修改	$student = $studentCollection->findOne(array('studentId' => $studentId));
			 	if($stuData[$i] != null){
			    //找到相同列上的活动名称,但不能读到已检测到的错误的活动名称
			    if (!in_array($i, $errNum)){
			 	$score = $stuData[$i];
			 	//活动名称为第二列
			 	$atyName = $data[1][$i];
			 	//根据活动名称进入该活动的数据库
			 	$activity = $activityCollection->findOne(array("name" => $atyName));
			 	$activityId = $activity['activityId'];
			 	//根据studentId找到该学生
			 	$where = array('studentId' => $studentId);
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
			    		$general = $score + $general;
			 			break;
			 		case '1':
			 			$humanity = $score + $humanity;
			 			break;
			 		case '2':
			 			$creative = $score + $creative;
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
			 	     				'addingTime' =>  date('y-m-d'),
			 	     				'score' => $score,
			 	     				'sutuoCode' => $sutuoCode,
			 	     				'optionSource' => [
			 	     				     //optionSourceCode暂且定为excel文件名称,id暂定为时间
			 	     					'optionSourceCode' => $_FILES['file']['name'],
			 	     					'id' => date(ymdhis)
			 	     						],
			 	     				'addingFrom' =>[
			 	     						'activityId' => $activityId,
			 	     						'addingTypeCode' => $fileType,
			 	     						]
			 	     				];
			 	 $result = $studentCollection->update($where,array('$push' => array("sutuoItems" => $sutuoItem)));
			 	 if ($result){
			 	 	$status = 1;
			 	 	$msg = '';
			 	 }else {
			 	 	$status = 0;
			 	 	$msg = '文件格式正确，但是数据格式不正确，导入失败';
			 	 }
			 			}	
			 	}
			 			}
			 				}
	}
			 			}
}
}
			?>