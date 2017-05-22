<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>告警日志</title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/reset.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/tabpage.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/jquery.ui.theme.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/bootstrap/css/bootstrap.min.css">
<script src="<?php echo base_url(); ?>application/views/js/jquery-1.9.1.js"></script>
<script src="<?php echo base_url(); ?>application/views/js/tabpage.js"></script>
<script src="<?php echo base_url(); ?>application/views/js/jquery-ui-1.9.2.custom.js"></script>
<script src="<?php echo base_url(); ?>application/views/bootstrap/js/bootstrap.min.js"></script>
<script src="<?php echo base_url(); ?>application/views/fusioncharts/fusioncharts.js"></script>
<script src="<?php echo base_url(); ?>application/views/fusioncharts/fusioncharts.charts.js"></script>
<style>
  .cantsee{
    display: none;
  }
</style>

<script type="text/javascript">
// JavaScript Document
//检查日期格式
function isDate(str){  
    if(str.length == 0) return true;
    var reg=/^(?:(?!0000)[0-9]{4}-(?:(?:0[1-9]|1[0-2])-(?:0[1-9]|1[0-9]|2[0-8])|(?:0[13-9]|1[0-2])-(?:29|30)|(?:0[13578]|1[02])-31)|(?:[0-9]{2}(?:0[48]|[2468][048]|[13579][26])|(?:0[48]|[2468][048]|[13579][26])00)-02-29)$/;  
    if (reg.test(str)) return true;  
    return false;  
}
function getUrlParam(name)
{
    var reg = new RegExp("(^|\\?|&)"+ name +"=([^&]*)(\\s|&|$)", "i");  
    if (reg.test(location.href)) return unescape(RegExp.$2.replace(/\+/g, " ")); return "";
}
//分析url
function parseURL(url) 
{
    var a = document.createElement('a');
    a.href = url;
    return {
        source: url,
        protocol: a.protocol.replace(':', ''),
        host: a.hostname,
        port: a.port,
        query: a.search,
        params: (function () {
            var ret = {},
            seg = a.search.replace(/^\?/, '').split('&'),
            len = seg.length, i = 0, s;
            for (; i < len; i++) {
                if (!seg[i]) { continue; }
                s = seg[i].split('=');
                ret[s[0]] = s[1];
            }
            return ret;
 
        })(),
        file: (a.pathname.match(/\/([^\/?#]+)$/i) || [, ''])[1],
        hash: a.hash.replace('#', ''),
        path: a.pathname.replace(/^([^\/])/, '/$1'),
        relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [, ''])[1],
        segments: a.pathname.replace(/^\//, '').split('/')
    };
}
 
//替换myUrl中的同名参数值
function replaceUrlParams(myUrl, newParams) { 
    for (var x in newParams) {
        var hasInMyUrlParams = false;
        for (var y in myUrl.params) {
            if (x.toLowerCase() == y.toLowerCase()) {
                myUrl.params[y] = newParams[x];
                hasInMyUrlParams = true;
                break;
            }
        }
        //原来没有的参数则追加
        if (!hasInMyUrlParams) {
            myUrl.params[x] = newParams[x];
        }
    }
    var _result = myUrl.protocol + "://" + myUrl.host + ":" + myUrl.port + myUrl.path + "?";
 
    for (var p in myUrl.params) {
        _result += (p + "=" + myUrl.params[p] + "&");
    }
 
    if (_result.substr(_result.length - 1) == "&") {
        _result = _result.substr(0, _result.length - 1);
    }
 
    if (myUrl.hash != "") {
        _result += "#" + myUrl.hash;
    }
    return _result;
}

//JQueryUI Date 中文替换 
jQuery(function($){ 
    $.datepicker.regional['zh-CN'] = { 
        clearText: '清除', 
        clearStatus: '清除已选日期', 
        closeText: '关闭', 
        closeStatus: '不改变当前选择', 
        prevText: '<上月', 
        prevStatus: '显示上月', 
        prevBigText: '<<', 
        prevBigStatus: '显示上一年', 
        nextText: '下月>', 
        nextStatus: '显示下月', 
        nextBigText: '>>', 
        nextBigStatus: '显示下一年', 
        currentText: '今天', 
        currentStatus: '显示本月', 
        monthNames: ['一月','二月','三月','四月','五月','六月', '七月','八月','九月','十月','十一月','十二月'], 
        monthNamesShort: ['一','二','三','四','五','六', '七','八','九','十','十一','十二'], 
        monthStatus: '选择月份', 
        yearStatus: '选择年份', 
        weekHeader: '周', 
        weekStatus: '年内周次', 
        dayNames: ['星期日','星期一','星期二','星期三','星期四','星期五','星期六'], 
        dayNamesShort: ['周日','周一','周二','周三','周四','周五','周六'], 
        dayNamesMin: ['日','一','二','三','四','五','六'], 
        dayStatus: '设置 DD 为一周起始', 
        dateStatus: '选择 m月 d日, DD', 
        dateFormat: 'yy-mm-dd', 
        firstDay: 1, 
        initStatus: '请选择日期', 
        isRTL: false}; 
        $.datepicker.setDefaults($.datepicker.regional['zh-CN']); 
});

$(function(){
    $( "#from" ).datepicker({
        dataFormat: "yy-mm-dd",
        //defaultDate: "+1w",
        changeMonth: true,
        onClose: function( selectedDate ) {
            $( "#to" ).datepicker( "option", "minDate", selectedDate );
        }
    });
    $( "#to" ).datepicker({
        dataFormat: "yy-mm-dd",
        //defaultDate: "+1w",
        changeMonth: true,
        onClose: function( selectedDate ) {
            $( "#from" ).datepicker( "option", "maxDate", selectedDate );
        }
    });
})

$(document).ready(function(){
	$(".center-page").css("display", "none");
	$("#page0").css("display", "block");
	//如果url中有值，则给input赋初值
	var sttime = getUrlParam('sttime');
	var edtime = getUrlParam('edtime');
	var as_num = getUrlParam('as');
    if (as_num) {
        $("#as_num").val(as_num);
    }
	if (sttime) {
	  $("#from").val(sttime);
	} 
	if (edtime) {
	  $("#to").val(edtime);
	}


	$("#submit").click(function(){  // 提交的内容
	  //检查是否有选择起止时间
      var as_num = $("#as_num").val();
	  var sttime = $("#from").val();
	  var edtime = $("#to").val();

	  //检查日期格式
        if (isDate(sttime) && isDate(edtime)) {
            var curUrl = parseURL(window.location.href);
            var newUrl = replaceUrlParams(curUrl, { c: 'index', m: "prefixQuery", as_num:as_num, sttime: sttime, edtime:edtime, page:1 });
//            window.location.href = newUrl;

            $.ajax({
 			type: "GET",
 			async:false,
 			url: "<?php echo base_url(); ?>index.php/index/prefixQueryAjax",
 			data: "as_num=" + as_num + "&start_time=" + sttime + "&end_time=" + edtime,
 			success: function(data){
 			    var d = JSON.parse(data);
 			    console.log(d.prefix);
// 				if (data == "ok") {
//// 					$(buttonobj).parent().html("已处理");
                    console.log("success");
// 				}
                console.log(data);
 			}
 		})
        } else {
            alert("非法的日期格式！请重新填写！");
            return false;
        }
	    }
    );


  // 加入初始时只显示表格
  // $("#page0").hide();

  // 加入按钮监听事件
  var btn = document.getElementById("switch"); 
  btn.onclick = function(){ 
    var bt_text = btn.textContent;
    if(bt_text == "切换到图表"){
        btn.textContent = "切换到表格";
        $("#chart0").toggle();
        $("#page0").toggle();
    } else {
        btn.textContent = "切换到图表"
        $("#page0").toggle();
        $("#chart0").toggle();
    }
  }   
  
  //统计图 - 信息预处理（时间 和 数量）
  


})

// function process_warning(domain, id, buttonobj) {
// 	if (confirm("确定要将该条告警置为已处理？")) {
// 		$.ajax({
// 			type: "GET",
// 			async:false,
// 			url: "<?php echo base_url(); ?>index.php/interface_common/update_warning_solved",
// 			data: "domain=" + domain + "&id=" + id,
// 			success: function(data){
// 				if (data == "ok") {
// 					$(buttonobj).parent().html("已处理");
// 				} else {
// 					alert("处理失败！");
// 				}
// 			} 
// 		})
// 	}
// }

// fusion chart
FusionCharts.ready(function () {
    var revenueChart = new FusionCharts({
        type: 'column2d',
        renderAt: 'chart0',
        width: '95%',
        height: '80%',
        dataFormat: 'json',
        dataSource: {
              "chart": {
                "palette": "3",
                "caption": "Worldwide sales report of mobile devices",
                "subcaption": "Samsung & Nokia",
                "yaxisname": "Sales in million units",
                "plotgradientcolor": " ",
                "numbersuffix": "M",
                "showvalues": "0",
                "divlinealpha": "30",
                "labelpadding": "10",
                "plottooltext": "$seriesnameYear :  $label Sales : $datavalue",
                "legendborderalpha": "0",
                "showborder": "0",
            },
            "categories": [
                {
                    "category": [
                        {
                            "label": "2010",
                            "stepSkipped": false
                        },
                        {
                            "label": "2011",
                            "stepSkipped": false
                        },
                        {
                            "label": "2012",
                            "stepSkipped": false
                        },
                        {
                            "label": "2013",
                            "stepSkipped": false
                        },
                        {
                            "label": "2014",
                            "stepSkipped": false
                        },
                        {
                            "label": "2015",
                            "stepSkipped": false
                        },
                        {
                            "label": "2016(Projected)",
                            "stepSkipped": false
                        }
                    ]
                }
            ],
            "dataset": [
                {
                    "seriesname": "Samsung",
                    "color": "A66EDD",
                    "data": [
                        {
                            "value": "281.07"
                        },
                        {
                            "value": "315.05"
                        },
                        {
                            "value": "384.63"
                        },
                        {
                            "value": "444.45"
                        },
                        {
                            "value": "405.94"
                        },
                        {
                            "value": "401.37"
                        },
                        {
                            "value": "390.76",
                            "dashed": "1"
                        }
                    ]
                },
                {
                    "seriesname": "Nokia/Microsoft",
                    "color": "F6BD0F",
                    "data": [
                        {
                            "value": "461.32"
                        },
                        {
                            "value": "422.48"
                        },
                        {
                            "value": "333.93"
                        },
                        {
                            "value": "250.81"
                        },
                        {
                            "value": "179.38"
                        },
                        {
                            "value": "126.61"
                        },
                        {
                            "value": "95.85",
                            "dashed": "1"
                        }
                    ]
                }
            ]
        }
    }).render();
});

</script>

</head>

<body>
  <div class="layout">
      <div>
          <ul>
          <?php foreach ($as as $t) { ?>  <!--从后台读数据-->
              <li><?php echo $t?></li>
          <?php } ?>
          </ul>
      </div>

  	<div class="layout-left">
      <div class="layout-header">
        <div class="layout-header-toggle"></div>
        <div class="layout-header-inner">前缀信息查询</div>
      </div>
      <div class="layout-content accordion-panel">
      	<div class="query-limit">
      		自治域
            <select name="as_num" id="as_num">
<!--                <option value=1>全部</option>-->
<!--                <option value=-1>自治域A</option>-->
<!--                <option value=2>自治域B</option>-->
<!--                <option value=3>自治域E</option>-->
<!--                <option value=4>自治域C</option>-->
<!--                <option value=5>自治域D</option>-->
                <option value=-1>全部</option>
                <?php foreach ($as as $t) { ?>
                    <option value=<?=$t?>><?=$t?></option>
                <?php }?>
            </select>
      	</div>
      	<!--<div class="query-limit">
      		告警等级：<select name="level" id="level"><option value=-1>全部</option><option value=1>严重</option><option value=2>轻微</option></select>
      	</div>
      	<div class="query-limit">
      		处理情况：<select name="solved" id="solved"><option value=-1>全部</option><option value=0>未处理</option><option value=1>已处理</option></select>
      	</div>-->
      	<div class="query-limit">时间选择：</div>
      	<div class="query-limit">
      		从：<input type="text" id="from" name="from" />
      	</div>
      	<div class="query-limit">
      		到：<input type="text" id="to" name="to" />
      	</div>
      	<div class="query-limit">
      		<button id="submit">查看信息</button>
      	</div>
        <!--<div class="query-limit">
          <button id="solve-submit">处理全部告警</button>
        </div>-->
      </div>
    </div>

    <div class="layout-center">
      <div class="query-limit" style="float: right; padding-right: 40px">
      		<button class="btn btn-default" id="switch">切换到图表</button>
      </div>

    	<div id="page0" class="center-page">
    		<div id="pagination"><?php echo $this->pagination->create_links(); ?></div>
    		<table id="warning-list" class="table-list">
    			<tr class="table-list-title">
                    <td class="warning-list-id">AS</td>
                    <td class="warning-list-time">这是前缀</td>
                    <td class="warning-list-name">创建时间</td>
                    <td class="warning-list-class">结束时间</td>
    			</tr>
    			<tbody class="tbody-list">
                <?php foreach ($prefix as $log) { ?>  <!--从后台读数据-->
                    <tr>
                        <td><?=$log['AS']?></td>
                        <td><?=$log['prefix']?></td>
                        <td><?=date("Y-m-d H:i:s", $log['create_time']);?></td>
<!--                        <td>--><?//=function($log){
//                            if($log['end_time']==9999999999){
//                                return date("Y-m-d H:i:s", $log['end_time']);}
//                            else{
//                                return '----';
//                            }
//                        }?><!--</td>-->
                        <td><?=($log['end_time']==9999999999) ? date("Y-m-d H:i:s", $log['end_time']) : '----';?></td>
                    </tr>
                <?php } ?>
          </tbody>
    		</table>
    	</div>

      <div id="chart0" style="padding-left: 30px; width: 100%; height:100%" class="cantsee center-page"><!--图表-->
        FusionCharts will render here
      </div>
            

    </div> 
    <div class="layout-collapse-left" style="display:none">
	    <div class="layout-collapse-left-toggle"></div>
	  </div>

	  <div class="footer"></div>
  </div>
</body>

</html>
