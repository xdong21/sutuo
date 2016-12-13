<?php
namespace Home\Model;
use Think\Model\MongoModel;

header("Content-type: text/html; charset=utf-8");
//echo '<meta name="apple-mobile-web-app-capable" content="yes">';
//header('Content-Type: image/png');
class SutuoTicketModel extends MongoModel{

	private $appid = 'wx9b14a8f36174d547';
	private $appsecret = '6821104adefda97882feaecb3c7ae4a3';
	
	// 源码中的定义
	// $level纠错级别：L、M、Q、H
	// $size点的大小：1到10,用于手机端4就可以了
	// $margin为边缘厚度
	// public static function png($text, $outfile = false, $level = QR_ECLEVEL_L, $size = 3, $margin = 4, $saveandprint=false, $back_color = 0xFFFFFF, $fore_color = 0x000000){}	
	//生成素拓票
	public function generateSutuoTicket($generateData){
		$ticketModel = new \Think\Model\MongoModel("ticket");
		$ticketCollection = $ticketModel->getCollection();
		$atyModel = new \Think\Model\MongoModel("activity");
		$atyCollection = $atyModel->getCollection();
		$generateData = (array)json_decode($generateData);
		//引入phpqrcode
		Vendor('phpqrcode.phpqrcode');
		//素拓票的相关数据
		/*
		此处说明一下，由于需求修改代码修改较为繁琐，下面两句代码是比较简便的改法
		*/

		$templateIdData=$ticketCollection->find()->sort(array('templateId' => -1))->limit(1);
		foreach ($templateIdData as $value) {
			$templateId = $value['templateId']+1;
			break;
		}
		$generateData['templateId'] = $templateId;
		$ticketData['templateId'] = $templateId;
		$ticketData['activityId'] = $generateData['activityId'];
		$ticketData['sutuoDetail'] = $generateData['sutuoDetail'];
		//获取活动的素拓项名称
		$atyData = $atyCollection->findOne(array('activityId' => $generateData['activityId']));
		// 没有该活动的异常处理
		if($atyData == null){
			$returnData = [
				'status' => 0,
				'msg' => '无活动'
			];
			return $returnData;
		}
		$atyName = $atyData['name'];
		$sutuoName = $atyData['sutuo']['sutuoName'];
		$sutuoDetail = $generateData['sutuoDetail'];
		$score = $generateData['score'];
		$holdTime = $atyData['holdTime'];
		$ticketData['sutuoCode'] = $atyData['sutuo']['sutuoCode'];
		$ticketData['score'] = $generateData['score'];
		//创建该次素拓票文件夹
		$dir = './Public/ticket/templateId'.$templateId;
		$this->deldir($dir);
		mkdir($dir);
		//生成加分密匙并生成二维码
		$page = 1;//A4纸张数,初始值为第一张
		for ($i = 0; $i < $generateData['quantity']; $i++){
			$secret = $this->randomString();
			//将加分密匙存入数组中，且使用状态为未使用
			$ticketData['quantity'][$i]['secret'] = $secret;
			$ticketData['quantity'][$i]['status'] = 0;
			//填写二维码所指向网站的链接
			//其中state保存导入素拓分所需的信息
			$state = 'templateId'.$templateId.'secret'.$secret;
			$redirect_uri = 'http://www.scutsutuo.cc/actyprj/thinkphp/index.php/Home/SutuoTicket/temp';
			$url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_userinfo&state='.$state.'#wechat_redirect';
			//生成素拓票存入 actyprj/thinkphp/Public/ticket
			$dir = './Public/ticket/templateId'.$templateId.'/'.$i.'.png';
			$src = \QRcode::png($url, $dir, 'QR_ECLEVEL_M', 4, 0);
			$tplfile = './Public/ticket/templateId.png';
			// $srcfile = './Public/ticket/templateId'.$templateId.'/'.$i.'.png';
			$imagefile = './Public/ticket/templateId'.$templateId.'/ticket'.$i.'.png';
			$tpl = imagecreatefrompng($tplfile); 
			// $src = imagecreatefrompng($srcfile);
			// unlink($srcfile);
			//创建png文件,使它为tpl的缩略图
			$width = imagesx($tpl); $height = imagesy($tpl);
			$img_width = 730; $img_height = 330;
			$img = imagecreatetruecolor($img_width, $img_height);
			imagecopyresampled($img,$tpl,0,0,0,0,$img_width,$img_height,$width,$height);
			$width = imagesx($src); $height = imagesy($src);
			imagecopyresampled($img,$src,450,52,0,0,$width,$height,$width,$height);
			//设置字体颜色和形式
			$black = imagecolorallocate($img ,0, 0, 0);
			$font_file ="./Public/font/SIMYOU.TTF";
			//设置活动
			$str = $atyName;
			imagettftext($img,17,0,45,235,$black,$font_file,$str);
			//设置分数说明
			$str = "——".$sutuoDetail."——";
			imagettftext($img,12,0,45,265,$black,$font_file,$str);
			//设置分数
			$pos = (int)((int)($score/0.25))%4;
			switch ($pos) {
				case 0:
					imagettftext($img,35,0,340,257,$black,$font_file,$score);
					break;
				case 1:
					imagettftext($img,35,0,270,257,$black,$font_file,$score);
					break;
				case 2:
					imagettftext($img,35,0,295,257,$black,$font_file,$score);
					break;
				case 3:
					imagettftext($img,35,0,270,257,$black,$font_file,$score);
					break;
				default:
					$str = "X";
					imagettftext($img,45,0,340,257,$black,$font_file,$score);
					break;
			}	
			//设置编号
			//$black = imagecolorallocate($img ,131, 139, 131);
			if ($i < 10) $id = "00".($i+1);
			if (($i > 10)&&($i < 100)) $id = "0".($i+1);
			if ($i > 100) $id = $i+1;
			$str ="编号：".$id."   "."活动时间：".$holdTime."   "."类型：".$sutuoName;
			imagettftext($img,10,0,45,295,$black,$font_file,$str);

			//合成A4，每张A4里10张票
			if (($i % 10) != 0){
				if (($i % 10) == 5){
					$pos_width = 1450;
					$pos_height = 300;
				}else{
					$pos_height += 630;
				}
				imagecopyresampled($pageimg, $img, $pos_width, $pos_height, 0, 0, $src_width, $src_height, $src_width, $src_height);
				if ($i == ($generateData['quantity']-1)){
					$savefile = './Public/ticket/templateId'.$templateId.'/page'.$page.'.png';
					imagepng($pageimg,$savefile);
					imagedestroy($pageimg);
					$page++;
				}
			}else{
				//保存图片（为0时不保存），重新载入空白A4纸
				if ($i != 0){
					$savefile = './Public/ticket/templateId'.$templateId.'/page'.$page.'.png';
					imagepng($pageimg,$savefile);
					imagedestroy($pageimg);
					$page++;
				}
				$pagesrcfile = './Public/ticket/a4.png';
				$pageimg = imagecreatefrompng($pagesrcfile);
				$src_width = 730; $src_height = 330;
				$pos_width = 300; $pos_height = 300;
				imagecopyresampled($pageimg, $img, $pos_width, $pos_height, 0, 0, $src_width, $src_height, $src_width, $src_height);
			}
			imagedestroy($img);
		} 
		$zip = new \ZipArchive();
		if ($zip->open('./Public/ticket/templateId'.$templateId.'.zip', \ZipArchive::OVERWRITE) === TRUE) {
		    $this->addFileToZip('./Public/ticket/templateId'.$templateId, $zip,$templateId); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
		    $zip->close(); //关闭处理的zip文件
		}
		$this->deldir('./Public/ticket/templateId'.$templateId);
		$ticketCollection->insert($ticketData);
		//文件下载
		require('FileDownload.class.php'); 
		$file = './Public/ticket/templateId'.$templateId.'.zip'; 
		$obj = new \FileDownload(); 
		//$flag = $obj->download($file, ''); 
		$flag = $obj->download($file, $name, true); // 断点续传 
		if(!$flag){ 
		  echo 'file not exists'; 
		}
	}

