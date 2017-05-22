<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>告警日志</title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/reset.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/tabpage.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/jquery.ui.theme.css">
<script src="<?php echo base_url(); ?>application/views/js/jquery-1.9.1.js"></script>
<script src="<?php echo base_url(); ?>application/views/js/tabpage.js"></script>
<script src="<?php echo base_url(); ?>application/views/js/jquery-ui-1.9.2.custom.js"></script>
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
	var warning_name = getUrlParam('warning_name');
	var level = getUrlParam('level');
	var solved = getUrlParam('solved');
	if (sttime) {
	  $("#from").val(sttime);
	} 
	if (edtime) {
	  $("#to").val(edtime);
	}
	if (warning_name > -1) {
		$("#warning_name option[value=" +warning_name + "]").attr("selected",'selected');
	}
	if (level > -1) {
		$("#level option[value=" +level + "]").attr("selected",'selected');
	}
	if (solved > -1) {
		$("#solved option[value=" +solved + "]").attr("selected",'selected');
	}
	$("#submit").click(function(){
	  //检查是否有选择起止时间
	  sttime = $("#from").val();
	  edtime = $("#to").val();
	  warning_name = $("#warning_name").val();
    level = $("#level").val();
	  solved = $("#solved").val();
	  //检查日期格式
    if (isDate(sttime) && isDate(edtime)) {
      var curUrl = parseURL(window.location.href);
      var newUrl = replaceUrlParams(curUrl, { c: 'index', m: "warninglog", sttime: sttime, edtime:edtime, warning_name:warning_name, level:level, solved:solved, page:1 });
      window.location.href = newUrl;
    } else {
      alert("非法的日期格式！请重新填写！");
      return false;
    }
	})
  $("#solve-submit").click(function(){
    if (confirm("确定要将所有告警置为已处理？")) {
      $.ajax({
        type: "GET",
        async:false,
        url: "<?php echo base_url(); ?>index.php/interface_common/update_warning_solved",
        data: "domain=" + -1 + "&id=" + -1,
        success: function(data){
          if (data == "ok") {
            $(".solve-state").html("已处理");
          } else {
            alert("处理失败！");
          }
        }
      })  
    }
  })
})

function process_warning(domain, id, buttonobj) {
	if (confirm("确定要将该条告警置为已处理？")) {
		$.ajax({
			type: "GET",
			async:false,
			url: "<?php echo base_url(); ?>index.php/interface_common/update_warning_solved",
			data: "domain=" + domain + "&id=" + id,
			success: function(data){
				if (data == "ok") {
					$(buttonobj).parent().html("已处理");
				} else {
					alert("处理失败！");
				}
			} 
		})
	}
}
</script>
</head>

<body>
  <div class="layout">

  	<div class="layout-left">
      <div class="layout-header">
        <div class="layout-header-toggle"></div>
        <div class="layout-header-inner">告警日志</div>
      </div>
      <div class="layout-content accordion-panel">
      	<div class="query-limit">
      		告警名称：<select name="warning_name" id="warning_name"><option value=-1>全部</option><option value=1>路由路径丢失</option><option value=2>路由路径新增</option><option value=3>路由协议邻居丢失</option><option value=4>路由协议邻居新增</option><option value=5>路由路径Metric值变化</option></select>
      	</div>
      	<div class="query-limit">
      		告警等级：<select name="level" id="level"><option value=-1>全部</option><option value=1>严重</option><option value=2>轻微</option></select>
      	</div>
      	<div class="query-limit">
      		处理情况：<select name="solved" id="solved"><option value=-1>全部</option><option value=0>未处理</option><option value=1>已处理</option></select>
      	</div>
      	<div class="query-limit">时间选择：</div>
      	<div class="query-limit">
      		从：<input type="text" id="from" name="from" />
      	</div>
      	<div class="query-limit">
      		到：<input type="text" id="to" name="to" />
      	</div>
      	<div class="query-limit">
      		<button id="submit">查看日志</button>
      	</div>
        <div class="query-limit">
          <button id="solve-submit">处理全部告警</button>
        </div>
      </div>
    </div>

    <div class="layout-center">
    	<div id="page0" class="center-page">
    		<div id="pagination"><?php echo $this->pagination->create_links(); ?></div>
    		<table id="warning-list" class="table-list">
    			<tr class="table-list-title">
    				<td class="warning-list-id">告警id</td>
            <td class="warning-list-time">告警时间</td>
            <td class="warning-list-name">告警名称</td>
            <td class="warning-list-class">告警类型</td>
            <td class="warning-list-level">告警等级</td>
            <td class="warning-list-text">描述</td>
            <td class="warning-list-handleinfo">处理建议</td>
            <td class="warning-list-parsetime">发现时长(ms)</td>
            <td class="warning-list-snmptime">snmp上报时长(ms)</td>
            <td class="warning-list-option">处理情况</td>
    			</tr>
    			<tbody class="tbody-list">
	          <?php foreach ($warninglogs as $log) { ?>
            <tr>
                <td><?=$log['id']?></td>
                <td><?=date('Y-m-d H:i:s', $log['timestamp'])?></td>
                <td><?=$log['name']?></td>
                <td><?=$log['class']==1?"路由异常告警":"路由配置情况监测"?></td>
                <td><?=$log['level']==1?"严重":"轻微"?></td>
                <td><?=$log['text']?></td>
                <td><?=$log['handleinfo']?></td>
                <td><?=$log['parse_time']?></td>
                <td><?=$log['snmp_time']?></td>    
                <td class="solve-state"><?php if ($log['solved']==1) echo "已处理"; else echo "<input type='button' class='opt-button' value='处理' onclick=process_warning(".$log['domain'].",".$log['id'].",this)>"; ?></td>
            </tr>
            <?php } ?>
          </tbody>
    		</table>
    	</div>
    </div> 

    <div class="layout-collapse-left" style="display:none">
	    <div class="layout-collapse-left-toggle"></div>
	  </div>

	  <div class="footer"></div>
  </div>
</body>

</html>
