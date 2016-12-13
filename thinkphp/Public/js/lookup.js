/*
 	for lookup,html
 */
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
		
		function readFromJSON() {
			$.getJSON("/thinkphp/index.php/Home/SutuoTicket/lookUp?studentId="+$("#studentId").text(), function(data) {
				console.log(data);
				if(data.status == 200) {
					$("#img_name").html(data.studentName);
					/*分数占比条*/
					percentBar(data);
					/*创造节点为素拓票*/
					createNode(data);
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