	//添加文件夹到zip
	function addFileToZip($path, $zip,$templateId) {
	    $handler = opendir($path); //打开当前文件夹由$path指定。
	    /*
	    循环的读取文件夹下的所有文件和文件夹
	    其中$filename = readdir($handler)是每次循环的时候将读取的文件名赋值给$filename，
	    为了不陷于死循环，所以还要让$filename !== false。
	    一定要用!==，因为如果某个文件名如果叫'0'，或者某些被系统认为是代表false，用!=就会停止循环
	    */
		$current = getcwd();//记录当前目录
		chdir('./Public/ticket');//改变目录
	    while (($filename = readdir($handler)) !== false) {
	        if ($filename != "." && $filename != "..") {//文件夹文件名字为'.'和‘..’，不要对他们进行操作
	            // if (is_dir($path . "/" . $filename)) {// 如果读取的某个对象是文件夹，则递归
	            //     addFileToZip($path . "/" . $filename, $zip);
	            // } else { //将文件加入zip对象
	                $zip->addFile('templateId'.$templateId.'/'.$filename);
	            // }
	        }
	    }
		chdir($current);//恢复目录
	    @closedir($path);
	}

	//删除文件夹
	function deldir($dir) {
	//先删除目录下的文件：
		$dh=opendir($dir);
		while (($file = readdir($dh))!=false) {
			if($file!="." && $file!="..") {
				$fullpath=$dir."/".$file;
				if(!is_dir($fullpath)) {
					unlink($fullpath);
				}else {
					$this->deldir($fullpath);
				}
			}
		}
		closedir($dh);
		//删除当前文件夹：
		if(rmdir($dir)) {
			return true;
		} else {
			return false;
		}
	}


