<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>系统配置</title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/reset.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/tabpage.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/jquery.ui.theme.css">
<script src="<?php echo base_url(); ?>application/views/js/jquery-1.9.1.js"></script>
<script src="<?php echo base_url(); ?>application/views/js/tabpage.js"></script>
<script src="<?php echo base_url(); ?>application/views/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript">
var modifycfgurl = "<?php echo base_url(); ?>index.php/interface_common/modifyConfig";
var ipReg = /^(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9])\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[1-9]|0)\.(25[0-5]|2[0-4][0-9]|[0-1]{1}[0-9]{2}|[1-9]{1}[0-9]{1}|[0-9])$/;
var maskReg = /^(254|252|248|240|224|192|128|0)\.0\.0\.0$|^(255\.(254|252|248|240|224|192|128|0)\.0\.0)$|^(255\.255\.(254|252|248|240|224|192|128|0)\.0)$|^(255\.255\.255\.(254|252|248|240|224|192|128|0))$/;
var intReg = /^\d+$/;
//var sysIdReg = /^[\s\S]$/;

$(document).ready(function(){
  $(".center-page").css("display", "none");
  $("#page0").css("display", "block");
  <?php if ($sysconfig->localSet->protocol == "ospf"): ?>
  $('#protocol')[0].selectedIndex=0;
  $("#asli").css("display", "block");
  $("#areali").css("display", "none");
  $("#ospf-border").css("display", "block");
  $("#isis-border").css("display", "none");
  $("#hqbox").css("display", "block");
  <?php if ($sysconfig->localSet->isHQ == 1): ?>
    $("#menu4").css("display", "block");
  <?php else: ?>
    $("#menu4").css("display", "none");
  <?php endif; ?>
  <?php else: ?>
  $('#protocol')[0].selectedIndex=1;
  $("#asli").css("display", "none");
  $("#areali").css("display", "none");
  $("#ospf-border").css("display", "none");
  $("#isis-border").css("display", "none");
  $("#hqbox").css("display", "none");
  $("#menu4").css("display", "none");
  <?php endif; ?>
  $('#interval')[0].selectedIndex=<?=$sysconfig->localSet->interval?> - 1;
  //控制切换协议
  $("#protocol").change(function(){
    if ($("select option:selected").val() == "ospf") {
      $("#asli").css("display", "block");
      $("#areali").css("display", "none");
      $("#ospf-border").css("display", "block");
      $("#isis-border").css("display", "none");
      $("#hqbox").css("display", "block");
      if ($("input[name='isHQ']:checked").val() == 1){
        $("#menu4").css("display", "block");
      }else {
        $("#menu4").css("display", "none");
      }
    } else {
      $("#asli").css("display", "none");
      $("#areali").css("display", "none");
      $("#ospf-border").css("display", "none");
      $("#isis-border").css("display", "none");
      $("#hqbox").css("display", "none");
      $("#menu4").css("display", "none");
    }
  })

  $("input[name='isHQ']").click(function(){
    if($(this).val() == 0) {
      $("#menu4").css("display", "none");
    }else {
      $("#menu4").css("display", "block");
    }
  })

  // 协议，是否总部 
  $("#button1").click(function(){
    var json_params = new Object();
    json_params.protocol = $("#protocol").val();
    json_params.asNum = $("#asnum").val();
    json_params.areaId = $("#areaid").val();
    json_params.isHQ = $("input[name='isHQ']:checked").val();
    submitData(1, json_params);
  })
  //interval等本地参数
  $("#button2").click(function(){
    var json_params = new Object();
    json_params.interval = parseInt($("#interval").val());
    json_params.inAdvance = $("#inadvance").val();
    json_params.topN = $("#topn").val();
    json_params.localFlowQueryPort = $("#localflowqueryport").val();
    json_params.baseUrl = $("#baseurl").val();
    json_params.localIp = $("#localip").val();
    submitData(2, json_params);
  })
  //ospf边界链路
  $("#button3").click(function(){
    var json_params = new Array();
    $.each($("#tbody-borderlink-list").children(), function(i, n){
      var bgp_link = new Object();
      bgp_link.bgp_id = parseInt($(n).children(":nth-child(9)").children("input:hidden").val());
      bgp_link.as_num = parseInt($(n).children(":nth-child(1)").html());
      bgp_link.router_id = $(n).children(":nth-child(2)").html();
      bgp_link.interface_ip = $(n).children(":nth-child(3)").html();
      bgp_link.n_as_num = parseInt($(n).children(":nth-child(4)").html());
      bgp_link.n_router_id = $(n).children(":nth-child(5)").html();
      bgp_link.n_interface_ip = $(n).children(":nth-child(6)").html();
      bgp_link.mask = $(n).children(":nth-child(7)").html();
      bgp_link.metric = parseInt($(n).children(":nth-child(8)").html());
      json_params.push(bgp_link);
    });
    submitData(3, json_params);
  })
  //isis level 1/2路由器ip映射
  $("#button3-5").click(function(){
    var json_params = new Array();
    $.each($("#tbody-isis-mapping-list").children(), function(i, n){
      var isis_mapping = new Object();
      isis_mapping.sysId = $(n).children(":nth-child(1)").html();
      isis_mapping.ip = new Array();
      ipsArr = $(n).children(":nth-child(2)").html().split(",");
      $.each(ipsArr, function(i, n) {
        isis_mapping.ip.push(n);
      })
      json_params.push(isis_mapping);
    });
    submitData(35, json_params);
  })
  //监视业务
  $("#button4").click(function(){
    var json_params = new Array();
    $.each($("#tbody-protocol-list").children(), function(i, n){
      var protocol = new Object();
      protocol.protocol = $(n).children(":nth-child(1)").html();
      protocol.protocolNum = $(n).children(":nth-child(2)").html();
      protocol.ports = new Array();
      portsArr = $(n).children(":nth-child(3)").html().split(",");
      $.each(portsArr, function(i, n) {
        protocol.ports.push(parseInt(n));
      })
      json_params.push(protocol);
    });
    submitData(4, json_params);
  })
  //数据库配置
  $("#button5").click(function(){
    var json_params = new Object();
    json_params.dbip = $("#dbip").val();
    json_params.dbport = $("#dbport").val();
    json_params.dbname = $("#dbname").val();
    json_params.dbuser = $("#dbuser").val();
    json_params.dbpw = $("#dbpw").val();
    submitData(5, json_params);
  })
  //综合分析设备ip和端口
  $("#button6").click(function(){
    var json_params = new Object();
    json_params.globalAnalysisIp = $("#globalanalysisip").val();
    json_params.globalAnalysisPort = $("#globalanalysisport").val();
    submitData(6, json_params);
  })
  //流量汇集设备参数
  $("#button7").click(function(){
    var json_params = new Object();
    json_params.flowDeviceConfigPort = $("#flowdevice_config_port").val();
    json_params.flowDeviceTopoPort = $("#flowdevice_topo_port").val();
    json_params.flowDeviceFlowPort = $("#flowdevice_flow_port").val();
    json_params.flowDeviceSamplingRate = $("#flowdevice_samplingrate").val();
    submitData(7, json_params);
  })
  //流量汇集设备列表
  $("#button8").click(function(){
    var json_params = new Array();
    $.each($("#tbody-flowdevices-list").children(), function(i, n){
      var dev = new Object();
      dev.devIp = $(n).children(":nth-child(1)").html();
      dev.devId = $(n).children(":nth-child(2)").html();
      dev.devName = $(n).children(":nth-child(3)").html();
      json_params.push(dev);
    });
    submitData(8, json_params);
  })
  //汇报设备地址
    $("#button11").click(function(){
    var json_params = new Object();
    json_params.url_Level1 = $("#url_Level1").val();
    json_params.url_Level2 = $("#url_Level2").val();
    submitData(11, json_params);
  })
  //样式自定义
  $("#button9").click(function(){
    var json_params = new Object();
    json_params.canvasWidth = $("#canvas_width").val();
    json_params.canvasHeight = $("#canvas_height").val();
    submitData(9, json_params);
  })
  //分部配置
  $("#button10").click(function(){
    var json_params = new Array();
    $.each($("#tbody-fb-list").children(), function(i, n){
      var fb = new Object();
      fb.fbname = $(n).children(":nth-child(1)").html();
      fb.fbip = $(n).children(":nth-child(2)").html();
      json_params.push(fb);
    });
    submitData(10, json_params);
  })
})

