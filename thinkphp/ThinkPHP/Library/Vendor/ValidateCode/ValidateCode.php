<?php
//验证码类
class ValidateCode {
	private $charset = 'abcdefghkmnprstuvwxyzABCDEFGHKMNPRSTUVWXYZ23456789';//随机因子
	private $code;//验证码 
	private $codelen = 4;//验证码长度
	private $width = 80;//宽度
	private $height = 23;//高度
	private $img;//图形资源句柄
	private $font;//指定的字体
	private $fontsize = 15;//指定字体大小
	private $fontcolor;//指定字体颜色
	//构造方法初始化
	public function __construct() {
		$this->font = "./Public/font/SIMYOU.TTF";//注意字体路径要写对，否则显示不了图片
	}
	//生成随机码
	private function createCode() {
		$_len = strlen($this->charset)-1;
		for ($i=0;$i<$this->codelen;$i++) {
			$this->code .= $this->charset[mt_rand(0,$_len)];
		}
	}
	//生成背景
	private function createBg() {
		$this->img = imagecreatetruecolor($this->width, $this->height);
		//生成白色背景
		$color = imagecolorallocate($this->img, 245, 245, 245);
 		imagefilledrectangle($this->img,0,$this->height,$this->width,0,$color);
	}
	//生成文字
	private function createFont() {
		$_x = $this->width / $this->codelen;
		for ($i=0;$i<$this->codelen;$i++) {
			$this->fontcolor = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
			imagettftext($this->img,$this->fontsize,mt_rand(-15,15),$_x*$i+mt_rand(1,3),$this->height / 1.4,$this->fontcolor,$this->font,$this->code[$i]);
		}
	}
	//生成线条、雪花
	private function createLine() {
		//线条
// 		for ($i=0;$i<6;$i++) {
// 			$color = imagecolorallocate($this->img,mt_rand(0,156),mt_rand(0,156),mt_rand(0,156));
// 			imageline($this->img,mt_rand(0,$this->width),mt_rand(0,$this->height),mt_rand(0,$this->width),mt_rand(0,$this->height),$color);
// 		}
		
		//雪花
		for ($i=0;$i<100;$i++) {
			$color = imagecolorallocate($this->img,mt_rand(200,255),mt_rand(200,255),mt_rand(200,255));
			imagestring($this->img,mt_rand(1,5),mt_rand(0,$this->width),mt_rand(0,$this->height),'.',$color);
		}
	}
	//输出
	private function outPut() {
		header('Content-type:image/png');
		// 将图像以png形式输出
		imagepng($this->img);
		// 将图像资源从内存中销毁,以节约资源
		imagedestroy($this->img);
	}
	//生成验证码并输出验证码图像
	public function createImg() {
		$this->createBg();
		$this->createCode();
		$this->createLine();
		$this->createFont();
		$this->outPut();
	}
	//获取验证码
	public function getCode() {
		//字符串转换为小写
		return strtolower($this->code);
	}
}