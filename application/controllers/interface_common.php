<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Interface_common extends CI_Controller {

  /**
   * 登陆用户信息
   *
   * @array(userid, username, usertype, dpt)
   * @access public
   */
  public $_userdata;

  public $_cfg_obj;
  public $_protocol;
  public $_ishq;

	public function __construct()
  {
    parent::__construct(); 

    $this->_cfg_obj = $this->sys_config->get_cfg_obj();
    $this->_protocol = $this->_cfg_obj->localSet->protocol;
    $this->_ishq = $this->_cfg_obj->localSet->isHQ;
  }

  /**
   * 登陆接口
   *
   * @access public
   * @param POST username password remember
   * @return json string
   */
  public function login()
  {
    $username = $this->input->post('username', TRUE);
    $password = $this->input->post('password', TRUE);
    $remember = $this->input->post('remember', TRUE);
    $returnData = array();
    if ($username && $password) {     
      $password = md5($password);
      //检查用户信息
      $this->load->model("user");
      $user = $this->user->checkLogin($username, $password);
      $returnData['codeStatus'] = $user['success'];
      $sessionData = $this->session->all_userdata();
      if ($returnData['codeStatus'] == 0) {
        //登陆成功，写入session
        $newdata = array('userid' =>$user['userid'],
                         'username'  => $user['username'],
                         'usertype'     => $user['usertype'],
                         'dpt' => $user['dpt']
                   );
        //记录系统日志
        $this->load->model('sys_log');
        //2014-08-28:日志
        $typeparam = $user['usertype'] == 1 ? "系统" : "普通";
        $this->sys_log->add_sys_log(1, array($typeparam, $user['username'], $sessionData['ip_address']) );
        //$this->sys_log->add_sys_log(1, array($user['username'], $sessionData['ip_address']) );
        //TODO: 处理remember CodeIgniter如何更改session有效期？
        if ($remember == 0) {
          $this->config->set_item('sess_expire_on_close', true);
        }
        $this->session->set_userdata($newdata);
        $returnData['result']['user'] = $user;
      } else {  //登陆失败
        if ($returnData['codeStatus'] == -1) {
          $returnData['errorMsg'] = "密码输入错误！";
        } elseif ($returnData['codeStatus'] == -2) {
          $returnData['errorMsg'] = "该用户名不存在！";
        }
        //记录系统日志
        $this->load->model('sys_log');
        $this->sys_log->add_sys_log(2, array($username, $sessionData['ip_address']) ) ; 
      }
      echo json_encode($returnData);
      exit;
    } else {
      $returnData['codeStatus'] = -99;
      $returnData['errorMsg'] = "缺少参数";
      echo json_encode($returnData);
      exit;
    }
  }

  /**
   * 登出接口
   *
   * @access public
   * @param 无
   * @return json string
   */
  public function logout()
  {
    $returnData = array();
    //记录系统日志
    $userid = $this->session->userdata('userid');
    if(!empty($userid)) {
      $this->load->model('sys_log');
      $sessionData = $this->session->all_userdata();
      //2014-08-28
      $typeparam = $this->session->userdata('username') == 1 ? "系统" : "普通";
      $this->sys_log->add_sys_log(3, array($typeparam, $this->session->userdata('username'), $sessionData['ip_address']) );
      //$this->sys_log->add_sys_log(3, array($this->session->userdata('username'),$sessionData['ip_address']) );
    }
    
    //删除session数据
    $array_items = array('userid' => '', 'username' => '', 'usertype' => '', 'dpt' => '');
    $this->session->unset_userdata($array_items);
    //注销session
    $this->session->sess_destroy();

    $returnData['codeStatus'] = 0;
    $returnData['errorMsg'] = "登出系统成功";
    echo json_encode($returnData);
    exit;
  }

  /**
   * 前端请求配置接口
   *
   * @access public
   * @param 无
   * @return json string
   */
  public function get_init()
  {
  	echo $this->sys_config->get_init_str();
  	exit;
  }

  //处理告警
  public function update_warning_solved()
  {
    //refined:2014.02.16 ospf总部分部waring表结构不一样，总部多as_num字段
    $alarm_id = $this->input->get('id', TRUE);
    $domain = $this->input->get('domain', TRUE);
    //ospf告警状态处理
    if($this->_protocol == "ospf") {
      $this->load->model('warning');
      $this->warning->construct($this->_protocol, $this->_ishq);
      $return = $this->warning->setWarningStatus($domain, $alarm_id, 1); //置为已处理
      if ($return) {
        echo "ok";
        exit;
      } else {
        echo "failed";
        exit;
      }
    } 
    //isis告警状态处理
    else {
      $this->load->model('isis_warning');
      $return = $this->isis_warning->setWarningStatus($alarm_id, 1);
      if ($return) {
        echo "ok";
        exit;
      } else {
        echo "failed";
        exit;
      }
    }
    
  }

  //获取链路上的业务流量信息
  public function get_protocol_traffic()
  {
    $this->load->database();  //默认DB
    $pid = $this->input->get('pid', TRUE);
    $link1 = $this->input->get('link1', TRUE);
    $link2 = $this->input->get('link2', TRUE);
    $retArr = array('A2B' => array(), 'B2A' => array());
    $tbname = '';
    //ospf总部流量拓扑分表的情况
    //其他，不分表的情况
    if($this->_ishq && $this->_protocol == "ospf") {
      $tbname = "hz_ospf_traffic_topo";
    }
    else {
      $tbname = $this->_protocol.'_traffic_topo';
    }
    //根据link1查业务流量
    $this->db->select('protocol_bytes');
    $this->db->from($tbname);
    $this->db->where('id', $link1);
    $this->db->where('pid', $pid);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
      if (!empty($query->row()->protocol_bytes)) {
        $A2B = json_decode($query->row()->protocol_bytes);
        $retArr['A2B'] = $A2B;
      } 
    }
    //根据link2查业务流量
    $this->db->select('protocol_bytes');
    $this->db->from($tbname);
    $this->db->where('id', $link2);
    $this->db->where('pid', $pid);
    $query = $this->db->get();
    if ($query->num_rows() > 0) {
       if (!empty($query->row()->protocol_bytes)) {
        $B2A = json_decode($query->row()->protocol_bytes);
        $retArr['B2A'] = $B2A;
      } 
    }
    echo json_encode($retArr);
    exit;
  }
  public function get_protocol_traffic1()
  {
    $this->load->database();  //默认DB
    $pid = $this->input->get('pid', TRUE);
    $link1 = $this->input->get('link1', TRUE);
    $link2 = $this->input->get('link2', TRUE);
    $retArr = array('A2B' => array(), 'B2A' => array());
    $tbname = '';
    //ospf总部流量拓扑分表的情况
    if ($this->_ishq && $this->_protocol == "ospf") {
      $trafficDB = $this->load->database('traffic', TRUE);
      $this->load->model('ospf_hq/traffic_topo');
      $tbname = $this->traffic_topo->get_table_name($pid);
      //echo $tbname;exit;
      //根据link1查业务流量

      $trafficDB->select('protocol_bytes');
      $trafficDB->from($tbname);
      $trafficDB->where('id', $link1);
      $trafficDB->where('pid', $pid);
      $query = $trafficDB->get();
      if ($query->num_rows() > 0) {
        if (!empty($query->row()->protocol_bytes)) {
          $A2B = json_decode($query->row()->protocol_bytes);
          $retArr['A2B'] = $A2B;
        } 
      }
      //根据link2查业务流量
      $trafficDB->select('protocol_bytes');
      $trafficDB->from($tbname);
      $trafficDB->where('id', $link2);
      $trafficDB->where('pid', $pid);
      $query = $trafficDB->get();
      if ($query->num_rows() > 0) {
         if (!empty($query->row()->protocol_bytes)) {
          $B2A = json_decode($query->row()->protocol_bytes);
          $retArr['B2A'] = $B2A;
        } 
      }
    } 
    //其他，不分表的情况
    else {
      $tbname = $this->_protocol.'_traffic_topo';
      //根据link1查业务流量
      $this->db->select('protocol_bytes');
      $this->db->from($tbname);
      $this->db->where('id', $link1);
      $this->db->where('pid', $pid);
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
        if (!empty($query->row()->protocol_bytes)) {
          $A2B = json_decode($query->row()->protocol_bytes);
          $retArr['A2B'] = $A2B;
        } 
      }
      //根据link2查业务流量
      $this->db->select('protocol_bytes');
      $this->db->from($tbname);
      $this->db->where('id', $link2);
      $this->db->where('pid', $pid);
      $query = $this->db->get();
      if ($query->num_rows() > 0) {
         if (!empty($query->row()->protocol_bytes)) {
          $B2A = json_decode($query->row()->protocol_bytes);
          $retArr['B2A'] = $B2A;
        } 
      }
    }
    echo json_encode($retArr);
    exit;
  }

  public function flow_ip7()
  {
    $query_obj = array();
    $query_obj['type'] = 'ip7';
    $query_obj['params'] = array(
      'stpid' => 201406121517,
      'edpid' => 201406121519,
      'srcIp' => '52.2.14.2',
      'dstIp' => '52.1.4.1',
      'srcPort' => 4242,
      'dstPort' => 80,
      'protocol' => 17
    );
    $query_str = json_encode($query_obj);
    $this->load->model('flow');
    echo $this->flow->local_query($query_str);
    exit;
  }

  //流查询
  public function flow_query()
  {
    $ishq = $this->input->get('ishq', TRUE);                // 0/1
    $isborder = $this->input->get('isborder', TRUE);        // 0/1
    $query_type = $this->input->get('query_type', TRUE);    // flow/linkip2/linkip7/linkprotocol/path
    $domainA = $this->input->get('domainA', TRUE);          // 必有值 分部是本AS，总部是查询所在AS
    $domainB = $this->input->get('domainB', TRUE);          // isborder==1且query_type==linkip2时有值，此时要向两个AS请求查询
    $query_str = $this->input->get('query_str', TRUE);      // 和流量设备的接口
    $query_str = urldecode($query_str);
    $query_obj = json_decode($query_str, true);

    /////////////////测试用////////////////
    // $group = isset($query_obj['group'])?$query_obj['group']:'';
    // echo $this->flow_test($ishq, $query_type, $isborder, $query_str);
    // exit;

    /////////////////实际用////////////////
    $this->load->model('flow');
    //入口1.总部路径查询
    if ($ishq == 1 && $query_type == "path") {
      $new_query_obj = array();
      $new_query_obj["type"] = "path";
      $new_query_obj["params"] = $query_obj;
      $query_str = json_encode($new_query_obj);
      echo $this->flow->local_query($query_str);
      exit;
    }
    //特殊处理1.如果是流查询，需要把起止时间改成起止pid
    if ($query_type == "flow") {
      $params = $query_obj['params'];
      $sttime = $params['sttime']; $edtime = $params['edtime'];
      $stpid = $edpid = 0;
      if ($this->_ishq == 0 && $this->_protocol == 'ospf') {
        $this->load->model('ospf_local/traffic_topo');
        $stpid = $this->traffic_topo->get_pid_by_timestamp($sttime);
        $edpid = $this->traffic_topo->get_pid_by_timestamp($edtime);
      } elseif ($this->_ishq == 1 && $this->_protocol == 'ospf') {
        $this->load->model('ospf_hq/traffic_topo');
        $stpid = $this->traffic_topo->get_pid_by_timestamp($sttime);
        $edpid = $this->traffic_topo->get_pid_by_timestamp($edtime);
      } 
	  if ($this->_protocol == 'isis') {
        $this->load->model('isis/traffic_topo');
        $stpid = $this->traffic_topo->get_pid_by_timestamp($sttime);
        $edpid = $this->traffic_topo->get_pid_by_timestamp($edtime);
      }
      //检查查询起止时间有没有超出当前已有pid范围
      // $intv = $this->_cfg_obj->localSet->interval;
      // if (abs(strtotime($stpid) - $sttime)/60 > 2*$intv) {
      //   $retObj = array("codeStatus" => -1,
      //                   "errorMsg" => "查询起始时间超出范围！");
      //   echo json_encode($retObj);exit;
      // }
      // if (abs(strtotime($edpid) - $edtime)/60 > 2*$intv) {
      //   $retObj = array("codeStatus" => -1,
      //                   "errorMsg" => "查询结束时间超出范围！");
      //   echo json_encode($retObj);exit;
      // }
      unset($params['sttime']); unset($params['edtime']);
      $params['stpid'] = $stpid; $params['edpid'] = $edpid;
      unset($query_obj['params']);
      $query_obj['params'] = $params;
      $query_str = json_encode($query_obj);
    }
    //入口2.如果是边界链路上的IP二元组聚合查询，需要向两个AS都提交查询请求，组合结果
    if($ishq == 1 && $query_type == "linkip2" && $isborder == 1) {
      $retObj = array("codeStatus" => 0,
                      "errorMsg" => "查询失败");
      //$obverseRes = json_decode($this->flow->cross_domain_query($domainB, $query_str));
      //$reverseRes = json_decode($this->flow->cross_domain_query($domainA, $query_str));
      $obverseRes = json_decode($this->flow->cross_domain_query($domainA, $query_str));
      $reverseRes = json_decode($this->flow->cross_domain_query($domainB, $query_str));
      //echo $obverseRes;
      //var_dump($reverseRes1->reverse);
      if (is_null($obverseRes) || $obverseRes->codeStatus != 0) {
        $retObj["result"]["obverse"] = array();
      } else {
        $retObj["result"]["obverse"] = $obverseRes->result->obverse;
      }
      if (is_null($reverseRes) || $reverseRes->codeStatus != 0) {
        $retObj["result"]["reverse"] = array();
      } else {
        $retObj["result"]["reverse"] = $reverseRes->result->reverse;
      }
      echo json_encode($retObj);
      exit;
    } 
    //其他正常情况
    if ($ishq == 1 && !empty($domainA)) {
      echo $this->flow->cross_domain_query($domainA, $query_str);
      exit;
    } else {
      echo $this->flow->local_query($query_str);
      exit;
    }
  }

  //修改系统配置
  public function modifyConfig()
  {
    $type = $this->input->post('type', TRUE);
    $data = $this->input->post('data', TRUE);
    $json_params = json_decode($data, true);
    $ret = array();
    if (empty($type) || empty($json_params)) {

    } 
    
    switch ($type) {
      //协议，是否总部
      case 1:
        $this->_cfg_obj->localSet->protocol = $json_params['protocol'];
        $this->_cfg_obj->localSet->asNum = intval($json_params['asNum']);
        $this->_cfg_obj->localSet->areaId = $json_params['areaId'];
        $this->_cfg_obj->localSet->isHQ = intval($json_params['isHQ']);
        $this->sys_config->save_cfg_obj($this->_cfg_obj);
        $ret['codeStatus'] = 0;
        $ret['msg'] = "网络基本配置已更新，请重新加载界面使新配置生效！";
        break;

      //interval等本地参数
      case 2:
        $this->_cfg_obj->localSet->interval = intval($json_params['interval']);
        $this->_cfg_obj->localSet->inAdvance = intval($json_params['inAdvance']);
        $this->_cfg_obj->localSet->topN = intval($json_params['topN']);
        $this->_cfg_obj->localSet->localFlowQueryPort = intval($json_params['localFlowQueryPort']);
        $this->_cfg_obj->localSet->baseUrl = $json_params['baseUrl'];
        $this->_cfg_obj->localSet->localIp = $json_params['localIp'];
        $this->sys_config->save_cfg_obj($this->_cfg_obj);
        $ret['codeStatus'] = 0;
        $ret['msg'] = "网络基本配置已更新，请重新加载界面使新配置生效！";
        break;

      //ospf边界链路
      case 3:
        $this->load->model('bgp_link_info');
        $this->bgp_link_info->update_bgp_links($json_params);
        $this->bgp_link_info->sync_border_links();
        $ret['codeStatus'] = 0;
        //$ret['msg'] = json_encode($json_params);
        $ret['msg'] = "边界链路配置已更新，请重新加载界面使新配置生效！";
        break;

      //isis level 1/2路由器ip映射
      case 35:
        $this->_cfg_obj->localSet->isisIpIdMapping = $json_params;
        $this->sys_config->save_cfg_obj($this->_cfg_obj);
        $ret['codeStatus'] = 0;
        $ret['msg'] = "ISIS Level 1/2路由器IP映射配置已更新，系统将自动启用新配置！";
        break;

      //监测业务
      case 4:
        $this->_cfg_obj->localSet->observePorts = $json_params;
        $this->sys_config->save_cfg_obj($this->_cfg_obj);
        $ret['codeStatus'] = 0;
        $ret['msg'] = "监测业务端口已更新，系统将自动启用新配置！";
        break;

      //数据库配置
      case 5:
        $this->_cfg_obj->localSet->localDBSet->ip = $json_params['dbip'];
        $this->_cfg_obj->localSet->localDBSet->port = $json_params['dbport'];
        $this->_cfg_obj->localSet->localDBSet->dbname = $json_params['dbname'];
        $this->_cfg_obj->localSet->localDBSet->username = $json_params['dbuser'];
        $this->_cfg_obj->localSet->localDBSet->password = $json_params['dbpw'];   
        $this->sys_config->save_cfg_obj($this->_cfg_obj); 
        $ret['codeStatus'] = 0;
        $ret['msg'] = "数据库配置已更新，系统将自动启用新配置！";
        break;    

      //综合分析设备
      case 6:
        $this->_cfg_obj->localSet->gloabalAnalysisSet->globalAnalysisIp = $json_params['globalAnalysisIp'];
        $this->_cfg_obj->localSet->gloabalAnalysisSet->globalAnalysisPort = intval($json_params['globalAnalysisPort']);
        $this->sys_config->save_cfg_obj($this->_cfg_obj);
        $ret['codeStatus'] = 0;
        $ret['msg'] = "综合分析设备通信设置已更新，请确认综合分析端运行程序对应该IP地址与端口，否则可能导致流量视图异常！";
        break;

      //流量汇集设备参数
      case 7:
        $this->_cfg_obj->localSet->flowDeviceSet->configPort = intval($json_params['flowDeviceConfigPort']);
        $this->_cfg_obj->localSet->flowDeviceSet->topoPort = intval($json_params['flowDeviceTopoPort']);
        $this->_cfg_obj->localSet->flowDeviceSet->flowPort = intval($json_params['flowDeviceFlowPort']);
        $this->_cfg_obj->localSet->flowDeviceSet->samplingRate = intval($json_params['flowDeviceSamplingRate']);
        $this->sys_config->save_cfg_obj($this->_cfg_obj);
        $ret['codeStatus'] = 0;
        $ret['msg'] = "流量设备通信设置已更新，请确认流量设备端运行程序对应该端口，否则可能导致流量视图异常！";
        break;

      //流量汇集设备列表
      case 8:
        $devIpList = array();
        foreach ($json_params as $key => $value) {
          $devIpList[] = $value['devIp'];
        }
        $this->_cfg_obj->localSet->flowDeviceSet->devInfo = $json_params;
        $this->_cfg_obj->localSet->flowDeviceSet->ipList = $devIpList;
        $this->sys_config->save_cfg_obj($this->_cfg_obj);
        $ret['codeStatus'] = 0;
        $ret['msg'] = "流量设备列表已更新！";
        break;
		
      //流量汇集设备参数
      case 11:
        $this->_cfg_obj->localSet->flowDeviceSet->url_Level1 = $json_params['url_Level1'];
        $this->_cfg_obj->localSet->flowDeviceSet->url_Level2 = $json_params['url_Level2'];
        $this->sys_config->save_cfg_obj($this->_cfg_obj);
        $ret['codeStatus'] = 0;
        $ret['msg'] = "汇报设备地址设置已更新！";
        break;
		
      //样式自定义
      case 9:
        $this->_cfg_obj->styleSet->canvasWidth = intval($json_params['canvasWidth']);
        $this->_cfg_obj->styleSet->canvasHeight = intval($json_params['canvasHeight']);
        $this->sys_config->save_cfg_obj($this->_cfg_obj);
        $ret['codeStatus'] = 0;
        $ret['msg'] = "样式已重设置，请重新加载界面使新配置生效！";
        break;

      //分部配置
      case 10:
        if ($this->_protocol == "ospf") {
          $this->_cfg_obj->HQSet->asSet = array();
          foreach ($json_params as $key => $value) {
            $as = array();
            $as['asNum'] = $value['fbname'];
            $as['asIp'] = $value['fbip'];
            $this->_cfg_obj->HQSet->asSet[] = $as;
          }
        } else {
          $this->_cfg_obj->HQSet->areaSet = array();
          foreach ($json_params as $key => $value) {
            $area = array();
            $area['areaId'] = $value['fbname'];
            $area['areaIp'] = $value['fbip'];
            $this->_cfg_obj->HQSet->areaSet[] = $area;
          }
        }
        $this->sys_config->save_cfg_obj($this->_cfg_obj);
        $ret['codeStatus'] = 0;
        $ret['msg'] = "分部设置已更新，系统将自动启用新配置！！";
        break;
        
      default:
        break;
    }
    echo json_encode($ret);
    exit;
  }

  //用户管理
  public function userAjax()
  {
    $data = array();
    //判断是否登陆
    $userid = $this->session->userdata('userid');
    $usertype = $this->session->userdata('usertype');
    $dpt = $this->session->userdata("dpt");
    $type = $this->input->post('type', TRUE);
    if (empty($userid)) {
        $data['codeStatus'] = -1;   //未登录
        $data['errorMsg'] = "未登录";
    } else if ( $usertype != 1) {
        $data['codeStatus'] = -2;   //权限不够
        $data['errorMsg'] = "没有操作权限";

        //修改密码
        if($type == 'resetpw') {

          $oldpw = $this->input->post('oldpw');
          $newpw = $this->input->post('newpw');
          $newpw2 = $this->input->post('newpw2');
          if ($newpw != $newpw2) {
              $data['codeStatus'] = -3;
              $data['errorMsg'] = "两次输入密码不一致！";
          } else {
              $oldpw = md5($oldpw);
              $this->load->model("user");
              $user = $this->user->checkLogin($this->session->userdata('username'), $oldpw);
              if ($user['success'] != 0) {
                  $data['codeStatus'] = -4;
                  $data['errorMsg'] = "原密码错误！";
              } else {
                  $euser['userid'] = $userid;
                  $euser['username'] = $this->session->userdata('username');
                  $euser['password'] = md5($newpw);
                  $euser['dptid'] = $this->session->userdata('dpt');
                  $euser['usertype'] = $this->session->userdata('usertype');
                  $this->user->updateUser($euser);
                  $data['codeStatus'] = 0;
              }
          }
        }

    } else {
        switch ($type) {
          //检查有没有重名用户名
          case 'checkusername':
              $uname = $this->input->post('username', TRUE);
              $this->load->model("user");
              $user = $this->user->getUserByName($uname);
              if ($user) {
                  $data['codeStatus'] = -3;   //用户名不可用
                  $data['errorMsg'] = "该用户名已存在";
              } else {
                  $data['codeStatus'] = 0;    //用户名可用
              }
              break;

          //删除用户
          case 'deleteuser':
              $uid = $this->input->post('userid');
              $this->load->model("user");
              $this->user->deleteUser($uid);
              $data['codeStatus'] = 0;
              break;

          //增加用户
          case 'adduser':
              $uname = $this->input->post('username');
              $upw = $this->input->post('password');
              $udpt = $this->input->post('dpt');
              $utype = $this->input->post('usertype');
              //$ugender = $this->input->post('usergender');
              $this->load->model("user");
              $uid = $this->user->addUser($uname, $upw, $udpt, $utype);
              if ($uid) {
                  $data['codeStatus'] = 0;
                  $data['result']['addUser'] = array('userid' => $uid);
              } else {
                  $data['codeStatus'] = -3;
                  $data['errorMsg'] = "插入数据库失败！";
              }
              break;

          //修改用户信息
          case 'edituser':
              $euser['userid'] = $this->input->post('userid');
              $euser['username'] = $this->input->post('username');
              $pw = $this->input->post('password');
              if (isset($pw) && (!is_null($pw)) && (!empty($pw)) ) {
                  $euser['password'] = md5($this->input->post('password'));
              }
              $euser['dptid'] = $this->input->post('dpt');
              $euser['usertype'] = $this->input->post('usertype');
              //$euser['usergender'] = $this->input->post('usergender');
              $this->load->model("user");
              $this->user->updateUser($euser);
              $data['codeStatus'] = 0;
              break;

          //查询某一用户信息
          case 'getuser':
              $uid = $this->input->post('userid');
              $this->load->model("user");
              $user = $this->user->getUser($uid);
              if ($user) {
                  $data['codeStatus'] = 0;
                  $data['result']['user'] = $user;
              } else {
                  $data['codeStatus'] = -3;
                  $data['errorMsg'] = "获取用户信息失败！";
              }
              break;

          /*--------------------------------------------*/

          //检查有没有重名单位名
          case 'checkdptname':
              $dptname = $this->input->post('dptname', TRUE);
              $this->load->model("dpt");
              $dpt = $this->dpt->getDptByName($dptname);
              if ($dpt) {
                  $data['codeStatus'] = -3;   //单位名不可用
                  $data['errorMsg'] = "用户名已存在";
              } else {
                  $data['codeStatus'] = 0;    //单位名可用
              }
              break;

          //删除单位
          case 'deletedpt':
              $dptid = $this->input->post('dptid');
              $this->load->model("dpt");
              $this->dpt->deleteDpt($dptid);
              $data['codeStatus'] = 0;
              $data['result']['delDpt'] = array('dptid' => $dptid);
              break;
          
          //增加单位
          case 'adddpt':
              $dname = $this->input->post('dptname');
              $ddes = $this->input->post('description');
              $this->load->model("dpt");
              $did = $this->dpt->addDpt($dname, $ddes);
              if ($did) {
                  $data['codeStatus'] = 0;
                  $data['result']['addDpt'] = array('dptid' => $did, 'dptname' => $dname);
              } else {
                  $data['codeStatus'] = -3;
                  $data['errorMsg'] = "插入数据库失败！";
              }
              break;

          //修改单位信息
          case 'editdpt':
              $edpt['dptid'] = $this->input->post('dptid');
              $edpt['dptname'] = $this->input->post('dptname');
              $edpt['description'] = $this->input->post('description');
              $this->load->model("dpt");
              $this->dpt->updateDpt($edpt);
              $data['codeStatus'] = 0;
              $data['result']['editDpt'] = array('dptid' => $edpt['dptid'], 'dptname' => $edpt['dptname']);
              break;

          //查询某一单位信息
          case 'getdpt':
              $did = $this->input->post('dptid');
              $this->load->model("dpt");
              $dpt = $this->dpt->getDpt($did);
              if ($dpt) {
                  $data['codeStatus'] = 0;
                  $data['result']['dpt'] = $dpt;
              } else {
                  $data['codeStatus'] = -3;
                  $data['errorMsg'] = "获取单位信息失败！";
              }
              break;

          default:
              $data['codeStatus'] = -100;
              $data['errorMsg'] = "未定义的请求";
              break;
        }
    }
    echo json_encode($data);
    exit;
  }

  private function flow_test($ishq, $qtype, $isborder, $qstr)
  {
    $retArr = array("codeStatus" => 0,
                    "errorMsg" => "出错啦",
                    "msg" => "");
    $flow_array = array();
    for ($i=0; $i<10; $i++) {
      $flow = array("srcIp" => "192.168.0.1",
                    "dstIp" => "192.168.0.".$i,
                    "srcPort" => 1234,
                    "dstPort" => 80,
                    "srcPrefix" => "192.168.0.0",
                    "dstPrefix" => "192.168.0.0",
                    "srcAs" => 21,
                    "dstAs" => 25,
                    "protocal" => 6,
                    "index" => 21,
                    "tos" => 1,
                    "bytes" => rand((10-$i-1)*10, (10-$i)*10));
      if ($i % 4 == 0) {
        $path = "5.5.5.5|6.6.6.6";
      } elseif ($i % 4 == 1) {
        $path = "1.1.2.3|1.1.2.1|1.1.3.1|1.1.3.4";
      } elseif ($i % 4 == 2) {
        $path = "1.1.4.2|1.1.4.1|1.1.1.1|6.6.6.6|5.5.5.5|5.5.1.1|5.5.1.4";
      } elseif ($i % 4 == 3) {
        $path = "3.3.1.3|3.3.1.1|3.3.3.3|4.4.4.4|5.5.5.5|6.6.6.6|6.6.2.1|6.6.2.3";
      }
      $flow["path"] = $path;
      $flow_array[] = $flow;
    }
    //流查询
    if ($qtype == "flow") {
      $retArr["result"] = $flow_array;
      return json_encode($retArr);
    }
    //ip二元组
    if ($qtype == "linkip2") {
      $res_obj = array("routerA" => "5.5.5.5",
                       "routerB" => "8.8.8.8",
                       "obverse" => $flow_array,
                       "reverse" => $flow_array);
      $retArr["result"] = $res_obj;
      return json_encode($retArr);
    }
    //ip七元组
    if ($qtype == "linkip7") {
      $retArr["result"] = $flow_array;
      return json_encode($retArr);
    }
    //业务类型分析
    if ($qtype == "linkprotocol") {
      $retArr["result"] = $flow_array;
      return json_encode($retArr);
    }
    //总部路径
    if ($ishq == 1 && $qtype == "path") {
      $asPath1 = array("AS" => 21, "path" => array("21.144.0.2|21.144.0.7"));
      $asPath2 = array("AS" => 25, "path" => array("25.92.0.5|25.100.0.2|25.100.0.1"));
      $retArr["result"] = array($asPath1, $asPath2);
      return json_encode($retArr);
    }
  }

    //获取链路上的业务流量信息
  // public function get_protocol_traffic()
  // {
  //   $this->load->database();
  //   $pid = $this->input->get('pid', TRUE);
  //   $link1 = $this->input->get('link1', TRUE);
  //   $link2 = $this->input->get('link2', TRUE);

  //   $prefix = '';
  //   if ($this->_ishq && $this->_protocol == "ospf") {
  //     $prefix = 'hz_';
  //   }
  //   $retArr = array('A2B' => array(), 'B2A' => array());
  //   //根据link1查业务流量
  //   $this->db->select('protocol_bytes');
  //   $this->db->from($prefix.$this->_protocol.'_traffic_topo');
  //   $this->db->where('id', $link1);
  //   $this->db->where('pid', $pid);
  //   $query = $this->db->get();
  //   if ($query->num_rows() > 0) {
  //     if (!empty($query->row()->protocol_bytes)) {
  //       $A2B = json_decode($query->row()->protocol_bytes);
  //       $retArr['A2B'] = $A2B;
  //     } 
  //   }
  //   //根据link2查业务流量
  //   $this->db->select('protocol_bytes');
  //   $this->db->from($prefix.$this->_protocol.'_traffic_topo');
  //   $this->db->where('id', $link2);
  //   $this->db->where('pid', $pid);
  //   $query = $this->db->get();
  //   if ($query->num_rows() > 0) {
  //      if (!empty($query->row()->protocol_bytes)) {
  //       $B2A = json_decode($query->row()->protocol_bytes);
  //       $retArr['B2A'] = $B2A;
  //     } 
  //   }
  //   echo json_encode($retArr);
  //   exit;
  // }
}