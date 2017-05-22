var left_width = 250;
var window_width;
var window_height;

$(document).ready(function(){
	
	window_width = parent.g_width;
	window_height = parent.g_frameheight;

	$(".layout").height(window_height - 36);
	$(".layout-left").height(window_height - 36);
	$(".layout-center").height(window_height - 36);
	$(".layout-collapse-left").height(window_height - 36);
	$(".layout-left").width(left_width);
	$(".layout-center").css("left", left_width + 8);
	$(".layout-center").width(window_width - left_width - 22);

	//控制hover
	$(".layout-header").mouseover(function(){
		$(this).addClass("layout-header-over");
	});
	$(".layout-header").mouseout(function(){
		$(this).removeClass("layout-header-over");
	});

	$(".layout-header-toggle").mouseover(function(){
		$(this).addClass("layout-header-toggle-over");
	});
	$(".layout-header-toggle").mouseout(function(){
		$(this).removeClass("layout-header-toggle-over");
	});

	$(".layout-collapse-left").mouseover(function(){
		$(this).addClass("layout-collapse-left-over");
	});
	$(".layout-collapse-left").mouseout(function(){
		$(this).removeClass("layout-collapse-left-over");
	});

	$(".layout-collapse-left-toggle").mouseover(function(){
		$(this).addClass("layout-collapse-left-toggle-over");
	});
	$(".layout-collapse-left-toggle").mouseout(function(){
		$(this).removeClass("layout-collapse-left-toggle-over");
	});

	//控制左侧菜单折叠
	$(".layout-header-toggle").click(function(){
		$(".layout-left").css("display","none");
		$(".layout-collapse-left").css("display", "block");
		$(".layout-center").css("left", 34);
		$(".layout-center").width(window_width - 50);
	});
	$(".layout-collapse-left-toggle").click(function(){
		$(".layout-left").css("display","block");
		$(".layout-collapse-left").css("display", "none");
		$(".layout-center").css("left", left_width + 8);
		$(".layout-center").width(window_width - left_width - 22);
	});

	//为左菜单面板导航添加click事件控制center-page的显隐
	$.each($(".accordion-panel ul").children(), function(i, n){
		$(n).attr("id", "menu" + i);
		$(n).bind('click', function(){
			//去掉active属性
			$(".accordion-panel .menu-active").removeClass("menu-active");
			//添加active属性
			$(this).addClass("menu-active");
			//切换display属性
			var index = parseInt($(this).attr("id").slice(4));	//截取menu之后的数字
			$(".center-page").css("display", "none");
			$("#page" + index).css("display", "block");
		})
	}) 

})