	//跳转中转，测试号功能不足，有服务号可修改
	public function temp(){
		$redirect_uri = 'http://www.scutsutuo.cc/actyprj/thinkphp/index.php/Home/SutuoTicket/getAddingTable';
		$state = $_GET['state'];
        $url='https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->appid.'&redirect_uri='.$redirect_uri.'&response_type=code&scope=snsapi_base&state='.$state.'#wechat_redirect';
        Header("Location:$url");
	}


	//确认加分
	public function getAddingTable(){
		$ticketModel = new \Think\Model\MongoModel("ticket");
		$ticketCollection = $ticketModel->getCollection();
		$studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		$atyModel = new \Think\Model\MongoModel("activity");
		$atyCollection = $atyModel->getCollection();
		$classModel = new \Think\Model\MongoModel("class");
		$classCollection = $atyModel->getCollection();
		//处理网页授权接口重定向后的GET数据
		$code = $_GET['code'];
		$state = $_GET['state'];
		$stateData = $this->transformState($state);
		//根据code请求得到扫码用户的openid
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appid."&secret=".$this->appsecret."&code=".$code."&grant_type=authorization_code";
		$userData = (array)json_decode($this->httpRequest($url, 'get'));
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=".$userData['access_token']."&openid=".$userData['openid']."&lang=zh_CN";
		$detailData = (array)json_decode($this->httpRequest($url, 'get'));
		//var_dump($userData);
		$openId = $userData['openid'];//echo $openId."<br>";
		//若无绑定openid，则提示用户绑定学号
		if($openId == NULL){
			$show = [
				'page' => 0
			];
			return $show;
		}
		//查找该openid对应的学生
		$studentData = $studentCollection->findOne(array('openId' => $openId));//var_dump($student);
		//若无绑定信息，则提示用户绑定学号
		if($studentData == NULL){
			$show = [
				'page' => 0
			];
			return $show;
		}
		//判断该素拓票是否未用
		$ticketData = $ticketCollection->findOne(array('templateId' => $stateData['templateId']));
		$judge = 0;
		foreach ($ticketData['quantity'] as $key => $value) {
			if(!strcmp($value['secret'],$stateData['secret'])){
				$judge = 1;
				break;
			}
		}
		//无此加分密匙
		if(!$judge){
			$show = [
				'page' => 1
			];
			return $show;
		}
		//判断素拓票的status是否为0，为0表示未用
		if($ticketData['quantity'][$key]['status']){
			$show = [
				'page' => 1
			];
			return $show;
		}

		//判断该学生是否已加此素拓分
		foreach ($studentData['sutuoItems'] as $value){
			if (($value['addingFrom']['activityId'] == $ticketData['activityId'])&&($value['addingFrom']['addingTypeCode'] == 'qrcode')){
				$show = [
					'page' => 2
				];
				return $show;
			}
		}
		$studentData['headimgurl'] = $detailData['headimgurl'];
		$studentCollection->update(
			array('openId' => $openId),$studentData
		);
		$atyData = $atyCollection->findOne(array('activityId' => $ticketData['activityId']));
		$classData = $classCollection->findOne(array('classId' => $studentData['classId']));
		$show = [
			'page' => 3,
			'activityName' => $atyData['name'],
			'sutuoType' => $atyData['sutuoType'],
			'score' => $ticketData['score'],
			'sutuoName' => $atyData['sutuo']['sutuoName'],
			'year' => substr($studentData['classId'], 0, 4),
			'class' => $classData['majorName'].$classData['className'],
			'studentName' => $studentData['studentName'],
			'templateId' => $stateData['templateId'],
			'studentId' => $studentData['studentId'],
			'secret' => $stateData['secret']
		];
		return $show;
	}


