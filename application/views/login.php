<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>登陆-路由流量综合监测系统</title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/reset.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/login.css">
<script src="<?php echo base_url(); ?>application/views/js/jquery-1.9.1.js"></script>
<script type="text/javascript">
var remember;	//是否记住登录状态
var checkLoginURL = "<?php echo base_url(); ?>index.php/interface_common/login";	//ajax提交地址
var ref = "<?php echo $ref; ?>";	//跳转回的地址
</script>
</head>

<body>
	<div id="container">
		<div id="content">
			<div id="logo-wapper">
				<div id="logo" style="display:none;"></div>
				<div id="name-C">路由流量综合监测设备</div>
                <div id="name-E" style="display:none;">Route&Traffic </div>
				<div class="clear"></div>
			</div>
			
			<div id="login-wapper">
				<!-- <div id="head-img"></div> -->
				<div style="width:220px;margin:auto;padding:30px;font:bold 18px 微软雅黑">请输入用户名密码：</div>
				<div id="login-input">
					<form>
						<input id="username" class="username" />
						<input id="password" class="password" type="password" />
					</form>
                    <div id="switch" style="display:none;">
                    	<div id="sel1">√</div>
                        <div id="sel2" class="switch-on"></div>
                        <div id= "sel3">记住状态</div>
                    </div>
                    <button id="sub">登录</button>  
				</div>
                <div class="wrong" id="wrong-username">该用户名不存在！</div>
                <div class="wrong" id="wrong-pw">密码输入错误！</div>
			</div>
			<div class="clear"></div>
		</div>
	</div>

	<div class="footer">中国电子科技集团公司第五十四研究所</div>
	<div class="footer">中国科学院计算技术研究所</div>
	<div class="footer">联合研制</div>
</body>

<script type="text/javascript">
	$(document).ready(function(){
		remember = 1;
		$("#wrong-username").hide();
		$("#wrong-pw").hide();
	});

	//响应键盘回车键消息
	$(function(){
		document.onkeydown = function(e){ 
			var ev = document.all ? window.event : e;
			if(ev.keyCode == 13) {
				$("#sub").click();
			}
		}
	});  

	//记住登陆状态改变
	$("#sel2").click(function(){
		//改变记住状态的值
		if (remember == 1) {
			remember = 0;
		} else {
			remember = 1;
		}
		//改变样式
		if (remember == 1) {
			$("#sel1").html("√");
			$(this).removeClass("switch-off");
			$(this).addClass("switch-on");
		} else {
			$("#sel1").html("×");
			$(this).removeClass("switch-on");
			$(this).addClass("switch-off");
		}
	});

	$("#sub").click(function(){
		var username = $("#username").val();
		var password = $("#password").val();
		//清掉样式
		$("#username").removeClass('input-wrong');
		$("#password").removeClass('input-wrong');
		$("#wrong-username").hide();
		$("#wrong-pw").hide();
		//TODO: 用户数据检测
		if (username.length < 3 || username.length >16) {
			$("#username").addClass('input-wrong');
			$("#wrong-username").html("用户名长度在3和16之间");
			$("#wrong-username").show();
			return false;
		}
		if (password.length < 5 || password.length >16) {
			$("#password").addClass('input-wrong');
			$("#wrong-pw").html("密码长度在5和16之间");
			$("#wrong-pw").show();
			return false;
		}
		//ajax提交检测
		$.ajax({
			type: "POST",
			async:false,
			dataType: "json",
			url: checkLoginURL,
			data: "username=" + username + "&password=" + password + "&remember=" + remember,
			success: function(data){
				if (data.codeStatus == -2) {
					$("#username").addClass('input-wrong');
					$("#wrong-username").html(data.errorMsg);
					$("#wrong-username").show();	//用户名不存在
				} else if (data.codeStatus == -1) {	
					$("#password").addClass('input-wrong');
					$("#wrong-pw").html(data.errorMsg);
					$("#wrong-pw").show();	//密码错误
				} else if (data.codeStatus == 0){
					window.location.href = ref;
				}
			} 
		})
	})
</script>
</html>