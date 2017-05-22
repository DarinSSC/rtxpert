<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>路由流量综合监测系统</title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/reset.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/index.css">
<script type="text/javascript" src="<?php echo base_url(); ?>application/views/js/jquery-1.9.1.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>application/views/js/Silverlight.js"></script>
<script type="text/javascript">
<?php $curIp = explode("/", base_url());?>
Date.prototype.toLocaleString = function() {
  return this.getFullYear() + "年" + (this.getMonth() + 1) + "月" + this.getDate() + "日 " + this.getHours() + "点" + this.getMinutes() + "分" + this.getSeconds() + "秒";
};
function onSilverlightError(sender, args) {
    
    var appSource = "";
    if (sender != null & sender != 0) {
      appSource = sender.getHost().Source;
    }
    
    var errorType = args.ErrorType;
    var iErrorCode = args.ErrorCode;

    if (errorType == "ImageError" || errorType == "MediaError") {
      return;
    }

    var errMsg = "Silverlight 应用程序中未处理的错误" +  appSource + "\n" ;

    errMsg += "代码: "+ iErrorCode + "    \n";
    errMsg += "类别: " + errorType + "       \n";
    errMsg += "消息: " + args.ErrorMessage + "     \n";

    if (errorType == "ParserError") {
        errMsg += "文件: " + args.xamlFile + "     \n";
        errMsg += "行: " + args.lineNumber + "     \n";
        errMsg += "位置: " + args.charPosition + "     \n";
    }
    else if (errorType == "RuntimeError") {           
        if (args.lineNumber != 0) {
            errMsg += "行: " + args.lineNumber + "     \n";
            errMsg += "位置: " +  args.charPosition + "     \n";
        }
        errMsg += "方法名称: " + args.methodName + "     \n";
    }

    throw new Error(errMsg);
}
</script>
<script type="text/javascript">
var logoutUrl = "<?php echo base_url(); ?>index.php/interface_common/logout";
var jumpUrl = "<?php echo base_url(); ?>index.php/index/login";
var slCtl = null;	//silverlight控件对象
var g_width = document.documentElement.clientWidth;
//var g_width = window.innerWidth;
var g_height = document.documentElement.clientHeight;
var g_frameheight = g_height - 32 - 36 - 2;
//alert(g_frameheight);
</script>
</head>

