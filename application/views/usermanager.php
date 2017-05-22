<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>用户权限</title>
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/reset.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/tabpage.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>application/views/css/jquery.ui.theme.css">
<script src="<?php echo base_url(); ?>application/views/js/jquery-1.9.1.js"></script>
<script src="<?php echo base_url(); ?>application/views/js/tabpage.js"></script>
<script src="<?php echo base_url(); ?>application/views/js/jquery-ui-1.9.2.custom.js"></script>
<script type="text/javascript">
var userUrl = "<?php echo base_url(); ?>index.php/interface_common/userAjax";
$(document).ready(function(){
    $(".center-page").css("display", "none");
    $("#page0").css("display", "block");
})
/*-----Add User Control-----*/    
$(function(){
    //获得表单中的对象引用
    var name = $( "#name" ),
    password = $( "#password" ),
    dpt = $( "#dpt" ),
    usertype = $( "#usertype" ),
    //usergender = $( "#usergender" ),
    allFields = $( [] ).add( name ).add( password ),
    tips = $( "#add-user-tips" );

    //更新提示信息
    function updateTips( t ) {
        tips
        .text( t )
        .addClass( "ui-state-highlight" );
        setTimeout(function() {
            tips.removeClass( "ui-state-highlight", 1500 );
        }, 500 );
    }

    //长度检查
    function checkLength( o, n, min, max ) {
        if ( o.val().length > max || o.val().length < min ) {
            o.addClass( "ui-state-error" );
            updateTips( n + "的长度" + "必须在" +
            min + "和" + max + "之间" );
            return false;
        } else {
            return true;
        }
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

    //用户名验重
    function checkName(o) {
        var rtn = false;
        $.ajax({
            type: "POST",
            async:false,    //注意这里要写成同步
            dataType: "json",
            url: userUrl,
            data: "type=checkusername&username=" + o.val(),
            success: function(data){
                if(data.codeStatus == 0) {  //用户名可用
                    rtn = true;
                } else {
                    o.addClass( "ui-state-error" );
                    updateTips( data.errorMsg );
                }
            }
        });
        return rtn;
    }

    //新增用户dialog定制
    $("#add-user-dialog").dialog({
        autoOpen: false,
        height: 360,
        width: 350,
        modal: true,
        buttons: {
        "保存信息": function() {
            var bValid = true;
            allFields.removeClass( "ui-state-error" );
            bValid = bValid && checkLength( name, "用户名", 3, 16 );
            bValid = bValid && checkLength( password, "密码", 5, 16 );
            bValid = bValid && checkRegexp( password, /^([0-9a-zA-Z])+$/, "密码允许输入范围 : a-z 0-9" );
            bValid = bValid && checkName( name );    //ajax检查重名
            if ( bValid ) {
                var uid = 0;               
                //填写正确，提交
                var suc = false;
                var errMsg = "网络错误，添加用户失败！";
                $.ajax({
                    type: "POST",
                    async:false,    //注意这里要写成同步
                    dataType: "json",
                    url: userUrl,
                    data: "type=adduser&username=" + name.val() + "&password=" + password.val() + "&dpt=" + 
                    dpt.val() + "&usertype=" + usertype.val(),
                    success: function(data){
                        if(data.codeStatus == 0) {  //添加用户成功
                            suc = true;  
                            uid = data.result.addUser.userid;  
                        } else {
                            errMsg = data.errorMsg;
                        }
                    }
                });
                if (suc) {
                    alert("添加新用户成功！");
                    //返回结果，在页面中动态加上
                    $( "#tbody-user-list" ).append( "<tr id='tr" + uid + "'>" +
                        "<td>" + name.val() + "</td>" +
                        "<td>" + dpt.find("option:selected").text() + "</td>" +
                        "<td>" + usertype.find("option:selected").text() + "</td>" +
                        //"<td>" + usergender.find("option:selected").text() + "</td>" +
                        "<td>" + "<input type='button' class='opt-button' value='修改' onclick=editUser(" + uid + ") />" + 
                        "&nbsp;<input type='button' class='opt-button' value='删除' onclick=deleteUser(" + uid + ") /></td>" +
                        "</tr>" );
                    $( this ).dialog( "close" ); 
                } else {
                    updateTips( errMsg );
                }                
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

    $( "#add-user" )
        .button()
        .click(function() {
        if(<?php echo $user['usertype']; ?> != 1) {
            alert("没有操作权限");
        } else{
            $( "#add-user-dialog" ).dialog( "open" );
        }
    })
})

/*-----Edit User Control-----*/
$(function(){
    //获得表单中的对象引用
    var e_uid = $("#e_uid");
    e_name = $( "#e_name" ),
    e_password = $( "#e_password" ),
    e_dpt = $( "#e_dpt" ),
    e_usertype = $( "#e_usertype" ),
    //e_usergender = $( "#e_usergender" ),
    e_allFields = $( [] ).add( e_password ),
    e_tips = $( "#edit-user-tips" );

    //更新提示信息
    function updateTips( t ) {
        e_tips
        .text( t )
        .addClass( "ui-state-highlight" );
        setTimeout(function() {
            e_tips.removeClass( "ui-state-highlight", 1500 );
        }, 500 );
    }

    //长度检查
    function checkLength( o, n, min, max ) {
        //长度为0也可以通过检测
        if (o.val().length == 0) {
            return true;
        }
        //修改密码
        else if ( o.val().length > max || o.val().length < min ) {
            o.addClass( "ui-state-error" );
            updateTips( n + "的长度" + "必须在" +
            min + "和" + max + "之间" );
            return false;
        } else {
            return true;
        }
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

    //修改用户dialog定制
    $("#edit-user-dialog").dialog({
        autoOpen: false,
        height: 360,
        width: 400,
        modal: true,
        buttons: {
        "保存信息": function() {
            var e_bValid = true;
            e_allFields.removeClass( "ui-state-error" );
            e_bValid = e_bValid && checkLength( e_password, "修改密码", 5, 16 );
            e_bValid = e_bValid && checkRegexp( e_password, /^([0-9a-zA-Z])*$/, "密码允许输入范围 : a-z 0-9" ); //注意：这里用*表示0次或多次，允许密码为空
            if ( e_bValid ) {
                var suc = false;
                var errMsg = "网络错误，修改用户信息失败！";
                //填写正确，提交
                $.ajax({
                    type: "POST",
                    async: false,    //注意这里要写成同步
                    dataType: "json",
                    url: userUrl,
                    data: "type=edituser&userid=" + e_uid.val() + "&username=" + e_name.val() + "&password=" + 
                    e_password.val() + "&dpt=" + e_dpt.val() + "&usertype=" + e_usertype.val(),
                    success: function(data){
                        if(data.codeStatus == 0) {  //获取用户信息成功
                            suc = true;  
                            //userInfo = data.result.user;  
                        } else {
                            errMsg = data.errorMsg;
                        }
                    }
                });
                if (suc) {
                    //在页面中动态改变表格中内容
                    var uid = e_uid.val();
                    $("#tr" + uid + " td:nth-child(1)").html(e_name.val());  //用户名
                    $("#tr" + uid + " td:nth-child(2)").html(e_dpt.find("option:selected").text());  //单位
                    $("#tr" + uid + " td:nth-child(3)").html(e_usertype.find("option:selected").text());  //权限
                    //$("#tr" + uid + " td:nth-child(4)").html(e_usergender.find("option:selected").text());  //性别
                    //关闭对话框
                    alert("修改用户信息成功！");
                    $( this ).dialog( "close" );
                } else {
                    alert(errMsg);
                }
            }
        },
        "取消": function() {
            $( this ).dialog( "close" );
        }
        },
        close: function() {
            e_allFields.val( "" ).removeClass( "ui-state-error" );
            e_tips.text("");
        }
    });
})

/*-----Add Dpt Control-----*/    
$(function(){
    //获得表单中的对象引用
    var dptname = $( "#dptname" ),
    description = $( "#description" ),
    d_allFields = $( [] ).add( dptname ).add( description ),
    d_tips = $( "#add-dpt-tips" );

    //更新提示信息
    function updateTips( t ) {
        d_tips
        .text( t )
        .addClass( "ui-state-highlight" );
        setTimeout(function() {
            d_tips.removeClass( "ui-state-highlight", 1500 );
        }, 500 );
    }

    //长度检查
    function checkLength( o, n, min, max ) {
        if ( o.val().length > max || o.val().length < min ) {
            o.addClass( "ui-state-error" );
            updateTips( n + "的长度" + "必须在" +
            min + "和" + max + "之间" );
            return false;
        } else {
            return true;
        }
    }

    //单位名验重
    function checkDptName(o) {
        var rtn = false;
        $.ajax({
            type: "POST",
            async:false,    //注意这里要写成同步
            dataType: "json",
            url: userUrl,
            data: "type=checkdptname&dptname=" + o.val(),
            success: function(data){
                if(data.codeStatus == 0) {  //单位名可用
                    rtn = true;
                } else {
                    o.addClass( "ui-state-error" );
                    updateTips( data.errorMsg );
                }
            }
        });
        return rtn;
    }

    //新增单位dialog定制
    $("#add-dpt-dialog").dialog({
        autoOpen: false,
        height: 260,
        width: 350,
        modal: true,
        buttons: {
        "保存信息": function() {
            var d_bValid = true;
            d_allFields.removeClass( "ui-state-error" );
            d_bValid = d_bValid && checkLength( dptname, "单位名", 1, 30 );
            d_bValid = d_bValid && checkLength( description, "单位描述", 0, 255 );
            d_bValid = d_bValid && checkDptName( dptname );    //ajax检查重名
            if ( d_bValid ) {
                //填写正确，提交
                var did = 0;
                var dname = "";
                var suc = false;
                var errMsg = "网络错误，添加单位失败！";
                $.ajax({
                    type: "POST",
                    async:false,    //注意这里要写成同步
                    dataType: "json",
                    url: userUrl,
                    data: "type=adddpt&dptname=" + dptname.val() + "&description=" + description.val() ,
                    success: function(data){
                        if(data.codeStatus == 0) {  //添加单位成功
                            suc = true;  
                            did = data.result.addDpt.dptid;  
                            dname = data.result.addDpt.dptname;
                        } else {
                            errMsg = data.errorMsg;
                        }
                    }
                });
                if (suc) {
                    alert("添加新单位成功！");
                    //返回结果，在页面中动态加上
                    $( "#tbody-dpt-list" ).append( "<tr id='dtr" + did + "'>" +
                    "<td>" + dptname.val() + "</td>" +
                    "<td>" + description.val() + "</td>" +
                    "<td>" + "<input type='button' class='opt-button' value='修改' onclick=editDpt(" + did + ") />" + 
                    "&nbsp;<input type='button' class='opt-button' value='删除' onclick=deleteDpt(" + did + ") /></td>" +
                    "</tr>" );
                    //在添加用户的form中添加单位
                    $("#dpt").append(" <option value=" + did + ">" + dname + "</option>");
                    //在修改用户的form中添加单位
                    $("#e_dpt").append(" <option value=" + did + ">" + dname + "</option>");
                    $( this ).dialog( "close" ); 
                } else {
                    updateTips( errMsg );
                }
            }
        },
        "取消": function() {
            $( this ).dialog( "close" );
        }
        },
        close: function() {
            d_allFields.val( "" ).removeClass( "ui-state-error" );
            d_tips.text("请填写以下内容：");
        }
    });

    $( "#add-dpt" )
        .button()
        .click(function() {
        if(<?php echo $user['dptid']; ?>  != 0 || <?php echo $user['usertype']; ?>  != 1) {
            alert("没有操作权限");
        } else {
            $( "#add-dpt-dialog" ).dialog( "open" );
        }
    })
})

/*-----Edit Dpt Control-----*/
$(function(){
    //获得表单中的对象引用
    var e_did = $( "#e_did" );
    e_dptname = $( "#e_dptname" ),
    e_description = $( "#e_description" ),
    e_allFields = $( [] ).add( e_dptname ).add( e_description ),
    e_tips = $( "#edit-dpt-tips" );

    //更新提示信息
    function updateTips( t ) {
        e_tips
        .text( t )
        .addClass( "ui-state-highlight" );
        setTimeout(function() {
            e_tips.removeClass( "ui-state-highlight", 1500 );
        }, 500 );
    }

    //长度检查
    function checkLength( o, n, min, max ) {
        if ( o.val().length > max || o.val().length < min ) {
            o.addClass( "ui-state-error" );
            updateTips( n + "的长度" + "必须在" +
            min + "和" + max + "之间" );
            return false;
        } else {
            return true;
        }
    }

    //修改单位dialog定制
    $("#edit-dpt-dialog").dialog({
        autoOpen: false,
        height: 260,
        width: 350,
        modal: true,
        buttons: {
        "保存信息": function() {
            var e_bValid = true;
            e_allFields.removeClass( "ui-state-error" );
            e_bValid = e_bValid && checkLength( e_description, "单位描述", 0, 255 );
            if ( e_bValid ) {
                var suc = false;
                //var dptInfo;
                var errMsg = "网络错误，修改单位信息失败！";
                //填写正确，提交
                $.ajax({
                    type: "POST",
                    async: false,    //注意这里要写成同步
                    dataType: "json",
                    url: userUrl,
                    data: "type=editdpt&dptid=" + e_did.val() + "&dptname=" + e_dptname.val() + "&description=" + 
                    e_description.val(),
                    success: function(data){
                        if(data.codeStatus == 0) {  //修改单位信息成功
                            suc = true; 
                            //dptInfo = data.result.dpt;  
                        } else {
                            errMsg = data.errorMsg;
                        }
                    }
                });
                if (suc) {
                    //在页面中动态改变表格中内容
                    var did = e_did.val();
                    $("#dtr" + did + " td:nth-child(1)").html(e_dptname.val());  //单位名
                    $("#dtr" + did + " td:nth-child(2)").html(e_description.val());  //单位描述
                    //关闭对话框
                    alert("修改单位信息成功！");
                    $( this ).dialog( "close" );
                } else {
                    alert(errMsg);
                }
            }
        },
        "取消": function() {
            $( this ).dialog( "close" );
        }
        },
        close: function() {
            e_allFields.val( "" ).removeClass( "ui-state-error" );
            e_tips.text("");
        }
    });
})

function editUser(uid) {
    if(<?php echo $user['usertype']; ?>  != 1) {
            alert("没有操作权限");
            return;
    }
    var suc = false;
    var userInfo;
    var errMsg = "获取用户信息失败！";
    //请求用户信息
    $.ajax({
        type: "POST",
        async: false,    //注意这里要写成同步
        dataType: "json",
        url: userUrl,
        data: "type=getuser&userid=" + uid,
        success: function(data){
            if(data.codeStatus == 0) {  //获取用户信息成功
                suc = true;  
                userInfo = data.result.user;  
            } else {
                errMsg = data.errorMsg;
            }
        }
    });
    if (suc) {
        //根据userInfo改变表单中内容
        $("#e_uid").val(uid);
        $("#e_name").val(userInfo.username);
        $("#e_dpt option[value='" + userInfo.dptid + "']").attr("selected",'selected');
        $("#e_usertype option[value='" + userInfo.usertype + "']").attr("selected",'selected');
        //$("#e_usergender option[value='" + userInfo.usergender + "']").attr("selected",'selected');
        if(uid == 1){
            $("#e_dpt").attr("disabled",'disabled');
            $("e_usertype").attr("disabled",'disabled');
        }
        //打开对话框
        $( "#edit-user-dialog" ).dialog( "open" );
    } else {
        alert(errMsg);
    } 
}

function deleteUser(uid) {
    if(<?php echo $user['usertype']; ?>  != 1) {
        alert("没有操作权限");
        return;
    }
    if(<?php echo $user['userid']; ?>  == uid) {
        alert("不能删除当前用户");
        return;
    }
    if(uid == 1){   
        alert("不能操作默认用户");
        return;
    }
    if (confirm("确定要删除该用户吗？")) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: userUrl,
            data: "type=deleteuser&userid=" + uid,
            success: function(data){
                if(data.codeStatus == 0) {  //获取用户信息成功
                    $("#tr" + uid).fadeOut("slow",function(){
                        $("#tr" + uid).remove(); 
                    });
                } else {
                    alert(data.errorMsg + ", 删除用户失败！");
                }
            }
        });
        
    }   
}

function editDpt(did) {
    if(<?php echo $user['dptid']; ?> != 0 || <?php echo $user['usertype']; ?> != 1) {
        alert("没有操作权限");
        return;
    }
    var suc = false;
    var dptInfo;
    var errMsg = "获取单位信息失败！";
    //请求单位信息
    $.ajax({
        type: "POST",
        async: false,    //注意这里要写成同步
        dataType: "json",
        url: userUrl,
        data: "type=getdpt&dptid=" + did,
        success: function(data){
            if(data.codeStatus == 0) {  //获取单位信息成功
                suc = true;  
                dptInfo = data.result.dpt;  
            } else {
                errMsg = data.errorMsg;
            }
        }
    });
    if (suc) {
        //根据dptInfo改变表单中内容
        $("#e_did").val(did);
        $("#e_dptname").val(dptInfo.dptname);
        $("#e_description").val(dptInfo.description);
        //打开对话框
        $( "#edit-dpt-dialog" ).dialog( "open" );
    } else {
        alert(errMsg);
    }
}

function deleteDpt(did) {
    if(<?php echo $user['dptid']; ?> != 0 || <?php echo $user['usertype']; ?> != 1) {
        alert("没有操作权限");
        return;
    }
    if(<?php echo $user['dptid']; ?> == did) {
        alert("不能删除当前单位");
        return;
    }
    if (confirm("确定要删除该单位吗？")) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: userUrl,
            data: "type=deletedpt&dptid=" + did,
            success: function(data){
                if(data.codeStatus == 0) { 
                    $("#dtr" + did).fadeOut("slow",function(){
                        $("#dtr" + did).remove(); 
                    });
                    //删除单位用户 
                    $(".tr" + did).fadeOut("slow",function(){
                        $(".tr" + did).remove();
                    })
                    //删除添加用户表单中的单位
                    $("#dpt option[value=" + data.result.delDpt.dptid + "]").remove();
                    //删除修改用户表单中的单位
                    $("#e_dpt option[value=" + data.result.delDpt.dptid + "]").remove();
                } else {
                    alert("删除单位失败！");
                }
            }
        }); 
    }
}

function resetPw() {
    var oldpw = $("#oldpw");
    var newpw = $("#newpw");
    var newpw2 = $("#newpw2");
    //修改密码
    $.ajax({
        type: "POST",
        dataType: "json",
        url: userUrl,
        data: "type=resetpw&oldpw=" + oldpw.val() + "&newpw=" + newpw.val() + "&newpw2=" + newpw2.val(),
        success: function(data){
            if(data.codeStatus == 0) {  //修改成功
                alert("修改密码成功！");
                oldpw.val("");
                newpw.val("");
                newpw2.val("");
            } else {
                alert(data.errorMsg);
            }
        }
    });
}
</script>
</head>

<body>
    <div class="layout">
        <div class="layout-left">
          <div class="layout-header">
            <div class="layout-header-toggle"></div>
            <div class="layout-header-inner">用户权限</div>
          </div>
          <div class="layout-content accordion-panel">
            <ul>
                <li class="menu-active">用户管理</li>
                <li>单位管理</li>
                <li>修改密码</li>
            </ul>
          </div>
        </div>

        <div class="layout-center">    
            <!-- 用户管理 -->
            <div id="page0" class="center-page">
                <button id="add-user"></button>
                <table id="user-list" class="table-list">
                    <tr class="table-list-title">
                        <td class="user-list-username">用户名</td>
                        <td class="user-list-dpt">单位</td>
                        <td class="user-list-type">权限</td>
                        <!--<td class="user-list-gender">性别</td>-->
                        <td class="user-list-option">编辑</td>
                    </tr>
                    <tbody class="tbody-list" id="tbody-user-list">
                    <?php foreach ($users as $user) { ?>
                    <tr id="tr<?=$user['userid']?>" class="tr<?=$user['dptid']?>">
                        <td><?=$user['username']?></td>
                        <td>
                        <? if($user['dptid'] == 0){ echo "全网"; }
                           else {
                             foreach ($dpts as $dpt) {
                                  if($dpt['dptid'] == $user['dptid']) {
                                     echo $dpt['dptname'];
                                  }
                              } 
                           }
                        ?>
                        </td>
                        <td><? if($user['usertype'] == 1){ echo "系统管理员"; } else { echo "普通用户";} ?></td>
                        <!--<td>男</td>-->
                        <td>
                            <input type="button" class="opt-button" value="修改" onclick="editUser(<?=$user['userid']?>)" />&nbsp;<input type="button" class="opt-button" value="删除" onclick="deleteUser(<?=$user['userid']?>)" />
                        </td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- 单位管理 -->
            <div id="page1" class="center-page">
                <button id="add-dpt"></button>
                <table id="dpt-list" class="table-list">
                    <tr class="table-list-title">
                        <td class="dpt-list-dptname">单位名</td>
                        <td class="dpt-list-description">单位描述</td>
                        <td class="dpt-list-option">编辑</td>
                    </tr>
                    <tbody class="tbody-list" id="tbody-dpt-list">
                    <?php foreach ($dpts as $dpt) { ?>
                    <tr id="dtr<?=$dpt['dptid']?>">
                        <td><?=$dpt['dptname']?></td>
                        <td><?=$dpt['description']?></td>
                        <td>
                            <input type="button" class="opt-button" value="修改" onclick="editDpt(<?=$dpt['dptid']?>)" />&nbsp;<input type="button" class="opt-button" value="删除" onclick="deleteDpt(<?=$dpt['dptid']?>)" />
                        </td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <!-- 修改密码 -->
            <div id="page2" class="center-page">
                <div class="group">
                    <img src="<?php echo base_url(); ?>application/views/images/tabpage/communication.gif">
                    <span>修改密码</span>
                </div>
                <ul>
                    <li><label>原密码：</label><input type="password" id="oldpw" /></li>
                    <li><label>新密码：</label><input type="password" id="newpw" /></li>
                    <li><label>再次输入新密码：</label><input type="password" id="newpw2" /></li>
                </ul>
                <input type="button" class="save-button" id="button1" value="保存" onclick="resetPw()">
            </div>
        </div> 

        <div class="layout-collapse-left" style="display:none">
            <div class="layout-collapse-left-toggle"></div>
        </div>
        
        <div class="footer"></div>
    </div>

    <!--Dialog : Add New User-->
    <div id="add-user-dialog" class="dialog" title="添加新用户">
        <p class="validateTips" id="add-user-tips">请填写以下内容：</p>
        <form>
            <table>
                <tr><td>用户名：</td>
                    <td><input type="text" name="name" id="name" /></td>
                </tr>
                <tr><td>密码：</td>
                    <td><input type="text" name="password" id="password" /></td>
                </tr>
                <tr><td>单位：</td>
                    <td>
                    <select name="dpt" id="dpt">

                        <?php if($this->_userdata['dpt'] == 0) {?><option value=0>全网</option><?php } ?>
                        <?php foreach ($dpts as $dpt) { ?>
                        <option value=<?=$dpt['dptid']?>><?=$dpt['dptname']?></option>
                        <?php } ?>
                    </select>
                    </td>
                </tr>
                <tr><td>权限：</td>
                    <td>
                    <select name="usertype" id="usertype">
                    <option value=1>系统管理员</option>
                    <option value=2>普通用户</option>
                    </select>
                    </td>
                </tr>
                <!--<tr><td>性别：</td>
                    <td>
                    <select name="usergener" id="usergender">
                    <option value=1>男</option>
                    <option value=0>女</option>
                    </select>
                    </td>
                </tr>-->
            </table>
        </form>
    </div>
    <!--Dialog : Edit User-->
    <div id="edit-user-dialog" class="dialog" title="修改用户信息">
        <p class="validateTips" id="edit-user-tips"></p>
        <form>
            <table>
                <input type="hidden" value="" id="e_uid" />
                <tr><td>用户名：</td>
                    <td><input type="text" name="e_name" id="e_name" readonly /></td>
                </tr>
                <tr><td>密码：</td>
                    <td><input type="text" name="e_password" id="e_password" /></td><td>&nbsp;留空则不修改</td>
                </tr>
                <tr><td>单位：</td>
                    <td>
                    <select name="dpt" id="e_dpt">

                        <?php if($this->_userdata['dpt'] == 0) {?><option value=0>全网</option><?php } ?>
                        <?php foreach ($dpts as $dpt) { ?>
                        <option value=<?=$dpt['dptid']?>><?=$dpt['dptname']?></option>
                        <?php } ?>
                    </select>
                    </td>
                </tr>
                <tr><td>权限：</td>
                    <td>
                    <select name="usertype" id="e_usertype">
                        <option value=1>系统管理员</option>
                        <option value=2>普通用户</option>
                    </select>
                    </td>
                </tr>
                <!--<tr><td>性别：</td>
                    <td>
                    <select name="usergener" id="e_usergender">
                        <option value=1>男</option>
                        <option value=0>女</option>
                    </select>
                    </td>
                </tr>-->
            </table>
        </form>
    </div>

    <!--Dialog : Add New Dpt-->
    <div id="add-dpt-dialog" class="dialog" title="添加新单位">
        <p class="validateTips" id="add-dpt-tips">请填写以下内容：</p>
        <form>
            <table>
                <tr><td>单位名：</td>
                    <td><input type="text" name="dptname" id="dptname" /></td>
                </tr>
                <tr><td>描述：</td>
                    <td><textarea name="description" id="description"></textarea></td>
                </tr>
            </table>
        </form>
    </div>

    <!--Dialog : Edit Dpt-->
    <div id="edit-dpt-dialog" class="dialog" title="修改单位信息">
        <p class="validateTips" id="edit-dpt-tips">请填写以下内容：</p>
        <form>
            <table>
                <input type="hidden" value="" id="e_did" />
                <tr><td>单位名：</td>
                    <td><input type="text" name="e_dptname" id="e_dptname" readonly /></td>
                </tr>
                <tr><td>描述：</td>
                    <td><textarea name="e_description" id="e_description"></textarea></td>
                </tr>
            </table>
        </form>
    </div>

</body>