///////////////////////////////////////////////////////////////////////

/*-----Add Border Link Control-----*/    
$(function(){
  //获得表单中的对象引用
  var as = $( "#a_as" ),
  router_id = $( "#a_router_id" ),
  interface_ip = $( "#a_interface_ip" ),
  n_as_num = $( "#a_n_as_num" ),
  n_router_id = $( "#a_n_router_id" ),
  n_interface_ip = $( "#a_n_interface_ip" ),
  mask = $( "#a_mask" ),
  metric = $( "#a_metric" ),
  allFields = $( [] ).add( as ).add( router_id ).add( interface_ip ).add( n_as_num ).add( n_router_id ).add( n_interface_ip ).add( mask ).add( metric ),
  tips = $( "#add-borderlink-tips" );

  //更新提示信息
  function updateTips( t ) {
      tips
      .text( t )
      .addClass( "ui-state-highlight" );
      setTimeout(function() {
          tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
  }

  //正则检查
  function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
          o.addClass( "ui-state-error" );
          updateTips( n );
          return false;
      } else {
          return true;
      }
  }

  //新增用户dialog定制
  $("#add-borderlink-dialog").dialog({
    autoOpen: false,
    height: 360,
    width: 350,
    modal: true,
    buttons: {
    "保存信息": function() {
      var bValid = true;
      allFields.removeClass( "ui-state-error" );
      //检查
      bValid = bValid && checkRegexp( as, intReg, "AS号必须是数字！" );
      bValid = bValid && checkRegexp( router_id, ipReg, "非法的路由器ID格式！" );
      bValid = bValid && checkRegexp( interface_ip, ipReg, "非法的IP地址格式！" );
      bValid = bValid && checkRegexp( n_as_num, intReg, "AS号必须是数字！" );
      bValid = bValid && checkRegexp( n_router_id, ipReg, "非法的路由器ID格式！" );
      bValid = bValid && checkRegexp( n_interface_ip, ipReg, "非法的IP地址格式！" );
      bValid = bValid && checkRegexp( mask, maskReg, "非法的子网掩码格式！" );
      bValid = bValid && checkRegexp( metric, intReg, "metric值必须是数字！" );
      if ( bValid ) {
        $( "#tbody-borderlink-list" ).append( "<tr>" +
            "<td>" + as.val() + "</td>" +
            "<td>" + router_id.val() + "</td>" +
            "<td>" + interface_ip.val() + "</td>" +
            "<td>" + n_as_num.val() + "</td>" +
            "<td>" + n_router_id.val() + "</td>" +
            "<td>" + n_interface_ip.val() + "</td>" +
            "<td>" + mask.val() + "</td>" +
            "<td>" + metric.val() + "</td>" +
            "<td>" + "<input type='hidden' value=0 /><input type='button' class='opt-button' value='修改' onclick='edit_bgp_link(this)' />&nbsp;<input type='button' class='opt-button' value='删除' onclick='delete_bgp_link(this)' />" + "</td>" +
            "</tr>" );
        $( this ).dialog( "close" ); 
      }
    },
    "取消": function() {
        $( this ).dialog( "close" );
    }
    },
    close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
        tips.text("请填写以下内容：");
    }
  });

  $( "#add_bgp_link" )
      .button()
      .click(function() {
      $( "#add-borderlink-dialog" ).dialog( "open" );
  })
})
/*-----Edit Border Link Control-----*/    
$(function(){
  //获得表单中的对象引用
  var bgplink_nth_tr = $("#e_bgplink_nth_tr");
  var e_as = $( "#e_as" ),
  e_router_id = $( "#e_router_id" ),
  e_interface_ip = $( "#e_interface_ip" ),
  e_n_as_num = $( "#e_n_as_num" ),
  e_n_router_id = $( "#e_n_router_id" ),
  e_n_interface_ip = $( "#e_n_interface_ip" ),
  e_mask = $( "#e_mask" ),
  e_metric = $( "#e_metric" ),
  allFields = $( [] ).add( e_as ).add( e_router_id ).add( e_interface_ip ).add( e_n_as_num ).add( e_n_router_id ).add( e_n_interface_ip ).add( e_mask ).add( e_metric ),
  tips = $( "#edit-borderlink-tips" );

  //更新提示信息
  function updateTips( t ) {
      tips
      .text( t )
      .addClass( "ui-state-highlight" );
      setTimeout(function() {
          tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
  }

  //正则检查
  function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
          o.addClass( "ui-state-error" );
          updateTips( n );
          return false;
      } else {
          return true;
      }
  }

  //修改边界链路dialog定制
  $("#edit-borderlink-dialog").dialog({
    autoOpen: false,
    height: 360,
    width: 350,
    modal: true,
    buttons: {
    "保存信息": function() {
      var bValid = true;
      allFields.removeClass( "ui-state-error" );
      //检查
      bValid = bValid && checkRegexp( e_as, intReg, "AS号必须是数字！" );
      bValid = bValid && checkRegexp( e_router_id, ipReg, "非法的路由器ID格式！" );
      bValid = bValid && checkRegexp( e_interface_ip, ipReg, "非法的IP地址格式！" );
      bValid = bValid && checkRegexp( e_n_as_num, intReg, "AS号必须是数字！" );
      bValid = bValid && checkRegexp( e_n_router_id, ipReg, "非法的路由器ID格式！" );
      bValid = bValid && checkRegexp( e_n_interface_ip, ipReg, "非法的IP地址格式！" );
      bValid = bValid && checkRegexp( e_mask, maskReg, "非法的子网掩码格式！" );
      bValid = bValid && checkRegexp( e_metric, intReg, "metric值必须是数字！" );
      if ( bValid ) {
        nthtr = parseInt(bgplink_nth_tr.val()) + 1;
        editTr = $("#tbody-borderlink-list").children(":nth-child("+nthtr+")");
        editTr.children(":nth-child(1)").html(e_as.val());
        editTr.children(":nth-child(2)").html(e_router_id.val());
        editTr.children(":nth-child(3)").html(e_interface_ip.val());
        editTr.children(":nth-child(4)").html(e_n_as_num.val());
        editTr.children(":nth-child(5)").html(e_n_router_id.val());
        editTr.children(":nth-child(6)").html(e_n_interface_ip.val());
        editTr.children(":nth-child(7)").html(e_mask.val());
        editTr.children(":nth-child(8)").html(e_metric.val());
        $( this ).dialog( "close" ); 
      }
    },
    "取消": function() {
        $( this ).dialog( "close" );
    }
    },
    close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
        tips.text("请填写以下内容：");
    }
  });
})

