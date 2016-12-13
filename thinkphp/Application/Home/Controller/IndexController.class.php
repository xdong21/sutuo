<?php

namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
    public function index(){
       // echo U('Home/Activity/insertActivity');
    		/*$a=C('url_model',null,'default_config');
    		 echo $a;*/
    		//echo U('Home/Activity/index');
    		/*$data['name'] = 'xiaoming';
    		$data['age'] = '20';
    		$data['sex'] = 'man';
    		$Model = new \Think\Model\MongoModel("class");
    		$Collection = $Model->getCollection();
    		$Collection->insert($data);
    		echo json_encode($data);
    		*/
    	}
    	public function index2(){
    		$data['name'] = 'xiaoming';
    		$data['age'] = '20';
    		$data['sex'] = 'man';
    		echo $this->ajaxReturn($data);
    	}
    	public function  classCollection(){
    		$grade['year'] = 2014;
    		$grade['classCollection'] = [
    				classId => 1,
    				className => '信工1班',
    				majorName => '信息工程',
    				studentCount => 40,
    				students =>[
    						name => '小明',
    						studentId => 201412345678,
    						seatId => 22
    				]
    		];
    		$Model = new \Think\Model\MongoModel("class");
    	$Collection = $Model->getCollection();
        	$Collection->insert($grade);
        	echo json_encode($grade);
        	/*$class['classId'] = '1';
    		$class['className'] = '信工1班';
    		$class['majorName'] = '信息工程';
    		$class['studentCount'] = '40';
    		$class['students'] = [
    		name => '小明',
    		studentId => 201400000000,
    		seatId => 1
    		];*/
    		}
    		public function  studentCollection(){
    		$student['studentId'] = '201412345678';
    		$student['classId'] = '1';
    	$student['studentName'] = '小明';
        	$student['sutuoAddingItems'] = array();
        			$Model = new \Think\Model\MongoModel("student");
    		$Collection = $Model->getCollection();
    	$Collection->insert($student);
        	echo json_encode($student);
    		}
    		public function sutuoAddingItemCollection(){
    		$sutuo['addingTime'] = Date('Y-m-d H:i:s');
    		$sutuo['optionSource'] = [
    				optionSourceCode => '',
    				id => ''
    		];
    		$sutuo['addingFrom'] = [
    				activityId => '',
    				addingTypeCode => ''
    						];
    						$Model = new \Think\Model\MongoModel('sutuoAddingItemn');
    						$Collection = $Model->getCollection();
    						$Collection->insert($sutuo);
    								echo json_encode($sutuo);
    		}
    
    		}
    /*public function dataReturn(){
    	$this->ajaxReturn(getTestData(),'json');
    }*/


/*function getTestData(){
		$data = [
			'name' => 1
		];
		return $data;
}*/
