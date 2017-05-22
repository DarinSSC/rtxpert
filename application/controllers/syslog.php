<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Syslog extends CI_Controller {
    
    /**
     * 与数据库相关的
     * 类型映射关系配置信息
     *
     * @array(usertype, routertype, linktype, inctopotype)
     * @access protected
     */
    protected $_runtimeconf;
    
    /**
     * 用户可修改的
     * 系统基本配置信息
     *
     * @object("isHQ":1, "asIpList":[], "asNum":1, "flowDeviceIpList":[], "configPort":47000, "topoPort":47001, "globalAnalysisIp":"127.0.0.1", "globalAnalysisPort":57000, "interval":3, "topN":9, "isReadLocal":"false", "protocal":"ospf")
     * @access protected
     */
    protected $_sysconfig;
    
    /**
     * 登陆用户信息
     *
     * @array(userid, username, usertype, dpt)
     * @access public
     */
    public $_userdata;
    
    public function __construct()
    {
        parent::__construct();  

        $this->_runtimeconf = $this->config->item('runtimeconf');
        $this->_sysconfig = $this->sysconfig->getConfigObj();
    }

    // --------------------------------------------------------------------

    /**
     * 登陆检测
     * 1.页面是否存在
     * 2.是否已登录
     * 3.是否是管理员
     * @access  public
     * @param   
     * @return  
     */
    protected function _pre_control($pagename)
    {
        //1.检查view页面存在情况
        if(isset($pagename)) {
            if ( ! file_exists('application/views/'.$pagename.'.php')) {
                show_404();exit;
            }
        }
        //2.判断登陆情况,若已登录，将sesion中用户信息放入数组    
        //  若未登录，跳转回登陆页面
        $userid = $this->session->userdata('userid');
        if (!empty($userid)) {
            $this->_userdata = array('userid' => $userid, 'username' => $this->session->userdata('username'), 'usertype' => $this->session->userdata('usertype'), 'usergender' => $this->session->userdata('usergender'), 'dpt' => $this->session->userdata('dpt'));
        } else {
            redirect(base_url().'index.php/index/login?ref='.urlencode($this->uri->uri_string()));
        }   
        //3.判断用户是否是系统管理员    
        //  若不是系统管理员，跳转
        $usertype = $this->session->userdata('usertype');
        if ($usertype != 1) {
            redirect(base_url().'index.php/index/index');    //TODO:打印提示信息？
        }
    }
    
    public function index()
    {      
        redirect(base_url().'index.php/syslog/syslogList');
    }
    

    // --------------------------------------------------------------------

    /**
     * 系统日志列表
     *
     * @access  public
     * @param   
     * @return  
     */
    public function syslogList()
    {
        //预检查
        $this->_pre_control('syslog');
        //头部数据
        $data = array();
        $data['user'] = $this->_userdata;
        $data['url'] = base_url();
        $data['title'] = '系统日志';
        $data['year'] = date("Y");
        $data['location'] = 'syslog';

        //接收参数
        $page = $this->input->get('page', TRUE);
        if(is_null($page) || empty($page)) {
            $page = 1;
        }
        $limit = 15; //每页多少条
        $offset = ($page-1)*$limit;
        $total = 0;
        $sttime = $this->input->get('sttime', TRUE);
        $edtime = $this->input->get('edtime', TRUE);

        $cfg['base_url'] = base_url().'index.php?c=syslog&m=syslogList';
        //处理查询参数
        $queryArgs = array();
        $queryArgs['sortBy'] = 'id';
        if (!is_null($edtime) && !empty($edtime)) {
            $queryArgs['edtime'] = strtotime($edtime);
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
        $this->load->model('log');
        $result = $this->log->getSyslogs($queryArgs);
        if ($result) {
            $data['syslogs'] = $result['data'];
            $total = $result['total'];
        } else {
            $data['syslogs'] = null;
        }
        //分页
        $this->load->library('pagination');
        //$cfg['base_url'] = base_url().'index.php?c=syslog&m=syslogList';
        $cfg['total_rows'] = $total;
        $cfg['per_page'] = $limit; 
        $cfg['use_page_numbers'] = TRUE;
        $cfg['page_query_string'] = TRUE;
        $cfg['query_string_segment'] = 'page';
        $cfg['first_link'] = '首页';
        $cfg['last_link'] = '末页';
        $cfg['num_links'] = 2;
        $pagination = $this->pagination->initialize($cfg); 
        //$data['pagination'] = $this->pagination->create_links();

        /*$page_config['perpage']=$limit;   //每页条数
        $page_config['part']=2;//当前页前后链接数量
        $page_config['url']='syslog/syslogList';//url
        $page_config['seg']=3;//参数取 index.php之后的段数，默认为3，即index.php/control/function/18 这种形式
        $page_config['nowindex']=$this->uri->segment($page_config['seg']) ? $this->uri->segment($page_config['seg']):1;//当前页
        $this->load->library('mypage');
        $page_config['total']=$total;
        $this->mypage->initialize($page_config);*/
          

        $this->load->view('header', $data);
        $this->load->view('syslog', $data);
        $this->load->view('footer', $data);
    }

}