/*-----Add Isis Mapping Control-----*/    
$(function(){
  //获得表单中的对象引用
  var sys_id = $( "#a_sys_id" ),
  ips = $( "#a_ips" ),
  allFields = $( [] ).add( sys_id ).add( ips ),
  tips = $( "#add-isis-mapping-tips" );

  //更新提示信息
  function updateTips( t ) {
      tips
      .text( t )
      .addClass( "ui-state-highlight" );
      setTimeout(function() {
          tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
  }

  //正则检查
  function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
          o.addClass( "ui-state-error" );
          updateTips( n );
          return false;
      } else {
          return true;
      }
  }

  //新增ISIS level 1/2路由器IP映射dialog定制
  $("#add-isis-mapping-dialog").dialog({
    autoOpen: false,
    height: 200,
    width: 600,
    modal: true,
    buttons: {
    "保存信息": function() {
      var bValid = true;
      allFields.removeClass( "ui-state-error" );
      //检查
      //bValid = bValid && checkRegexp( sys_id, sysIdReg, "sysID不符合格式！" );
      if ( bValid ) {
        $( "#tbody-isis-mapping-list" ).append( "<tr>" +
            "<td>" + sys_id.val() + "</td>" +
            "<td>" + ips.val() + "</td>" +
            "<td>" + "<input type='button' class='opt-button' value='修改' onclick='edit_isis_mapping(this)' />&nbsp;<input type='button' class='opt-button' value='删除' onclick='delete_isis_mapping(this)' />" + "</td>" +
            "</tr>" );
        $( this ).dialog( "close" ); 
      }
    },
    "取消": function() {
        $( this ).dialog( "close" );
    }
    },
    close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
        tips.text("请填写以下内容：");
    }
  });

  $( "#add_l12router" )
      .button()
      .click(function() {
      $( "#add-isis-mapping-dialog" ).dialog( "open" );
  })
})
/*-----Edit Isis Mapping Control-----*/    
$(function(){
  //获得表单中的对象引用
  var e_isis_nth_tr = $("#e_isis_nth_tr");
  var e_sys_id = $( "#e_sys_id" ),
  e_ips = $( "#e_ips" ),
  allFields = $( [] ).add( e_sys_id ).add( e_ips ),
  tips = $( "#edit-isis-mapping-tips" );

  //更新提示信息
  function updateTips( t ) {
      tips
      .text( t )
      .addClass( "ui-state-highlight" );
      setTimeout(function() {
          tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
  }

  //正则检查
  function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
          o.addClass( "ui-state-error" );
          updateTips( n );
          return false;
      } else {
          return true;
      }
  }

  //修改ISIS level 1/2路由器IP映射dialog定制
  $("#edit-isis-mapping-dialog").dialog({
    autoOpen: false,
    height: 200,
    width: 600,
    modal: true,
    buttons: {
    "保存信息": function() {
      var bValid = true;
      allFields.removeClass( "ui-state-error" );
      //检查
      //bValid = bValid && checkRegexp( e_sys_id, sysIdReg, "sysID不符合格式！" );
      if ( bValid ) {
        nthtr = parseInt(e_isis_nth_tr.val()) + 1;
        editTr = $("#tbody-isis-mapping-list").children(":nth-child("+nthtr+")");
        editTr.children(":nth-child(1)").html(e_sys_id.val());
        editTr.children(":nth-child(2)").html(e_ips.val());
        $( this ).dialog( "close" ); 
      }
    },
    "取消": function() {
        $( this ).dialog( "close" );
    }
    },
    close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
        tips.text("请填写以下内容：");
    }
  });
})

/*-----Add Protocol Control-----*/    
$(function(){
  //获得表单中的对象引用
  var protocol_name = $( "#a_protocol_name" ),
  protocol_num = $( "#a_protocol_num"),
  ports = $( "#a_ports" ),
  allFields = $( [] ).add( protocol_name ).add( ports ),
  tips = $( "#add-protocol-tips" );

  //更新提示信息
  function updateTips( t ) {
      tips
      .text( t )
      .addClass( "ui-state-highlight" );
      setTimeout(function() {
          tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
  }

  //正则检查
  function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
          o.addClass( "ui-state-error" );
          updateTips( n );
          return false;
      } else {
          return true;
      }
  }

  //新增监测业务dialog定制
  $("#add-protocol-dialog").dialog({
    autoOpen: false,
    height: 200,
    width: 400,
    modal: true,
    buttons: {
    "保存信息": function() {
      var bValid = true;
      allFields.removeClass( "ui-state-error" );
      //检查
      bValid = bValid && checkRegexp( protocol_name, /^[A-Za-z]+$/, "业务类型需用英文描述！" );
      if ( bValid ) {
        $( "#tbody-protocol-list" ).append( "<tr>" +
            "<td>" + protocol_name.val() + "</td>" +
            "<td>" + protocol_num.find("option:selected").val() + "</td>" +
            "<td>" + ports.val() + "</td>" +
            "<td>" + "<input type='button' class='opt-button' value='修改' onclick='edit_protocol(this)' />&nbsp;<input type='button' class='opt-button' value='删除' onclick='delete_protocol(this)' />" + "</td>" +
            "</tr>" );
        $( this ).dialog( "close" ); 
      }
    },
    "取消": function() {
        $( this ).dialog( "close" );
    }
    },
    close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
        tips.text("请填写以下内容：");
    }
  });

  $( "#add_protocol" )
      .button()
      .click(function() {
      $( "#add-protocol-dialog" ).dialog( "open" );
  })
})
/*-----Edit Protocol Control-----*/    
$(function(){
  //获得表单中的对象引用
  var e_protocol_nth_tr = $("#e_protocol_nth_tr");
  var e_protocol_name = $( "#e_protocol_name" ),
  e_protocol_num = $("#e_protocol_num"),
  e_ports = $( "#e_ports" ),
  allFields = $( [] ).add( e_protocol_name ).add( e_ports ),
  tips = $( "#edit-protocol-tips" );

  //更新提示信息
  function updateTips( t ) {
      tips
      .text( t )
      .addClass( "ui-state-highlight" );
      setTimeout(function() {
          tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
  }

  //正则检查
  function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
          o.addClass( "ui-state-error" );
          updateTips( n );
          return false;
      } else {
          return true;
      }
  }

  //修改边界链路dialog定制
  $("#edit-protocol-dialog").dialog({
    autoOpen: false,
    height: 200,
    width: 400,
    modal: true,
    buttons: {
    "保存信息": function() {
      var bValid = true;
      allFields.removeClass( "ui-state-error" );
      //检查
      bValid = bValid && checkRegexp( e_protocol_name, /^[A-Za-z]+$/, "业务类型需用英文描述！" );
      if ( bValid ) {
        nthtr = parseInt(e_protocol_nth_tr.val()) + 1;
        editTr = $("#tbody-protocol-list").children(":nth-child("+nthtr+")");
        editTr.children(":nth-child(1)").html(e_protocol_name.val());
        editTr.children(":nth-child(2)").html(e_protocol_num.find("option:selected").val());
        editTr.children(":nth-child(3)").html(e_ports.val());
        $( this ).dialog( "close" ); 
      }
    },
    "取消": function() {
        $( this ).dialog( "close" );
    }
    },
    close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
        tips.text("请填写以下内容：");
    }
  });
})