	//素拓加分
	public function addSutuo($data){
		$ticketModel = new \Think\Model\MongoModel("ticket");
		$ticketCollection = $ticketModel->getCollection();
		$studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		$ticketData = $ticketCollection->findOne(array('templateId' => (int)$data['templateId']));
		$studentData = $studentCollection->findOne(array('studentId' => $data['studentId']));
		//判断素拓票，学生是否真实存在数据库中
		if (($ticketData == null) || ($studentData == null)){
			$returnData = [
				'status' => 0,
				'msg' => '链接无效',
				'data' => json_encode($data)
			];
			return $returnData;
		}
		//判断该素拓票是否未用
		$judge = 0;
		foreach ($ticketData['quantity'] as $key => $value) {
			if(!strcmp($value['secret'],$data['secret'])){
				$judge = 1;
				break;
			}
		}
		//无此加分密匙
		if(!$judge){
			$returnData = [
				'status' => 0,
				'msg' => '素拓票无效',
			];
			return $returnData;
		}
		//判断素拓票的status是否为0，为0表示未用
		if($ticketData['quantity'][$key]['status']){
			$returnData = [
				'status' => 0,
				'msg' => '素拓票无效'
			];
			return $returnData;
		}
		//将此素拓票无效化
		$ticketData['quantity'][$key]['status'] = 1;
		//判断该学生是否已加此素拓分
		foreach ($studentData['sutuoItems'] as $value){
			if (($value['addingFrom']['activityId'] == $ticketData['activityId'])&&($value['addingFrom']['addingTypeCode'] == 'qrcode')){
				$returnData = [
					'status' => 0,
					'msg' => '已加该活动素拓分'
				];
				return $returnData;
			}
		}
		//对该学生进行加分
		//获得sutuoItems当前数目
		$i = count($studentData['sutuoItems']);
		$studentData['sutuoItems'][$i]['addingTime'] = date('y-m-d');
		$studentData['sutuoItems'][$i]['score'] = $ticketData['score'];
		$studentData['sutuoItems'][$i]['sutuoCode'] = $ticketData['sutuoCode'];
		$studentData['sutuoItems'][$i]['optionSource']['optionSourceCode'] = $ticketData['templateId'];
		$studentData['sutuoItems'][$i]['optionSource']['id'] = date(ymdhis);
		$studentData['sutuoItems'][$i]['addingFrom']['activityId'] = $ticketData['activityId'];
		$studentData['sutuoItems'][$i]['addingFrom']['addingTypeCode'] = 'qrcode';
		switch ($ticketData['sutuoCode']){
			case '0':
				$studentData['sutuoScore']['general'] += $ticketData['score']; 
				break;
			case '1':
				$studentData['sutuoScore']['humanity'] += $ticketData['score']; 
				break;	
			case '2':
				$studentData['sutuoScore']['creative'] += $ticketData['score']; 
				break;
			default:
				break;
		}
		//更新学生信息
		$studentCollection->update(
			array('studentId' => $data['studentId']),$studentData
		);
		//更新素拓票信息
		$ticketCollection->update(
			array('templateId' => $ticketData['templateId']),$ticketData
		);
		$returnData = [
			'status' => 1,
			'studentId' => $data['studentId'],
			'score' => $ticketData['score']
		];
		return $returnData;
	}

