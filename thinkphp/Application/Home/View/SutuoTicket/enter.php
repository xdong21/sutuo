<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<!-- maximum-scale=1.0 与 user-scalable=no 一起使用,禁用缩放功能 -->
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<title>素拓加分</title>
		
		<link rel="stylesheet" href="/actyprj/thinkphp/Public/css/bootstrap.min.css">
		<link rel="stylesheet" href="/actyprj/thinkphp/Public/css/common.css" />
		
	</head>
	<body>
		<!-- container的左右padding改为了30px -->
		<div class="container">
			<div class="row">						
				<!-- center -->
				
				<!-- attention picture -->
				<div class="col-xs-12">
					<div class="row">
						<div class="col-xs-12 white-box top-offset"></div>
						<div class="col-xs-12 top-offset">
							<img class="col-xs-12" src="/actyprj/thinkphp/Public/images/enter_01.png" />
						</div>
					</div>
				</div>
				
				<!-- description -->
				<div class="col-xs-10 col-xs-offset-1 top-offset">
					<p class="font-size-16 text-center text-black">加分成功！您已录入<span id="score_size" class="text-green"><?php echo $_GET['score']; ?></span><span id="score_sort">普通分</span></p>
				</div>
				
				<!-- button -->
				<div class="col-xs-10 col-xs-offset-1 top-offset">
					<!-- btn-success 颜色改为了 { #36d387 } 交互颜色调整 -->
					<button id="enter_btn" type="button" class="btn btn-success button-width">查看个人分数</button>
				</div>

				<div  id="studentId" style="display:none" ><?php echo $_GET['studentId']; ?></div>
			</div>
		</div>
		
		<script src="/actyprj/thinkphp/Public/js/jquery.min.js"></script>
		<script src="/actyprj/thinkphp/Public/js/bootstrap.min.js"></script>
		<script src="/actyprj/thinkphp/Public/js/common.js"></script>
<!--	<script src="/thinkphp/Public/js/data.js" ></script> -->
		<script>
			$("#enter_btn").click(function() {
				// $.ajax({
				// 	url: "/thinkphp/index.php/Home/SutuoTicket/lookUp",
				// 	type: "GET",
				// 	data: {
				// 		'studentId': $("#studentId").text()
				// 	},
				// 	dataType: "json",
				// 	success: function(returnData) {
				// 		alert("ok"+returnData.studentId);
				// 		window.location.href = "/sutuo-WeChat/lookup.html";
				// 	},
				// 	error: function(returnData) {
				// 		window.location.href = "/sutuo-WeChat/lookup.html";
				// 	}
				// });
				window.location.href = "/sutuo-WeChat/lookup.php?studentId="+$("#studentId").text();
			});
		</script>
		
	</body>
</html>