/*-----Add Flow Device Control-----*/    
$(function(){
  //获得表单中的对象引用
  var device_ip = $( "#a_flowdevice_ip" ),
  device_id = $( "#a_flowdevice_id" ),
  device_name = $( "#a_flowdevice_name" ),
  allFields = $( [] ).add( device_ip ).add( device_id ).add( device_name ),
  tips = $( "#add-flowdevice-tips" );

  //更新提示信息
  function updateTips( t ) {
      tips
      .text( t )
      .addClass( "ui-state-highlight" );
      setTimeout(function() {
          tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
  }

  //正则检查
  function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
          o.addClass( "ui-state-error" );
          updateTips( n );
          return false;
      } else {
          return true;
      }
  }

  //新增流量设备dialog定制
  $("#add-flowdevice-dialog").dialog({
    autoOpen: false,
    height: 250,
    width: 400,
    modal: true,
    buttons: {
    "保存信息": function() {
      var bValid = true;
      allFields.removeClass( "ui-state-error" );
      //检查
      bValid = bValid && checkRegexp( device_ip, ipReg, "设备IP地址格式错误！" );
      bValid = bValid && checkRegexp( device_id, intReg, "设备ID必须为正整数！" );
      if ( bValid ) {
        $( "#tbody-flowdevices-list" ).append( "<tr>" +
            "<td>" + device_ip.val() + "</td>" +
            "<td>" + device_id.val() + "</td>" +
            "<td>" + device_name.val() + "</td>" +
            "<td>" + "<input type='button' class='opt-button' value='修改' onclick='edit_device(this)' />&nbsp;<input type='button' class='opt-button' value='删除' onclick='delete_device(this)' />" + "</td>" +
            "</tr>" );
        $( this ).dialog( "close" ); 
      }
    },
    "取消": function() {
        $( this ).dialog( "close" );
    }
    },
    close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
        tips.text("请填写以下内容：");
    }
  });

  $( "#add_flowdevice" )
      .button()
      .click(function() {
      $( "#add-flowdevice-dialog" ).dialog( "open" );
  })
})
/*-----Edit Flow Device Control-----*/    
$(function(){
  //获得表单中的对象引用
  var e_flowdevice_nth_tr = $("#e_flowdevice_nth_tr");
  var e_device_ip = $( "#e_flowdevice_ip" ),
  e_device_id = $( "#e_flowdevice_id" ),
  e_device_name = $( "#e_flowdevice_name" ),
  allFields = $( [] ).add( e_device_ip ).add( e_device_id ).add( e_device_name ),
  tips = $( "#edit-flowdevice-tips" );

  //更新提示信息
  function updateTips( t ) {
      tips
      .text( t )
      .addClass( "ui-state-highlight" );
      setTimeout(function() {
          tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
  }

  //正则检查
  function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
          o.addClass( "ui-state-error" );
          updateTips( n );
          return false;
      } else {
          return true;
      }
  }

  //修改边界链路dialog定制
  $("#edit-flowdevice-dialog").dialog({
    autoOpen: false,
    height: 250,
    width: 400,
    modal: true,
    buttons: {
    "保存信息": function() {
      var bValid = true;
      allFields.removeClass( "ui-state-error" );
      //检查
      bValid = bValid && checkRegexp( e_device_ip, ipReg, "设备IP地址格式错误！" );
      bValid = bValid && checkRegexp( e_device_id, intReg, "设备ID必须为正整数！" );
      if ( bValid ) {
        nthtr = parseInt(e_flowdevice_nth_tr.val()) + 1;
        editTr = $("#tbody-flowdevices-list").children(":nth-child("+nthtr+")");
        editTr.children(":nth-child(1)").html(e_device_ip.val());
        editTr.children(":nth-child(2)").html(e_device_id.val());
        editTr.children(":nth-child(3)").html(e_device_name.val());
        $( this ).dialog( "close" ); 
      }
    },
    "取消": function() {
        $( this ).dialog( "close" );
    }
    },
    close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
        tips.text("请填写以下内容：");
    }
  });
})

/*-----Add fb Control-----*/    
$(function(){
  //获得表单中的对象引用
  var fb_name = $( "#a_fb_name" ),
  fb_ip = $( "#a_fb_ip" ),
  allFields = $( [] ).add( fb_name ).add( fb_ip ),
  tips = $( "#add-fb-tips" );

  //更新提示信息
  function updateTips( t ) {
      tips
      .text( t )
      .addClass( "ui-state-highlight" );
      setTimeout(function() {
          tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
  }

  //正则检查
  function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
          o.addClass( "ui-state-error" );
          updateTips( n );
          return false;
      } else {
          return true;
      }
  }

  //新增分部dialog定制
  $("#add-fb-dialog").dialog({
    autoOpen: false,
    height: 200,
    width: 400,
    modal: true,
    buttons: {
    "保存信息": function() {
      var bValid = true;
      allFields.removeClass( "ui-state-error" );
      //检查
      //bValid = bValid && checkRegexp( fb_name, intReg, "AS号必须是整数！" );
      bValid = bValid && checkRegexp( fb_ip, ipReg, "非法的IP地址！" );
      if ( bValid ) {
        $( "#tbody-fb-list" ).append( "<tr>" +
            "<td>" + fb_name.val() + "</td>" +
            "<td>" + fb_ip.val() + "</td>" +
            "<td>" + "<input type='button' class='opt-button' value='修改' onclick='edit_fb(this)' />&nbsp;<input type='button' class='opt-button' value='删除' onclick='delete_fb(this)' />" + "</td>" +
            "</tr>" );
        $( this ).dialog( "close" ); 
      }
    },
    "取消": function() {
        $( this ).dialog( "close" );
    }
    },
    close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
        tips.text("请填写以下内容：");
    }
  });

  $( "#add_fb" )
      .button()
      .click(function() {
      $( "#add-fb-dialog" ).dialog( "open" );
  })
})
/*-----Edit fb Control-----*/    
$(function(){
  //获得表单中的对象引用
  var e_fb_nth_tr = $("#e_fb_nth_tr");
  var e_fb_name = $( "#e_fb_name" ),
  e_fb_ip = $( "#e_fb_ip" ),
  allFields = $( [] ).add( e_fb_name ).add( e_fb_ip ),
  tips = $( "#edit-fb-tips" );

  //更新提示信息
  function updateTips( t ) {
      tips
      .text( t )
      .addClass( "ui-state-highlight" );
      setTimeout(function() {
          tips.removeClass( "ui-state-highlight", 1500 );
      }, 500 );
  }

  //正则检查
  function checkRegexp( o, regexp, n ) {
      if ( !( regexp.test( o.val() ) ) ) {
          o.addClass( "ui-state-error" );
          updateTips( n );
          return false;
      } else {
          return true;
      }
  }

  //修改边界链路dialog定制
  $("#edit-fb-dialog").dialog({
    autoOpen: false,
    height: 200,
    width: 400,
    modal: true,
    buttons: {
    "保存信息": function() {
      var bValid = true;
      allFields.removeClass( "ui-state-error" );
      //检查
      //bValid = bValid && checkRegexp( e_fb_name, intReg, "AS号必须是整数！" );
      bValid = bValid && checkRegexp( e_fb_ip, ipReg, "非法的IP地址！" );
      if ( bValid ) {
        nthtr = parseInt(e_fb_nth_tr.val()) + 1;
        editTr = $("#tbody-fb-list").children(":nth-child("+nthtr+")");
        editTr.children(":nth-child(1)").html(e_fb_name.val());
        editTr.children(":nth-child(2)").html(e_fb_ip.val());
        $( this ).dialog( "close" ); 
      }
    },
    "取消": function() {
        $( this ).dialog( "close" );
    }
    },
    close: function() {
        allFields.val( "" ).removeClass( "ui-state-error" );
        tips.text("请填写以下内容：");
    }
  });
})