	//进入学号绑定填写页面
	public function ensureBound(){
		$studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		//处理网页授权接口重定向后的GET数据
		$code = $_GET['code'];
		//根据code请求得到扫码用户的openid
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$this->appid."&secret=".$this->appsecret."&code=".$code."&grant_type=authorization_code";
		$userData = (array)json_decode($this->httpRequest($url, 'get'));
		if(!isset($userData['openid'])){
			$show['page'] = 0;
			return $show;
		}
		$openId = $userData['openid'];
		if($openId == null){
			$show['page'] = 0;
			return $show;
		}
		$show['page'] = 1;
		$studentData = $studentCollection->findOne(array('openId' => $openId));
		if ($studentData == null){
			$show['currentId'] = '无';
		}else{
			$show['currentId'] = $studentData['studentId'];
		}
		$show['openId'] = $openId;
		return $show;
	}

	//进行学号绑定
	public function bound($data){
		$studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		if (($data['openId'] == null)||($data['studentId'] == null)){
			$returnData = [
				'status' => 0,
				'msg' => '数据传输错误',
			];
			return $returnData;
		}
		$studentData = $studentCollection->findOne(array('studentId' => $data['studentId']));
		if ($studentData == null){
			$returnData = [
				'status' => 0,
				'msg' => '素拓部未导入该学号',
			];
			return $returnData;
		}
		$newData = [
			'$set' => [
				'openId' => $data['openId']
			]
		];
		$studentCollection->update(array('studentId' => $data['studentId']),$newData);
		$returnData = [
			'status' => 1,
			'msg' => '绑定成功',
		];
		return $returnData;
	}

	//学生素拓查询
	public function lookUp($studentId){
		$studentModel = new \Think\Model\MongoModel("student");
		$studentCollection = $studentModel->getCollection();
		$atyModel = new \Think\Model\MongoModel("activity");
		$atyCollection = $atyModel->getCollection();
		$studentData = $studentCollection->findOne(array("studentId" => $studentId));
		if ($studentData == null){
			$returnData = [
				'status' => 0,
				'msg' => '无此学生'
			];
			return $returnData;
		}
		$returnData = [
			'status' => 200,
			'studentName' => $studentData['studentName'],
			'headimgurl' => $studentData['headimgurl'],
			'general' => $studentData['sutuoScore']['general'],
			'humanity' => $studentData['sutuoScore']['humanity'],
			'creative' => $studentData['sutuoScore']['creative']
		];
		//普通、文创、科创个数
		$generalNumber=0; $humanityNumber=0; $creativeNumber=0;
		foreach ($studentData['sutuoItems'] as $key => $value) {
			switch ($value['sutuoCode']) {
				case 0:
					$atyData = $atyCollection->findOne(array("activityId" => $value['addingFrom']['activityId']));
					$returnData['generalDetail'][$generalNumber] = [
						'holdTime' => $atyData['holdTime'],
						'activityName' => $atyData['name'],
						'score' => $value['score']
					];
					$generalNumber++;
					break;
				case 1:
					$atyData = $atyCollection->findOne(array("activityId" => $value['addingFrom']['activityId']));
					$returnData['humanityDetail'][$generalNumber] = [
						'holdTime' => $atyData['holdTime'],
						'activityName' => $atyData['name'],
						'score' => $value['score']
					];
					$humanityNumber++;
					break;
				case 2:
					$atyData = $atyCollection->findOne(array("activityId" => $value['addingFrom']['activityId']));
					$returnData['creativeDetail'][$generalNumber] = [
						'holdTime' => $atyData['holdTime'],
						'activityName' => $atyData['name'],
						'score' => $value['score']
					];
					$creativeNumber++;
					break;
				default:
					$returnData = [
						'status' => 0,
						'msg' => '查询错误'
					];
					return $returnData;
					break;
			}
		}
		//按时间先后排序，rsort()默认以第一个字段从大到小排序
		rsort($returnData['generalDetail']);
		rsort($returnData['humanityDetail']);
		rsort($returnData['creativeDetail']);
		return $returnData;
	}