<body>
	<div class="header">
		<div class="logo">路由流量综合监测系统</div>
		<div class="option">当前用户：<?php echo $this->_userdata["username"]; ?><span>|</span><a onclick="logout()">退出</a></div>
	</div>

	<div class="navbar">
		<ul class="nav">
			<li class="active"><a>路由拓扑</a></li>
			<li><a>流量拓扑</a></li>
			<li><a>告警日志</a></li>
			<li><a>系统日志</a></li>
			<li><a>设备监测</a></li>
			<li><a>报表分析</a></li>
			<li><a>历史回顾</a></li>
			<li><a>用户权限</a></li>
			<li><a>系统配置</a></li>
			<li><a>使用帮助</a></li>
			<li><a>域间拓扑</a></li>
			<li><a>前缀信息统计</a></li>
		</ul>
		<div id="time">2013.08.29 12:11:21</div>
	</div>

	<!-- silverlight控件 -->
	<div id="silverlight" class="tabpage" >
		<object data="data:application/x-silverlight-2," type="application/x-silverlight-2" width="100%" height="100%">
			<param name="source" value="<?php echo base_url(); ?>application/views/ClientBin/DzxSL.xap"/>
			<param name="onerror" value="onSilverlightError" />
			<param name="background" value="white" />
			<param name="minRuntimeVersion" value="3.0.40818.0" />
			<param name="autoUpgrade" value="true" />
			<param name="onLoad" value="pluginLoaded" />
			<a href="http://go.microsoft.com/fwlink/?LinkID=149156&v=3.0.40818.0" style="text-decoration: none;">
     			<img src="http://go.microsoft.com/fwlink/?LinkId=108181" alt="获取 Microsoft Silverlight" style="border-style: none"/>
			</a>
		</object><iframe id="_sl_historyFrame" style='visibility:hidden;height:0;width:0;border:0px'></iframe>
	</div>

	<!-- 54所2个页面 -->
	<iframe id="page5" class="tabpage" frameborder="0" src="http://<?php echo $curIp[2]; ?>:8080/jrta/jsp/report/index.jsp?u_dptid=<?=$this->_userdata["dpt"]?>" ><!--http://localhost:8080/jrta/jsp/report/index.jsp-->
	</iframe>
	<iframe id="page4" class="tabpage" frameborder="0" src="http://<?php echo $curIp[2]; ?>:8080/jrta/jsp/device/index.jsp?u_dptid=<?=$this->_userdata["dpt"]?>" ><!--http://localhost:8080/jrta/jsp/device/index.jsp-->
	</iframe>

	<!-- 计算所4个页面 -->
	<iframe id="page2" class="tabpage" frameborder="0" src="<?php echo base_url(); ?>index.php?c=index&m=warninglog" >
	</iframe>
	<iframe id="page3" class="tabpage" frameborder="0" src="<?php echo base_url(); ?>index.php?c=index&m=syslog" >
	</iframe>
	<iframe id="page7" class="tabpage" frameborder="0" src="<?php echo base_url(); ?>index.php/index/usermanager" >
	</iframe>
	<iframe id="page8" class="tabpage" frameborder="0" src="<?php echo base_url(); ?>index.php/index/sysconfig" >
	</iframe>
	<iframe id="page9" class="tabpage" frameborder="0" src="<?php echo base_url(); ?>application/views/file/userguide.pdf" >
	</iframe>

	<!-- 前缀信息查询页面 -->
	<iframe id="page11" class="tabpage" frameborder="0" src="<?php echo base_url(); ?>index.php?c=index&m=prefixQuery" >
	</iframe>

</body>

<script type="text/javascript">
//高度宽度控制
$(".tabpage").css("width", g_width);
$(".tabpage").css("height", g_frameheight);
//让页面显示为silverlight页面1
$(".tabpage").addClass("tabpage-hidden");
$("#silverlight").removeClass("tabpage-hidden");

//实时时间显示
showtime();
//为导航条绑定点击事件
//$(".nav").children.each(function(i, n){
$.each($(".nav").children(), function(i, n){
	$(n).attr("id", "tab" + i);
	$(n).bind('click', function(){
		//去掉active属性
		$(".nav > .active").removeClass("active");
		//添加active属性
		$(this).addClass("active");
		//切换display属性
		var index = parseInt($(this).attr("id").slice(3));	//截取tab之后的数字
		if (index == 10){  // 域间拓扑 silverlight
			$(".tabpage").addClass("tabpage-hidden");
			$("#silverlight").removeClass("tabpage-hidden");
			index = 0;
			slCtl.Content.SLapp.SetPage(index + 1);
		} else if (index == 0 || index == 1 || index == 6) {	//silverlight页面
			$(".tabpage").addClass("tabpage-hidden");
			$("#silverlight").removeClass("tabpage-hidden");
			if(index == 6) slCtl.Content.SLapp.SetPage(3);
			else slCtl.Content.SLapp.SetPage(index + 1);
		} else {  // 否则就是正常的html界面
			$(".tabpage").addClass("tabpage-hidden");
			$("#page" + index).removeClass("tabpage-hidden");
		}
	})
})    

function showtime() {
	var now = new Date();
	$("#time").html(now.toLocaleString());
	setTimeout("showtime()", 1000);
}

function logout() {
	if (confirm("确定要退出系统吗？")) {
		$.ajax({
			type: "GET",
			async:false,
			dataType: "json",
			url: logoutUrl,
			success: function(data){
				if (data.codeStatus == 0) {
					window.location.href = jumpUrl;	//跳转回登陆页
				} else {
					alert(data.errorMsg);
				}
			} 
		})
	}
}

function pluginLoaded(sender, args) {
    slCtl = sender.getHost();      
    //根据body工作区的大小初始SL的工作区尺寸
    slCtl.Content.SLapp.InitSL(g_width, g_frameheight);
}

</script>

</html>