///////////////////////////////////////////////////////////////////////

// ajax 提交
function submitData(type, sendobj) {
  $.ajax({
    type: "POST",
    dataType: "json",
    url: modifycfgurl,
    data: "type=" + type + "&data=" + JSON.stringify(sendobj),
    success:function(ret_data){
      if (ret_data.codeStatus == 0) { //成功
        alert(ret_data.msg);
      }
    }
  })
}

function edit_bgp_link(obj) {
  trObj = $(obj).parent().parent();
  $("#e_bgplink_nth_tr").val($(trObj).index());  //第几行，修改值后定位用
  $("#e_bgplink_id").val($(obj).parent().children(":nth-child(1)").val());
  $("#e_as").val( $(trObj).children(":nth-child(1)").html() );
  $("#e_router_id").val( $(trObj).children(":nth-child(2)").html() );
  $("#e_interface_ip").val( $(trObj).children(":nth-child(3)").html() );
  $("#e_n_as_num").val( $(trObj).children(":nth-child(4)").html() );
  $("#e_n_router_id").val( $(trObj).children(":nth-child(5)").html() );
  $("#e_n_interface_ip").val( $(trObj).children(":nth-child(6)").html() );
  $("#e_mask").val( $(trObj).children(":nth-child(7)").html() );
  $("#e_metric").val( $(trObj).children(":nth-child(8)").html() );
  $( "#edit-borderlink-dialog" ).dialog( "open" );
}
function delete_bgp_link(obj) {
  if (confirm("确定要删除此边界链路吗？")) {
    $(obj).parent().parent().fadeOut("slow",function(){
      $(obj).parent().parent().remove(); 
    });  
  }   
}

function edit_isis_mapping(obj) {
  trObj = $(obj).parent().parent();
  $("#e_isis_nth_tr").val($(trObj).index());  //第几行，修改值后定位用
  $("#e_sys_id").val( $(trObj).children(":nth-child(1)").html() );
  $("#e_ips").val( $(trObj).children(":nth-child(2)").html() );
  $( "#edit-isis-mapping-dialog" ).dialog( "open" );
}
function delete_isis_mapping(obj) {
  if (confirm("确定删除此路由器配置吗？")) {
    $(obj).parent().parent().fadeOut("slow",function(){
      $(obj).parent().parent().remove(); 
    });  
  }   
} 

function edit_protocol(obj) {
  trObj = $(obj).parent().parent();
  $("#e_protocol_nth_tr").val($(trObj).index());  //第几行，修改值后定位用
  $("#e_protocol_name").val( $(trObj).children(":nth-child(1)").html() );
  trProtocolNum = $(trObj).children(":nth-child(2)").html();
  if(trProtocolNum == "TCP") {
    $("#e_protocol_num option[value='" + 6 + "']").attr("selected",'selected');
  } else {
    $("#e_protocol_num option[value='" + 17 + "']").attr("selected",'selected');
  }
  $("#e_ports").val( $(trObj).children(":nth-child(3)").html() );
  $( "#edit-protocol-dialog" ).dialog( "open" );
}
function delete_protocol(obj) {
  if (confirm("确定不再监视此业务吗？")) {
    $(obj).parent().parent().fadeOut("slow",function(){
      $(obj).parent().parent().remove(); 
    });  
  }   
}

function edit_device(obj) {
  trObj = $(obj).parent().parent();
  $("#e_flowdevice_nth_tr").val($(trObj).index());  //第几行，修改值后定位用
  $("#e_flowdevice_ip").val( $(trObj).children(":nth-child(1)").html() );
  $("#e_flowdevice_id").val( $(trObj).children(":nth-child(2)").html() );
  $("#e_flowdevice_name").val( $(trObj).children(":nth-child(3)").html() );
  $( "#edit-flowdevice-dialog" ).dialog( "open" );
}
function delete_device(obj) {
  if (confirm("确定从列表中删除此设备？")) {
    $(obj).parent().parent().fadeOut("slow",function(){
      $(obj).parent().parent().remove(); 
    });  
  }   
}

function edit_fb(obj) {
  trObj = $(obj).parent().parent();
  $("#e_fb_nth_tr").val($(trObj).index());  //第几行，修改值后定位用
  $("#e_fb_name").val( $(trObj).children(":nth-child(1)").html() );
  $("#e_fb_ip").val( $(trObj).children(":nth-child(2)").html() );
  $("#edit-fb-dialog").dialog( "open" );
}
function delete_fb(obj) {
  if (confirm("确定删除此分部吗？")) {
    $(obj).parent().parent().fadeOut("slow",function(){
      $(obj).parent().parent().remove(); 
    });  
  }   
}
</script>
</head>

