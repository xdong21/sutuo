<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<title>学生素拓查询</title>

		<link rel="stylesheet" href="/actyprj/thinkphp/Public/css/bootstrap.min.css" />
		<link rel="stylesheet" href="/actyprj/thinkphp/Public/css/common.css" />
	</head>
	<body>
		<!-- 上 -->
		<div class="up-box">
			<!-- 背景进度 -->
			<div class="bg-progress">
				<div id="bg_progress_loading"></div>
			</div>
			<!-- 上半部分显示的信息 -->
			<div class="info-box">
				<!-- 头像 -->
				<div class="img-position">
					<img id="headimg" class="img-circle img-size" src="" />
					<p id="img_name"></p>
				</div>
				
				<!-- 分数 -->
				<div class="score-position">
					<div class="score-box-half">
						<p id="humanity" class="font-gray-big">0</p>
						<p class="font-gray-small">文创</p>
					</div>
					<div class="score-box-half">
						<p id="creative" class="font-gray-big">0</p>
						<p class="font-gray-small">科创</p>
					</div>
					<div class="score-ruler">
						<div class="score-ico">
							<span class="score-ico-all score-ico-1"></span>
							<span class="score-ico-all score-ico-2"></span>
							<span class="score-ico-all score-ico-3"></span>
							<span class="score-ico-all score-ico-1"></span>
						</div>
						<p class="font-gray-small">加分规则</p>
					</div>
				</div>
				
				<!-- 进度条 -->
				<div class="bar-progress">
					<div id="bar_progress_loading"></div>
				</div>
				
				<!-- 进度条信息 -->
				<div class="font-gray-small">
					<p class="score-message">本学年普通总分</p>
					<p class="score-percentage">
						<span id="score">0</span>
						<span>/</span>
						<span id="full_score">10</span>
					</p>
				</div>
			</div>
		</div>

		<!-- 下 -->
		<div class="pages-box">
			<ul class="pages">
				<li class="pages-click">普通分</li>
				<li>文创</li>
				<li>科创</li>
			</ul>
			<div class="pages-info">
				<span class="pages-info-left">活动</span>
				<span class="font-gray-small pages-info-right">分值</span>
			</div>
			<div class="pages-play">
				<div class="pages-play-piece">
					<div class="pages-patterns">
						<div class="pages-line"></div>
					</div>
					<div id="generalDetail" class="pages-tickets">
						<!--这里是普通分信息-->
					</div>
				</div>
				<div class="pages-play-piece">
					<div class="pages-patterns">
						<div class="pages-line"></div>
					</div>
					<div id="humanityDetail" class="pages-tickets">
						<!--这里是人文分信息-->
					</div>
				</div>
				<div class="pages-play-piece">
					<div class="pages-patterns">
						<div class="pages-line"></div>
					</div>
					<div id="creativeDetail" class="pages-tickets">
						<!--这里是科创分信息-->
					</div>
				</div>
			</div>
		</div>
		<div  id="studentId" style="display:none" ><?php echo $_GET['studentId']; ?></div>
		
		<script src="/actyprj/thinkphp/Public/js/jquery.min.js"></script>
		<script src="/actyprj/thinkphp/Public/js/bootstrap.min.js" ></script>