	//分解字符串$state中的数据转化为数组
	function transformState($state){
		$temp=array('1','2','3','4','5','6','7','8','9','0','.');
		//共有三个数值
//		for ($j=0; $j<4; $j++)
//		{
			$name = '';
			$i = 0;
			for($i=0; $i<strlen($state); $i++){
				$judge = 0;
				$multi = 10;
				while(in_array($state[$i],$temp)){
					if ($state[$i] == '.'){
						$multi = 0.1;
						$i++;
						continue;
					}
					if ($multi == 10){
						$stateData[$name]=$stateData[$name]*$multi+(int)$state[$i];
						$multi = $multi*10;
					}
					else{
						$stateData[$name]=$stateData[$name]+$multi*(int)$state[$i];
						$multi = $multi*0.1;
					}
					$i++;
					$judge = 1;
				}
				$name.=$state[$i];
				if ($judge == 1) break;
			}
			$state = substr($state, $i);
//		}
		//提取secret密匙
		$stateData['secret'] = substr($state, 6);
		return $stateData;
	}

	//生成指定长度的随机字符串
	public function randomString(){
		srand(microtime()*10000000);
		$possible="ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
		$str="";
		$len = 8;
		while(strlen($str)<$len) {
			$str.=substr($possible,(rand()%(strlen($possible))),1);
		}
		return $str;
	}

	//sutuoName转换为sutuoCode
	public static function chgSutuoCode($sutuoName){
		switch ($sutuoName) {
			case '普通分':
				$sutuoCode = 0;
				break;

			case '人文素养教育学分':
				$sutuoCode = 1;
				break;

			case '创新能力培养学分':
				$sutuoCode = 2;
				break;
		}
		return $sutuoCode;
	}

	//使用curl进行请求,$method='get'or'post'
	public function httpRequest($url, $method='get', $data='undefined'){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		if ($method == 'get'){
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22");
			curl_setopt($ch, CURLOPT_ENCODING ,'gzip'); //加入gzip解析
        	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求 不验证证书和hosts
        	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}           
		if ($method == 'post'){
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			if (curl_errno($ch)) {
				return curl_error($ch);
			}
		}	
		$output = curl_exec($ch);		
		curl_close($ch);
		return $output;
	}

	//长链接转短连接
	public function shortUrl($url){
		$tokenArr = (array)json_decode($this->httpRequest('https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret.'','get'));
		$token = $tokenArr['access_token'];
		$requestUrl = 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token='.$token.'';
		$data = '{"action":"long2short","long_url":"'.$url.'"}';
		$shortUrlArr = (array)json_decode($this->httpRequest($requestUrl,'post',$data));
		$shortUrl = $shortUrlArr['short_url'];
		return $shortUrl;
	}
}