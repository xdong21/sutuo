$(document).ready(function() {
	$("#confirm_btn").click(function() {
		$.ajax({
			url: '/thinkphp/index.php/Home/SutuoTicket/addSutuo',
//			url: '/thinkphp/test.json',
			type: 'POST',
			contentType: "application/json; charset=utf-8",
			data: {
				templateId: $("#templateId").text(),
				studentId: $("#studentId").text(),
				secret: $("#secret").text(),
			},
			dataType: "json",
			success: function(returnData) {
				console.log(returnData);
				alert(returnData);
				window.location.href = "/sutuo-WeChat/enter.html";
			},
			error: function(returnData) {
				console.log(grade);
				console.log($("#grade").text());
				console.log("error");
				console.log(returnData);
				alert(returnData);
				window.location.href = "/sutuo-WeChat/lookup.html";
			}
		});
	});
	
	$("#enter_btn").click(function() {
		$.ajax({
			url: "/thinkphp/index.php/Home/SutuoTicket/lookUp",
			type: "GET",
			data: {
				studentId: "string",
			},
			success: function(data) {
				window.location.href = "/thinkphp/Application/lookup.html";
			},
			error: function(data) {
				alert("error");
				window.location.href = "/thinkphp/Application/lookup.html";
			}
		});
	});
});