<!--	<script src="/thinkphp/Public/js/lookup.js" ></script> -->
		<script src="/actyprj/thinkphp/Public/js/common.js"></script>
		<script>
			(function() {
				$(document).ready(function() {		
					/* 分页大小 */
					(function() {
						$(".pages-play").css("height", window.innerHeight - 250 - 56 - 37 + "px");
						$(".pages-play-piece").css("height", window.innerHeight - 250 - 56 - 37 - 30 + "px");
					})();
					/*分页*/
					function pagesDivide() {
						$(".pages").children("li").click(function() {
							/*这里this指 li */
							$("li").removeClass("pages-click");
							$(this).addClass("pages-click");
							var thisClick = $(this).index();
							$(".pages-play").animate({
								marginLeft: -thisClick * 100 + "%",
							});
						});
					}
					pagesDivide();
					
					/*分数占比条*/
					function percentBar(data) {
						var plusif = 10;
						var scoreBar = Number(data.general) + Number(data.humanity) + Number(data.creative);
						
						/*分数嵌入*/
						addNumber(data.humanity, humanity);
						addNumber(data.creative, creative);
						addNumber(scoreBar, score);

						/* 如果超过上限分数 */
						if(scoreBar>10) {
							scoreBar = 10;
						}
						var percent = scoreBar / $("#full_score").text();
						
						/* 进度条动画 */
						$("#bar_progress_loading").animate({
							width: percent * 100 + "%",
						}, 5000);
						
						/*背景*/
						switch(percent) {
							case 1: plusif = 20; break;
							case 0: plusif = 0; break;
							default: plusif = 10; break;
						}
						$("#bg_progress_loading").animate({
							width: percent * 80 + plusif + "%",
						}, 5000);
					}
					
					/*一个个加数字*/
					function addNumber(upLimit, id) {
						var i = 0;
						var t = setInterval(function() {
							$(id).html(i);
							if(upLimit - i >= 1) {
								i  += 1;
							} else if(upLimit - i > 0.25) {
								i += 0.5;
							} else if(upLimit - i <= 0) {
								clearInterval(t);				
							} else {
								i += 0.25;
							}
						}, 400);
					}
					
					/*素拓查询条目*/
					function lineDot(parentNum, i) {
						
						$(".pages-play-piece").each(function() {
							if($(this).index() + 1 == parentNum) {
								/*线长*/
								$(this).children(".pages-patterns").children(".pages-line").css("height", (i - 1) * 48 + "px");
								
								/*绿圈数*/
								for(var n = 0; n < i; n++) {
									$(this).children(".pages-patterns").append('<span class="pages-circle"></span>');
								}
							}
						});
					}
					
					/*创建素拓票节点*/
					function createTicket(item, date, value) {
						return "<div class='pages-tickets-one'><p class='score-value'>+" + value + "</p><p class='score-item'>" + item + "</p><p class='score-date'>" + date + "</p></div>";
					}
					function circleCreate(parentNum, Detail) {
						switch(parentNum) {
							case 1: var parent = $("#generalDetail");break;
							case 2: var parent = $("#humanityDetail");break;
							case 3: var parent = $("#creativeDetail");break;
						}
						var i = 0;
						
						/*判断JOSN中的数据是否可用的重要依据*/
						while(Detail != null && Detail != undefined && Detail[i] != null) {
							var $childrenNode = createTicket(Detail[i].activityName, Detail[i].holdTime, Detail[i].score);
							parent.append($childrenNode);
							i++;
						}
						/*空的话，你要加油啊！！！*/
						if(Detail == null) {
							var $nullNode = createTicket("加油多参加点活动吧！", 0, 0);
							parent.append($nullNode);
							i++;
						}
						lineDot(parentNum, i);
					}
					function createNode(data) {
						/* 普通分 */
						circleCreate(1, data.generalDetail);
						/* 人文分 */
						circleCreate(2, data.humanityDetail);
						/* 科创分 */
						circleCreate(3, data.creativeDetail);
					}
					/* 修改头像 */
					function headimg(data) {
						document.getElementById("headimg").src = data.headimgurl;
					}
					
					function readFromJSON() {
						$.getJSON("/actyprj/thinkphp/index.php/Home/SutuoTicket/lookUp?studentId="+$("#studentId").text(), function(data) {
							console.log(data);
							if(data.status == 200) {
								$("#img_name").html(data.studentName);
								/*分数占比条*/
								percentBar(data);
								/*创造节点为素拓票*/
								createNode(data);
								headimg(data);
							} else {
								if(confirm("好像出了点问题，是否刷新？")) {
									window.history.go(0);
								} else {
									window.history.go(-1);
								}
							}
						});
					}
					readFromJSON();
					
				})
			})();
		</script>
	</body>
</html>