<body>
  <div class="layout">

    <div class="layout-left">
      <div class="layout-header">
      	<div class="layout-header-toggle"></div>
      	<div class="layout-header-inner">系统配置</div>
      </div>
      <div class="layout-content accordion-panel">
      	<ul>
      		<li class="menu-active">本地配置</li>
      		<li>综合分析设备配置</li>
      		<li>流量汇集设备配置</li>
      		<li>样式自定义</li>
      		<li>分部配置</li>
      	</ul>
        <p style='text-align:center;font-size:16px;' >版本号：V 0.2.0.0</p>
      </div>
    </div>

    <div class="layout-center">
    	<!-- 本地配置 -->
    	<div id="page0" class="center-page">
          <!-- 1.协议，是否总部 -->
        	<div class="group">
	          <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
	          <span>网络环境</span>
	        </div>
          <ul>
              <li><label>协议：</label><select id="protocol" name="protocol"><option value="ospf">OSPF</option><option value="isis">ISIS</option></select></li>
              <li id="asli"><label>AS号：</label><input id="asnum" value="<?=$sysconfig->localSet->asNum?>" /></li>
              <li id="areali"><label>Area号：</label><input id="areaid" value="<?=$sysconfig->localSet->areaId?>" /></li>
              <li id="hqbox"><label>是否总部：</label>&nbsp;&nbsp;<input type="radio" name="isHQ" value=1 <?php if($sysconfig->localSet->isHQ == 1) echo "checked=checked"; ?> />是&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="isHQ" value=0 <?php if($sysconfig->localSet->isHQ == 0) echo "checked=checked"; ?> />不是</li>
          </ul>
          <input type="button" class="save-button" id="button1" value="保存">

          <!-- 2.interval等本地参数 -->
          <div class="group">
	          <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
	          <span>参数设置</span>
	        </div>
          <ul>
              
              <li><label>流量拓扑周期：</label><select id = "interval" name = "interval">
                <?php for($i = 1; $i <= 15; $i++) {
                  echo "<option value='".$i."'>".$i."</option>";
                 } ?>
                </select>分钟</li>
              <li><label>提前分析时间：</label><input id="inadvance" value=<?=$sysconfig->localSet->inAdvance?> />秒</li>
              <li><label>Top N 默认值：</label><input id="topn" value=<?=$sysconfig->localSet->topN?> /></li>
              <li><label>本地流查询服务端口：</label><input id="localflowqueryport" value=<?=$sysconfig->localSet->localFlowQueryPort?> /></li>
              <li><label>本地IP</label><input id="localip" value=<?=$sysconfig->localSet->localIp?> /></li>
              <li><label>站点访问入口：</label><input id="baseurl" value="<?=$sysconfig->localSet->baseUrl?>" /></li> 
          </ul>
          <input type="button" class="save-button" id="button2" value="保存">

          <!-- 3.OSPF边界链路 -->
          <div id="ospf-border">
          <div class="group">
	          <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
	          <span>OSPF边界链路配置(本AS)</span>
	        </div>
          <button id="add_bgp_link">新增边界链路</button>
          <table id="bgplinks-list" class="table-list">
            <tr class="table-list-title">
              <td>AS号</td>
              <td>路由器ID</td>
              <td>接口IP</td>
              <td>邻居AS号</td>
              <td>邻居路由器ID</td>
              <td>邻居接口IP</td>
              <td>子网掩码</td>
              <td>链路代价</td>
              <td style="width:160px;">编辑</td>
            </tr>
            <tbody id="tbody-borderlink-list" class="tbody-list" >
              <?php foreach ($bgp_links as $key => $link) { ?>
                <tr>
                  <td><?=$link['as_num']?></td>
                  <td><?=$link['router_id']?></td>
                  <td><?=$link['interface_ip']?></td>
                  <td><?=$link['n_as_num']?></td>
                  <td><?=$link['n_router_id']?></td>
                  <td><?=$link['n_interface_ip']?></td>
                  <td><?=$link['mask']?></td>
                  <td><?=$link['metric']?></td>
                  <td><input type="hidden" value=<?=$link['id']?> /><input type="button" class="opt-button" value="修改" onclick="edit_bgp_link(this)" />&nbsp;<input type="button" class="opt-button" value="删除" onclick="delete_bgp_link(this)" /></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
          <br>
          <input type="button" class="save-button" id="button3" value="保存">
          </div>

          <!-- 3-5.ISIS level 1/2路由器IP和sysid映射配置 -->
          <div id="isis-border">
          <div class="group">
            <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
            <span>ISIS Level 1和Level 2之间的边界路由器IP映射配置</span>
          </div>
          <button id="add_l12router">新增Level 1和Level 2之间的边界路由器</button>
          <table id="isis-mapping-list" class="table-list">
            <tr class="table-list-title">
              <td>路由器sysID</td>
              <td>路由器接口</td>
              <td style="width:160px;">编辑</td>
            </tr>
            <tbody id="tbody-isis-mapping-list" class="tbody-list">
              <?php foreach ($sysconfig->localSet->isisIpIdMapping as $key => $mapping) { ?>
                <tr>
                  <td><?=$mapping->sysId?></td>
                  <td><?php
                    $ips = "";
                    foreach ($mapping->ip as $key => $ip) {
                      $ips = $ips.$ip.",";
                    }
                    echo rtrim($ips, ",");
                    ?></td>
                  <td><input type="button" class="opt-button" value="修改" onclick="edit_isis_mapping(this)" />&nbsp;<input type="button" class="opt-button" value="删除" onclick="delete_isis_mapping(this)" /></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
          <br>
          <input type="button" class="save-button" id="button3-5" value="保存">
          </div>

          <!-- 4.链路业务端口监测 -->
	        <div class="group">
	          <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
	          <span>链路流量监测业务配置</span>
	        </div>
          <button id="add_protocol">新增监视业务</button>
      		<table id="protocol-list" class="table-list">
      			<tr class="table-list-title">
      				<td>监测业务</td>
              <td>监测协议</td>
      				<td>监测端口</td>
              <td style="width:160px;">编辑</td>
      			</tr>
      			<tbody id="tbody-protocol-list" class="tbody-list">
      				<?php foreach ($sysconfig->localSet->observePorts as $key => $observePort) { ?>
      					<tr>
      						<td><?=$observePort->protocol?></td>
                  <td><?=$observePort->protocolNum?></td>
      						<td><?php
                    $ports = "";
                    foreach ($observePort->ports as $key => $port) {
      							  $ports = $ports.$port.",";
                    }
                    echo rtrim($ports, ",");
      						  ?></td>
                  <td><input type="button" class="opt-button" value="修改" onclick="edit_protocol(this)" />&nbsp;<input type="button" class="opt-button" value="删除" onclick="delete_protocol(this)" /></td>
      					</tr>
      				<?php } ?>
      			</tbody>
      		</table>
          <br>
          <input type="button" class="save-button" id="button4" value="保存">

          <!-- 5.数据库设置 -->
      		<div class="group">
	          <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
	          <span>数据库设置</span>
	        </div>
          <ul>
              <li><label>IP地址：</label><input id="dbip" value="<?=$sysconfig->localSet->localDBSet->ip?>" /></li>
              <li><label>端口：</label><input id="dbport" value="<?=$sysconfig->localSet->localDBSet->port?>" /></li>
              <li><label>数据库名：</label><input id="dbname" value="<?=$sysconfig->localSet->localDBSet->dbname?>" /></li>
              <li><label>登录用户名：</label><input id="dbuser" value="<?=$sysconfig->localSet->localDBSet->username?>" /></li>
              <li><label>登录密码：</label><input id="dbpw" type="password" value="<?=$sysconfig->localSet->localDBSet->password?>" /></li>
          </ul>
          <input type="button" class="save-button" id="button5" value="保存">
    	</div>

    	<!-- 综合分析设备配置 -->
    	<div id="page1" class="center-page">
          <!-- 6.综合分析设备ip和端口 -->
          <div class="group">
	          <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
	          <span>Socket通信配置</span>
	        </div>
          <ul>
              <li><label>设备IP地址：</label><input id="globalanalysisip" value="<?=$sysconfig->localSet->gloabalAnalysisSet->globalAnalysisIp?>" /></li>
              <li><label>接受流量拓扑端口号：</label><input id="globalanalysisport" value="<?=$sysconfig->localSet->gloabalAnalysisSet->globalAnalysisPort?>" /></li>
          </ul>
          <input type="button" class="save-button" id="button6" value="保存">
    	</div>

    	<!-- 流量汇集设备配置 -->
    	<div id="page2" class="center-page">
          <!-- 7.端口设置等参数 -->
    		<div class="group">
	          <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
	          <span>参数配置</span>
	        </div>
	        <ul>
              <li><label>接收配置端口号：</label><input id="flowdevice_config_port" value="<?=$sysconfig->localSet->flowDeviceSet->configPort?>" /></li>
              <li><label>接受拓扑端口号：</label><input id="flowdevice_topo_port" value="<?=$sysconfig->localSet->flowDeviceSet->topoPort?>" /></li>
              <li><label>流查询端口号：</label><input id="flowdevice_flow_port" value="<?=$sysconfig->localSet->flowDeviceSet->flowPort?>" /></li>
              <li><label>采样比：</label><input id="flowdevice_samplingrate" value="<?=$sysconfig->localSet->flowDeviceSet->samplingRate?>" /></li>
          </ul>
          <input type="button" class="save-button" id="button7" value="保存">

          <!-- 8.设备列表 -->
	        <div class="group">
	          <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
	          <span>设备列表</span>
	        </div>
          <button id="add_flowdevice">新增流量设备</button>
          <table id="flowdevices-list" class="table-list">
            <tr class="table-list-title">
              <td>设备IP地址</td>
              <td>设备ID</td>
              <td>设备名称</td>
              <td style="width:160px;">编辑</td>
            </tr>
            <tbody id="tbody-flowdevices-list" class="tbody-list">
              <?php foreach ($sysconfig->localSet->flowDeviceSet->devInfo as $key => $dev) { ?>
                <tr>
                  <td><?=$dev->devIp?></td>
                  <td><?=$dev->devId?></td>
                  <td><?=$dev->devName?></td>
                  <td><input type="button" class="opt-button" value="修改" onclick="edit_device(this)" />&nbsp;<input type="button" class="opt-button" value="删除" onclick="delete_device(this)" /></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
          <br>
          <input type="button" class="save-button" id="button8" value="保存">
		  
		  <!-- 11.汇报设备设置等参数 -->
    		<div class="group">
	          <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
	          <span>汇报设备配置</span>
	        </div>
	        <ul>
              <li><label>一级汇报地址：</label><input id="url_Level1" value="<?=$sysconfig->localSet->flowDeviceSet->url_Level1?>" /></li>
              <li><label>二级汇报地址：</label><input id="url_Level2" value="<?=$sysconfig->localSet->flowDeviceSet->url_Level2?>" /></li>
            </ul>
          <input type="button" class="save-button" id="button11" value="保存">
    	</div>

    	<!-- 样式自定义 -->
    	<div id="page3" class="center-page">
          <!-- 9.样式设置 -->
          <div class="group">
            <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
            <span>样式自定义</span>
          </div>
    			<ul>
              <li><label>画布宽：</label><input id="canvas_width" value="<?=$sysconfig->styleSet->canvasWidth?>" /></li>
              <li><label>画布高：</label><input id="canvas_height" value="<?=$sysconfig->styleSet->canvasHeight?>" /></li>
          </ul>
          <input type="button" class="save-button" id="button9" value="保存">
    	</div>

    	<!-- 总部-分部设置 -->
    	<div id="page4" class="center-page">
        <!-- 10.总部-分部设置 -->
        <div class="group">
          <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
          <span>分部设置</span>
        </div>
        <button id="add_fb">新增分部</button>
        <table id="fb-list" class="table-list">
          <tr class="table-list-title">
            <td>分部</td>
            <td>分部ip</td>
            <td style="width:160px;">编辑</td>
          </tr>
          <tbody id="tbody-fb-list" class="tbody-list">
            <?php if ($sysconfig->localSet->protocol == "ospf"): ?>
            <?php foreach ($sysconfig->HQSet->asSet as $key => $as) { ?>
              <tr>
                <td><?=$as->asNum?></td>
                <td><?=$as->asIp?></td>
                <td><input type="button" class="opt-button" value="修改" onclick="edit_fb(this)" />&nbsp;<input type="button" class="opt-button" value="删除" onclick="delete_fb(this)" /></td>
              </tr>
            <?php } ?>
            <?php else: ?>
            <?php foreach ($sysconfig->HQSet->areaSet as $key => $area) { ?>
              <tr>
                <td><?=$area->areaId?></td>
                <td><?=$area->areaIp?></td>
                <td><input type="button" class="opt-button" value="修改" onclick="edit_fb(this)" />&nbsp;<input type="button" class="opt-button" value="删除" onclick="delete_fb(this)" /></td>
              </tr>
            <?php } ?>
            <?php endif; ?>
          </tbody>
        </table>
        <br/>
        <input type="button" class="save-button" id="button10" value="保存">  
    </div>
    
    <div class="layout-collapse-left" style="display:none">
    	<div class="layout-collapse-left-toggle"></div>
    </div>

    <div class="footer"></div>
  </div>
  
  <!--Dialog : Add Border Link-->
  <div id="add-borderlink-dialog" class="dialog" title="添加边界链路">
      <p class="validateTips" id="add-borderlink-tips">请填写以下内容：</p>
      <form>
          <table>
              <tr><td>AS号：</td>
                  <td><input type="text" name="a_as" id="a_as" /></td>
              </tr>
              <tr><td>路由器ID：</td>
                  <td><input type="text" name="a_router_id" id="a_router_id" /></td>
              </tr>
              <tr><td>接口IP：</td>
                  <td><input type="text" name="a_interface_ip" id="a_interface_ip" /></td>
              </tr>
              <tr><td>邻居AS号：</td>
                  <td><input type="text" name="a_n_as_num" id="a_n_as_num" /></td>
              </tr>
              <tr><td>邻居路由器ID：</td>
                  <td><input type="text" name="a_n_router_id" id="a_n_router_id" /></td>
              </tr>
              <tr><td>邻居接口IP：</td>
                  <td><input type="text" name="a_n_interface_ip" id="a_n_interface_ip" /></td>
              </tr>
              <tr><td>子网掩码：</td>
                  <td><input type="text" name="a_mask" id="a_mask" /></td>
              </tr>
              <tr><td>链路代价：</td>
                  <td><input type="text" name="a_metric" id="a_metric" /></td>
              </tr>
          </table>
      </form>
  </div>

  <!--Dialog : Edit Border Link-->
  <div id="edit-borderlink-dialog" class="dialog" title="修改边界链路">
      <p class="validateTips" id="edit-borderlink-tips">请填写以下内容：</p>
      <form>
          <table>
              <tr><td>AS号：</td>
                  <td><input type="text" name="e_as" id="e_as" /></td>
              </tr>
              <tr><td>路由器ID：</td>
                  <td><input type="text" name="e_router_id" id="e_router_id" /></td>
              </tr>
              <tr><td>接口IP：</td>
                  <td><input type="text" name="e_interface_ip" id="e_interface_ip" /></td>
              </tr>
              <tr><td>邻居AS号：</td>
                  <td><input type="text" name="e_n_as_num" id="e_n_as_num" /></td>
              </tr>
              <tr><td>邻居路由器ID：</td>
                  <td><input type="text" name="e_n_router_id" id="e_n_router_id" /></td>
              </tr>
              <tr><td>邻居接口IP：</td>
                  <td><input type="text" name="e_n_interface_ip" id="e_n_interface_ip" /></td>
              </tr>
              <tr><td>子网掩码：</td>
                  <td><input type="text" name="e_mask" id="e_mask" /></td>
              </tr>
              <tr><td>链路代价：</td>
                  <td><input type="text" name="e_metric" id="e_metric" /></td>
              </tr>
              <input type="hidden" name="e_bgplink_id" id="e_bgplink_id" />
              <input type="hidden" name="e_bgplink_nth_tr" id="e_bgplink_nth_tr" />
          </table>
      </form>
  </div>

  <!--Dialog : Add Isis Mapping-->
  <div id="add-isis-mapping-dialog" class="dialog" title="添加ISIS Level 1和Level 2之间的边界路由器">
    <p class="validateTips" id="add-isis-mapping-tips">请填写以下内容：</p>
    <form>
      <table>
        <tr><td>路由器sysID：</td>
          <td><input type="text" name="a_sys_id" id="a_sys_id" /><span>Level 1和Level 2之间的边界路由器sysID</span></td>
        </tr>
        <tr><td>路由器接口：</td>
          <td><input type="text" name="a_ips" id="a_ips" /><span>多个接口请用“,”分隔</span></td>
        </tr>
      </table>
    </form>
  </div>

  <!--Dialog : Edit Isis Mapping-->
  <div id="edit-isis-mapping-dialog" class="dialog" title="修改ISIS Level 1和Level 2之间的边界路由器">
    <p class="validateTips" id="edit-isis-mapping-tips">请填写以下内容：</p>
    <form>
      <table>
        <tr><td>路由器sysID：</td>
          <td><input type="text" name="e_sys_id" id="e_sys_id" /></td>
        </tr>
        <tr><td>路由器接口：</td>
          <td><input type="text" name="e_ips" id="e_ips" /></td>
        </tr>
      </table>
      <input type="hidden" name="e_isis_nth_tr" id="e_isis_nth_tr" />
    </form>
  </div>

  <!--Dialog : Add Protocol-->
  <div id="add-protocol-dialog" class="dialog" title="添加监视业务">
    <p class="validateTips" id="add-protocol-tips">请填写以下内容：</p>
    <form>
      <table>
        <tr><td>业务类型：</td>
          <td><input type="text" name="a_protocol_name" id="a_protocol_name" /><span>业务类型用英文表示</span></td>
        </tr>
        <tr><td>监测协议：</td>
          <td><select name="a_protocol_num" id="a_protocol_num"><option value="6">TCP</option><option value="17">UDP</option></select></td>
        </tr>
        <tr><td>监测端口：</td>
          <td><input type="text" name="a_ports" id="a_ports" /><span>多个端口请用“,”分隔</span></td>
        </tr>
      </table>
    </form>
  </div>

  <!--Dialog : Edit Protocol-->
  <div id="edit-protocol-dialog" class="dialog" title="修改监视业务">
    <p class="validateTips" id="edit-protocol-tips">请填写以下内容：</p>
    <form>
      <table>
        <tr><td>业务类型：</td>
          <td><input type="text" name="e_protocol_name" id="e_protocol_name" /></td>
        </tr>
        <tr><td>监测协议：</td>
          <td><select name="e_protocol_num" id="e_protocol_num"><option value="6">TCP</option><option value="17">UDP</option></select></td>
        </tr>
        <tr><td>监测端口：</td>
          <td><input type="text" name="e_ports" id="e_ports" /></td>
        </tr>
      </table>
      <input type="hidden" name="e_protocol_nth_tr" id="e_protocol_nth_tr" />
    </form>
  </div>

  <!--Dialog : Add Flow Device-->
  <div id="add-flowdevice-dialog" class="dialog" title="添加流量设备">
    <p class="validateTips" id="add-flowdevice-tips">请填写以下内容：</p>
    <form>
      <table>
        <tr><td>流量设备IP地址：</td>
          <td><input type="text" name="a_flowdevice_ip" id="a_flowdevice_ip" /></td>
        </tr>
        <tr><td>流量设备ID：</td>
          <td><input type="text" name="a_flowdevice_id" id="a_flowdevice_id" /></td>
        </tr>
        <tr><td>流量设备名称：</td>
          <td><input type="text" name="a_flowdevice_name" id="a_flowdevice_name" /></td>
        </tr>
      </table>
    </form>
  </div>

  <!--Dialog : Edit Flow Device-->
  <div id="edit-flowdevice-dialog" class="dialog" title="修改流量设备">
    <p class="validateTips" id="edit-flowdevice-tips">请填写以下内容：</p>
    <form>
      <table>
        <tr><td>流量设备IP地址：</td>
          <td><input type="text" name="e_flowdevice_ip" id="e_flowdevice_ip" /></td>
        </tr>
        <tr><td>流量设备ID：</td>
          <td><input type="text" name="e_flowdevice_id" id="e_flowdevice_id" /></td>
        </tr>
        <tr><td>流量设备名称：</td>
          <td><input type="text" name="e_flowdevice_name" id="e_flowdevice_name" /></td>
        </tr>
      </table>
      <input type="hidden" name="e_flowdevice_nth_tr" id="e_flowdevice_nth_tr" />
    </form>
  </div>

  <!--Dialog : Add FB-->
  <div id="add-fb-dialog" class="dialog" title="添加分部">
    <p class="validateTips" id="add-fb-tips">请填写以下内容：</p>
    <form>
      <table>
        <tr><td>分部：</td>
          <td><input type="text" name="a_fb_name" id="a_fb_name" /><span>OSPF请填写AS号</span></td>
        </tr>
        <tr><td>分部IP：</td>
          <td><input type="text" name="a_fb_ip" id="a_fb_ip" /><span>web板卡所在IP</span></td>
        </tr>
      </table>
    </form>
  </div>

  <!--Dialog : Edit FB-->
  <div id="edit-fb-dialog" class="dialog" title="修改分部">
    <p class="validateTips" id="edit-fb-tips">请填写以下内容：</p>
    <form>
      <table>
        <tr><td>分部：</td>
          <td><input type="text" name="e_fb_name" id="e_fb_name" /></td>
        </tr>
        <tr><td>分部IP：</td>
          <td><input type="text" name="e_fb_ip" id="e_fb_ip" /></td>
        </tr>
      </table>
      <input type="hidden" name="e_fb_nth_tr" id="e_fb_nth_tr" />
    </form>
  </div>
</body>
</html>