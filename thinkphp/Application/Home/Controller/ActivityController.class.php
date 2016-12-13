<?php
#
# ****************  素拓系统后台 活动部分 *****************
#
namespace Home\Controller;
use Think\Controller;

header("Content-type: text/html; charset=utf-8");  
class ActivityController extends Controller {
    public function index(){
    }
    

    //创建活动
    public function insertActivity(){
        $userModel = new \Home\Model\UserModel();
        $returnData = $userModel->confirmUser();
        if ($returnData['islogin'] == 0){
            echo json_encode($returnData ,JSON_UNESCAPED_UNICODE);
            exit;
        }
        //获取post数据
        $atyData=file_get_contents("php://input");
        $aty = new \Home\Model\ActivityModel();
        //创建活动同时返回activityId
        $activityId=$aty->insert($atyData);
        //响应中的数据
        $response=[
            'status' => 1,
            'activityId' => $activityId
        ];
        $returnData = array_merge($returnData, $response);
        echo json_encode($returnData,JSON_UNESCAPED_UNICODE);
    }


    //查看所有活动概况
    public function getAllActivity(){
    	$userModel = new \Home\Model\UserModel();
     	$returnData = $userModel->confirmUser();
        if ($returnData['islogin'] == 0){
            echo json_encode($returnData ,JSON_UNESCAPED_UNICODE);
            exit;
        }
        $aty = new \Home\Model\ActivityModel();
        $i=0;
        foreach ($aty->getAll() as $atyData) {
            $allAtyData[$i] = $atyData; 
            $i++;
        }
        $returnData = array_merge($returnData, $allAtyData);
        //返回所有活动信息
        echo json_encode($returnData ,JSON_UNESCAPED_UNICODE);
        //此处输出是一些转义字符，有问题！加了后面JSON_UNESCAPED_UNICODE没问题了
    }


    //查询某一活动的详细信息
    public function getActivity(){
        $userModel = new \Home\Model\UserModel();
        $returnData = $userModel->confirmUser();
        if ($returnData['islogin'] == 0){
            echo json_encode($returnData ,JSON_UNESCAPED_UNICODE);
            exit;
        }
        $aty = new \Home\Model\ActivityModel();
        //get
        $activityId = $_GET['activityId'];
        $atyData = $aty->get($activityId);
        //返回某一活动的详细信息
        $returnData = array_merge($returnData, $atyData);
        echo json_encode($returnData,JSON_UNESCAPED_UNICODE);
    }

    //修改活动
    public function updateActivity(){
        $userModel = new \Home\Model\UserModel();
        $returnData = $userModel->confirmUser();
        if ($returnData['islogin'] == 0){
            echo json_encode($returnData ,JSON_UNESCAPED_UNICODE);
            exit;
        }
        $aty = new \Home\Model\ActivityModel();
        $atyData=file_get_contents("php://input");
        $status=$aty->update($atyData);
        $response=[
            'status' => status
        ];
        $returnData = array_merge($returnData, $response);
        echo json_encode($returnData);
    }
}