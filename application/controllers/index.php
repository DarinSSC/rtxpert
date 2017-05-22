<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Index extends CI_Controller {
  
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

  private function _pre_control($pagename = "")
  {
    if ($pagename != "") {
      //检查view页面存在情况
      if ( ! file_exists('application/views/'.$pagename.'.php')) {
        show_404();
        exit;
      }
    }
    //判断登陆情况,若已登录，将sesion中用户信息放入数组  
    //  若未登录，跳转回登陆页面
    $userid = $this->session->userdata('userid');
    if (!empty($userid)) {
      $this->_userdata = array('userid' => $userid, 'username' => $this->session->userdata('username'), 'usertype' => $this->session->userdata('usertype'), 'dpt' => $this->session->userdata('dpt'));
    } else {
      redirect(base_url().'index.php/index/login?ref='.urlencode($this->uri->uri_string()));
    } 
  }

  /**
   * 登陆页
   *
   * @access public
   */
  public function login()
  {
    $data['url'] = base_url();
    $ref = $this->input->get('ref', TRUE);  //重定向到之前的页面，若为空，则为首页
    $ref = empty($ref)?$data['url'].'index.php/index/index':$data['url'].'index.php/'.$ref; 
    $data['ref'] = $ref;
    $data['year'] = date("Y");
    $this->load->view('login', $data);
  }

  /**
   * 首页
   *
   * @access public
   */
  public function index()
  {
    $data['localip'] = $this->_cfg_obj->localSet->localIp;
    $this->_pre_control("index");
    $this->load->view('index', $data);
  }

/**
 *  前缀信息统计页面
 *
 * ChuXiaokai
 * @access public
 */
  public function prefixQuery()
  {
      //预检查
      $this->_pre_control('prefixQuery');
      $data = array();
      $data['user'] = $this->_userdata;
      $data['year'] = date("Y");

      //构造分页参数    
      $page = $this->input->get('page', TRUE);
      if(is_null($page) || empty($page)) {
          $page = 1;
      }
      $limit = 10; //每页多少条
      $offset = ($page-1)*$limit;
      $total = 0;
      $cfg['base_url'] = base_url().'index.php?c=index&m=prefixQuery';  // 点击分页跳转的页面

      $this->load->model('prefix');
      //接受参数
      $as_num = $this->input->get("as_num", TRUE);
      if(is_null($as_num) || empty($as_num))//注意，这里empty()函数当传入参数为0时，仍返回true
      {
          $as_num = -1;
      }

      $queryArgs = array();
      if(!is_null($as_num) && $as_num>-1){
          $queryArgs['as_num'] = $as_num;
          $cfg['base_url'] .= '&as_num='.$as_num;
      }
      $cfg['base_url'] .= '&as_num='.$as_num;
//      echo $as_num;
      //在这里构造起止时间传入参数
      $sttime = $this->input->get('sttime', TRUE);
      $edtime = $this->input->get('edtime', TRUE);
      //判断是否传入了时间参数
      if(!(is_null($sttime) || empty($sttime)))//注意，这里empty()函数当传入参数为0时，仍返回true
      {
          $sttime = 0;
          $queryArgs['start_time'] = strtotime($sttime);
      }
      if(!(is_null($edtime) || empty($edtime)))//注意，这里empty()函数当传入参数为0时，仍返回true
      {
          $edtime = 9999999999;
          $queryArgs['end_time'] = strtotime($edtime);
      }

      $result = $this->prefix->getPrefix($queryArgs);
      if($result){
          $data['prefix'] = array_slice($result['data'], $offset, $limit);
          // $data['total'] = $result['total'];
          $total = $result['total'];  // 总页数
          $data['as'] = $result['as'];
      }
      $cfg['base_url'] .= '&edtime='.$edtime;
      $cfg['base_url'] .= '&sttime='.$sttime;

      //分页
      $this->load->library('pagination');
      $cfg['total_rows'] = $total;
      $cfg['per_page'] = $limit;
      $cfg['use_page_numbers'] = TRUE;
      $cfg['page_query_string'] = TRUE;
      $cfg['query_string_segment'] = 'page';
      $cfg['first_link'] = '首页';
      $cfg['last_link'] = '末页';
      $cfg['num_links'] = 2;
      $pagination = $this->pagination->initialize($cfg);

      $this->load->view('prefixQuery', $data);  
}
public function prefixQueryAjax(){
    $this->load->model('prefix');

    //构造分页参数
    $page = $this->input->get('page', TRUE);
    if(is_null($page) || empty($page)) {
        $page = 1;
    }
    $limit = 10; //每页多少条
    $offset = ($page-1)*$limit;
    //接受参数
    $as_num = $this->input->get("as_num", TRUE);
    if(is_null($as_num) || empty($as_num))//注意，这里empty()函数当传入参数为0时，仍返回true
    {
        $as_num = -1;
    }

    $queryArgs = array();
    if(!is_null($as_num) && $as_num>-1){
        $queryArgs['as_num'] = $as_num;
    }
//      echo $as_num;
    //在这里构造起止时间传入参数
    $sttime = $this->input->get('sttime', TRUE);
    $edtime = $this->input->get('edtime', TRUE);
    //判断是否传入了时间参数
    if(!(is_null($sttime) || empty($sttime)))//注意，这里empty()函数当传入参数为0时，仍返回true
    {
        $sttime = 0;
        $queryArgs['start_time'] = strtotime($sttime);
    }
    if(!(is_null($edtime) || empty($edtime)))//注意，这里empty()函数当传入参数为0时，仍返回true
    {
        $edtime = 9999999999;
        $queryArgs['end_time'] = strtotime($edtime);
    }

    $result = $this->prefix->getPrefix($queryArgs);
    if($result){
        $data['prefix'] = array_slice($result['data'], $offset, $limit);
        $data['total_prefix'] = $result['data'];
    }
    echo json_encode($data);
    exit;
}


  /**
   * 告警日志
   *
   * @access public
   */
  public function warninglog()
  {
    //预检查
    $this->_pre_control('warninglog');
    //头部数据
    $data = array();
    $data['user'] = $this->_userdata;
    $data['year'] = date("Y");

    //接收参数
    $page = $this->input->get('page', TRUE);
    if(is_null($page) || empty($page)) {
        $page = 1;
    }
    $limit = 10; //每页多少条
    $offset = ($page-1)*$limit;
    $total = 0;
    $sttime = $this->input->get('sttime', TRUE);
    $edtime = $this->input->get('edtime', TRUE);  
    $warning_name = $this->input->get('warning_name', TRUE);
    $level = $this->input->get('level', TRUE);
    $solved = $this->input->get('solved', TRUE);

    $cfg['base_url'] = base_url().'index.php?c=index&m=warninglog';
    //处理查询参数
    $queryArgs = array();
    $queryArgs['sortBy'] = 'id';
    if (!is_null($edtime) && !empty($edtime)) {
      $queryArgs['edtime'] = strtotime("$edtime +1 day"); //结束日期加1天
      $queryArgs['sortDirection'] = 'desc';
      $cfg['base_url'] .= '&edtime='.$edtime;
    }
    if (!is_null($sttime) && !empty($sttime)) {
      $queryArgs['sttime'] = strtotime($sttime);
      $queryArgs['sortDirection'] = 'asc';
      $cfg['base_url'] .= '&sttime='.$sttime;
    }
    if (!is_null($warning_name) && $warning_name > -1) {
      $queryArgs['name'] = $warning_name;
      $cfg['base_url'] .= '&warning_name='.$warning_name;
    }
    if (!is_null($level) && $level > -1) {
      $queryArgs['level'] = $level;
      $cfg['base_url'] .= '&level='.$level;
    }
    if (!is_null($solved) && $solved > -1) {
      $queryArgs['solved'] = $solved;
      $cfg['base_url'] .= '&solved='.$solved;
    }
    $queryArgs['limit'] = $limit;
    $queryArgs['offset'] = $offset;

    //查询
    if($this->_protocol == "ospf") {
      $this->load->model('warning');
      $this->warning->construct($this->_protocol, $this->_ishq);
      $result = $this->warning->getWarninglogs($queryArgs);
    } else {
      $this->load->model('isis_warning');
      $result = $this->isis_warning->getWarninglogs($queryArgs);
    }
    if ($result) {
        $data['warninglogs'] = $result['data'];
        $total = $result['total'];
    } else {
        $data['warninglogs'] = null;
    }
    //分页
    $this->load->library('pagination');
    $cfg['total_rows'] = $total;
    $cfg['per_page'] = $limit; 
    $cfg['use_page_numbers'] = TRUE;
    $cfg['page_query_string'] = TRUE;
    $cfg['query_string_segment'] = 'page';
    $cfg['first_link'] = '首页';
    $cfg['last_link'] = '末页';
    $cfg['num_links'] = 2;
    $pagination = $this->pagination->initialize($cfg); 
      
    $this->load->view('warninglog', $data);
  }

  /**
   * 系统日志
   *
   * @access public
   */
  public function syslog()
  {
    //预检查
    $this->_pre_control('syslog');
    //头部数据
    $data = array();
    $data['user'] = $this->_userdata;
    $data['year'] = date("Y");

    //接收参数
    $page = $this->input->get('page', TRUE);
    if(is_null($page) || empty($page)) {
        $page = 1;
    }
    $limit = 10; //每页多少条
    $offset = ($page-1)*$limit;
    $total = 0;
    $sttime = $this->input->get('sttime', TRUE);
    $edtime = $this->input->get('edtime', TRUE);

    $cfg['base_url'] = base_url().'index.php?c=index&m=syslog';
    //处理查询参数
    $queryArgs = array();
    $queryArgs['sortBy'] = 'id';
    if (!is_null($edtime) && !empty($edtime)) {
      $queryArgs['edtime'] = strtotime("$edtime +1 day"); //结束日期加1天
      $queryArgs['sortDirection'] = 'desc';
      $cfg['base_url'] .= '&edtime='.$edtime;
    }
    if (!is_null($sttime) && !empty($sttime)) {
      $queryArgs['sttime'] = strtotime($sttime);
      $queryArgs['sortDirection'] = 'asc';
      $cfg['base_url'] .= '&sttime='.$sttime;
    }
    $queryArgs['limit'] = $limit;
    $queryArgs['offset'] = $offset;

    //查询
    $this->load->model('sys_log');
    $result = $this->sys_log->get_sys_logs($queryArgs);
    if ($result) {
        $data['syslogs'] = $result['data'];
        $total = $result['total'];
    } else {
        $data['syslogs'] = null;
    }
    //分页
    $this->load->library('pagination');
    $cfg['total_rows'] = $total;
    $cfg['per_page'] = $limit; 
    $cfg['use_page_numbers'] = TRUE;
    $cfg['page_query_string'] = TRUE;
    $cfg['query_string_segment'] = 'page';
    $cfg['first_link'] = '首页';
    $cfg['last_link'] = '末页';
    $cfg['num_links'] = 2;
    $pagination = $this->pagination->initialize($cfg); 
      
    $this->load->view('syslog', $data);
  }

  /**
   * 用户权限
   *
   * @access public
   */
  public function usermanager()
  {
    //预检查
    $this->_pre_control('usermanager');

    $data = array();
    $data['user'] = array('userid'    =>  $this->_userdata['userid'],
                              'username'  =>  $this->_userdata['username'],
                              'usertype'  =>  $this->_userdata['usertype'],
                              'dptid'     =>  $this->_userdata['dpt']);
    $mydpt = $this->_userdata['dpt'];
    $myusertyp = $this->_userdata['usertype'];
    $data['year'] = date("Y");
    $this->load->model("user");
    $users = $this->user->fetchAll();
    $this->load->model("dpt");
    $dpts = $this->dpt->fetchAll();
    if($mydpt == 0) {
      $data['users'] = $users;
      $data['dpts'] =$dpts;
    } else {
      foreach ($users as $key => $user) {
        if($user['dptid'] != $mydpt) {
          unset($users[$key]);
        }
      }
      foreach ($dpts as $key => $dpt) {
        if($dpt['dptid'] != $mydpt) {
          unset($dpts[$key]);
        }
      }
      $data['users'] = $users;
      $data['dpts'] = $dpts;

    }
    $this->load->view('usermanager', $data);
    
  }

  /**
   * 系统配置
   *
   * @access public
   */
  public function sysconfig()
  {
    //预检查
    $this->_pre_control('sysconfig');
    //头部数据
    $data = array();
    $data['year'] = date("Y");
    $data['sysconfig'] = $this->_cfg_obj;
    $this->load->model("bgp_link_info");
    $data['bgp_links'] = $this->bgp_link_info->get_bgp_links();

    $this->load->view('sysconfig', $data);
  }
}
