<?php
	namespace Home\Model;
	use Think\Model\MongoModel;

	header("Content-type: text/html; charset=utf-8");
	class ActivityModel extends MongoModel{

		public static function insert($atyData){
			//选择哪个数据库在配置中设置
			$activityModel = new \Think\Model\MongoModel("activity");
			$atyCollection = $activityModel->getCollection();
			//括号内为collection名，全名加配置中的前缀
			//这两个语句创建名为sutuo_activity的集合,存在则不创建

			$atyData=json_decode($atyData);
//!!!!!!!!!!此处的$atyData是对象，返回其成员时用例如$atyData->name
			$activityIdData = $atyCollection->find()->sort(array('activityId' => -1))->limit(1);
			foreach ($activityIdData as $value) {
				$i = $value['activityId']+1;
				break;
			}
			$atyData->activityId = $i;
			$sutuoName = $atyData->sutuo->sutuoName;
			switch ($sutuoName){
				case '普通分': $sutuoCode = 0;
				break;
				case '人文素质教育学分': $sutuoCode = 1;
				break;
				case '创新能力培养学分': $sutuoCode = 2;
				break;
			}
			$atyData->sutuo->sutuoCode = $sutuoCode;
			//将多个空格符转换为1个空格符
			$atyData->name = preg_replace( "/\s(?=\s)/","\\1", $atyData->name);
			$atyData->name = trim($atyData->name);        					 
			$atyCollection->insert($atyData);
			//返回activityId
			return $i;
		}


		//获取全部活动详细信息
		public static function getAll(){
			$activityModel = new \Think\Model\MongoModel("activity");
			$atyCollection = $activityModel->getCollection();
			$allAtyData = $atyCollection->find()->sort(array("holdTime" => -1));
			return $allAtyData;
		}


		//获取某一个活动的详细信息
		public static function get($activityId){
			$activityModel = new \Think\Model\MongoModel("activity");
			$atyCollection = $activityModel->getCollection();
			//get
			//此处的id是string，需要转换成int
			$id = (int)$activityId;
			//注意此处无法直接用变量查询，必须单个变量转换为如上数组形式
			$atyData = $atyCollection->findOne(array('activityId' => $id));
			return $atyData;
		}


		//更新活动信息
		public static function update($atyData){
			$activityModel = new \Think\Model\MongoModel("activity");
			$atyCollection = $activityModel->getCollection();
			//post
			$atyData = json_decode($atyData);
			$newData = $atyCollection->findOne(array('activityId' => $atyData->activityId));
			//将多个空格符转换为1个空格符
			$newData["name"] = preg_replace( "/\s(?=\s)/","\\1", $atyData->name);;
			$newData["holder"] = $atyData->holder; 	
			$newData["holdTime"] = $atyData->holdTime;
			switch ($sutuoName){
				case '普通分': $sutuoCode = 0;
				break;
				case '人文素质教育学分': $sutuoCode = 1;
				break;
				case '创新能力培养学分': $sutuoCode = 2;
			}
            $newData['sutuoType'] = $atyData->sutuoType;
			 			$newData['sutuo']=[
			 			"sutuoName" => $atyData->sutuo->sutuoName,     
						"sutuoCode" => $sutuoCode,     
						"score" => $atyData->sutuo->score
			 		];
			$atyCollection->update(
				array("activityId" => $newData['activityId']),
				$newData
			);
			return true;
		}
	}
?>