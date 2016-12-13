<?php
#
# ****************  素拓系统后台 素拓票部分 *****************
#

namespace Home\Controller;
use Think\Controller;

header("Content-type: text/html; charset=utf-8");  
class SutuoTicketController extends Controller {
    public function index(){
    	//$this->display('unbound');
    }


    //生成素拓票
    public function generateSutuoTicket(){
        $ticketObj = new \Home\Model\SutuoTicketModel();

        $generateData = file_get_contents("php://input");
        // $generateData = [
        // 	'activityId' => 1,
        // 	'sutuoDetail' => '普通分',
        // 	'score' => 0.5,
        // 	'quantity' => 5,
        // ];
        // $generateData = json_encode($generateData);
        $returnData = $ticketObj->generateSutuoTicket($generateData);
        echo json_encode($returnData);
    }

    //将素拓票与模板合成,尺寸自行规定
    public function composeImage(){
        $ticketObj = new \Home\Model\SutuoTicketModel();
        $ticketObj->composeImage();
    }

    //跳转中转
    public function temp(){
    	$ticketObj = new \Home\Model\SutuoTicketModel();
    	$ticketObj->temp();
    }

    //进入加分界面
    public function getAddingTable(){
    	$ticketObj = new \Home\Model\SutuoTicketModel();
    	$show = $ticketObj->getAddingTable();
        //显示页面
        switch ($show['page']) {
            case 0:
                $this->display('unbound');//未绑定
                break;
            case 1:
                $this->display('ticketused');//素拓票已用
                break;
            case 2:
                $this->display('unadd');//已加分
                break;
            case 3:
                $this->assign('show',$show);
                $this->display('confirm');//确认加分
                break;   
            default:
                echo "wrong";
                break;
        }
    }

    //执行加分请求
    public function addSutuo(){
        $ticketObj = new \Home\Model\SutuoTicketModel();
        $data = file_get_contents("php://input");
        $data = (array)json_decode($data);
        $returnData = $ticketObj->addSutuo($data);
        echo json_encode($returnData);
    }

    //学生素拓查询
    public function lookUp(){
        $ticketObj = new \Home\Model\SutuoTicketModel();
        $studentId = $_GET['studentId'];
        $returnData = $ticketObj->lookUp($studentId);
        echo json_encode($returnData);
    }

    //进入学号绑定填写页面
    public function ensureBound(){
        $ticketObj = new \Home\Model\SutuoTicketModel();
        $show = $ticketObj->ensureBound();
        //显示页面
        switch ($show['page']) {
            case 0:
                $this->display('boundwrong');
                break;
            case 1:
                $this->assign('show', $show);
                $this->display('ensureBound');
                break; 
            default:
                $this->display('boundwrong');
                break;
        } 
    }

    //进行学号绑定
    public function bound(){
        $ticketObj = new \Home\Model\SutuoTicketModel();
        $data = file_get_contents("php://input");
        $data = (array)json_decode($data);
        $returnData = $ticketObj->bound($data);
        echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
    }
}