<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Interface_ospf_local extends CI_Controller {

	/**
	* 登陆用户信息
	*
	* @array(userid, username, usertype, dpt)
	* @access public
	*/
	public $_userdata;

	public $_cfg_obj;

	public function __construct()
	{
		parent::__construct(); 
		$this->_cfg_obj = $this->sys_config->get_cfg_obj();
	}

	public function get_full_topo()
	{
		$return_array = array();
		$return_array['timestamp'] = time();	//当前查询时间
		$this->load->model('ospf_local/topo');
		$this->topo->construct(array($return_array['timestamp']));
		$return_array['topo'] = $this->topo->get_full_topo();
		$this->load->model('warning');
		$this->warning->construct($this->_cfg_obj->localSet->protocol, $this->_cfg_obj->localSet->isHQ);
		$return_array['warning'] = $this->warning->get_unsolved_warnings();
		echo json_encode($return_array);
		exit;
	}

	public function get_inc_topo()
	{
		$timestamp = $this->input->get('timestamp', TRUE);	//上次查询时间
		$return_array = array();
		$update_time = intval($timestamp);											//此次更新时间
		$this->load->model('ospf_local/topo');
		$this->topo->construct(array($timestamp));
		$return_array['inc_topo'] = $this->topo->get_inc_topo(0, $update_time);
		$return_array['timestamp'] = $update_time;			//warning表的inc_topo最新更新时间
		$this->load->model('warning');
		$this->warning->construct($this->_cfg_obj->localSet->protocol, $this->_cfg_obj->localSet->isHQ);
		$return_array['warning'] = $this->warning->get_unsolved_warnings();
		echo json_encode($return_array);
		exit;
	}

	public function get_traffic_topo()
	{
		$pid = $this->input->get('pid', TRUE);
		$return_array = array();
		$this->load->model('ospf_local/traffic_topo');
		if ($pid == 0) {	//第一次请求
			$pid = $this->traffic_topo->get_max_pid();
		} else {	//非第一次请求
			$pid = $this->traffic_topo->has_traffic_update($pid);
		}
		if ($pid) {
			$return_array['period_id'] = $pid;
			$return_array['period_interval'] = $this->traffic_topo->get_interval($pid);
			$return_array['period_traffic'] = $this->traffic_topo->get_traffic_by_pid($pid);
		} else {
			$return_array[] = json_decode("{}");
		}
		echo json_encode($return_array);
		exit;
	}

	//查询历史拓扑
	public function get_history_topo()
	{
		$sttime = $this->input->get('sttime', TRUE);
		$limit = $this->input->get('limit', TRUE);
		//2014-07-28:增加period参数，单位分钟，接口改为查询从sttime开始，Period分钟以内的所有变化事件
		$period = $this->input->get('period', TRUE);
		$return = array();
		$this->load->model('ospf_local/topo');
		if ($limit > 0) {
			$this->topo->construct(array($sttime));
			$return['base_topo'] = $this->topo->get_full_topo();
			$return['base_topo']['timestamp'] = $sttime;
			$return['inc_topo'] = $this->topo->get_inc_topo($limit, $sttime, $period);
			echo json_encode($return);
			exit;
		} else {
			$this->topo->construct(array($sttime));
			$return['base_topo'] = $this->topo->get_full_topo();
			$return['base_topo']['timestamp'] = $sttime;
			echo json_encode($return);
			exit;
		}
	}

	//查询历史流量
	public function get_history_traffic()
	{
		$sttime = $this->input->get('sttime', TRUE);
		$limit = $this->input->get('limit', TRUE);
		$limit = ($limit > 0) ? $limit : 1;
		$this->load->model('ospf_local/traffic_topo');
		$stpid = $this->traffic_topo->get_pid_by_timestamp($sttime);
		echo json_encode($this->traffic_topo->get_history_traffic($stpid, $limit));
		exit;
	}

	//获取路由器接口信息
	public function get_router_interfaces()
	{
		$as_num = $this->input->get('asnum', TRUE);
		$router_id = $this->input->get('routerid', TRUE);
		$timestamp = $this->input->get('timestamp', TRUE);
		$this->load->database();
		$this->db->select('*');
		$this->db->from('ospf_link_info');
		$this->db->where('as_num', $as_num);
		$this->db->where('router_id', $router_id);
		$this->db->where('create_time <=', $timestamp);
		$this->db->where('end_time >', $timestamp);
		$query = $this->db->get();
		$return_array = array();
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$interface = array();
				$interface['interface_ip'] = $row->interface_ip;
				//获取n_interface_ip
				$this->db->select('interface_ip');
				$this->db->from('ospf_link_info');	
				$this->db->where('router_id', $row->n_router_id);
				$this->db->where('n_router_id', $row->router_id);
				$this->db->where('create_time <=', $timestamp);
				$this->db->where('end_time >', $timestamp);
				$query2 = $this->db->get();
				if ($query2->num_rows() > 0) {
					$interface['n_interface_ip'] = $query2->row()->interface_ip;
				} else {
					$interface['n_interface_ip'] = '';
				}	
				$interface['prefix'] = $row->interface_ip;	//改
				if (is_null($row->mask)) {
					$interface['mask'] = "255.255.255.0";	//TODO:写死！！
				} else {
					$interface['mask'] = $row->mask;
				}
				$interface['area_id'] = $row->area_id;
				$interface['link_type'] = $row->link_type;
				switch ($row->link_type) {
					case 1:
						$interface['link_type'] = "Point-to-point";
						break;
					case 2:
						$interface['link_type'] = "Transit";
						break;
					case 3:
						$interface['link_type'] = "Stub";
						break;
					case 4:
						$interface['link_type'] = "Virtual link";
						break;
					default:
						$interface['link_type'] = "Point-to-point";
						break;
				}
				$interface['metric'] = $row->metric;
				$return_array[] = $interface;
			}
		}
		echo json_encode($return_array);
		exit;
	}	

	//修改路由器属性
	public function update_router_attr()
	{
		$json_str = $this->input->get('jsonStr', TRUE);
		$json_arr = json_decode($json_str);
		$this->load->model('ospf_local/router_attr');
		$return = $this->router_attr->update_router_attr($json_arr[0], $json_arr[1], $json_arr[2], $json_arr[3], $json_arr[4], $json_arr[5], $json_arr[6]);
		if ($return) {
			echo "ok";
			exit;
		} else {
			echo "failed";
			exit;
		}
	}

	//修改布局
	public function update_topo_pos()
	{
		$json_str = $this->input->post('jsonStr', TRUE);
		$json_arrs = json_decode($json_str, TRUE);
		$affec_rows = 0;
		$this->load->model('ospf_local/router_attr');
		foreach ($json_arrs as $router) {		
			$affec_rows += $this->router_attr->update_router_pos($router[0],$router[1],$router[2],$router[3],$router[4]);
		}
		if ($affec_rows) {
			echo "ok";
			exit;
		} else {
			echo "failed";
			exit;
		}	
	}

	//修改链路属性
	public function update_link_attr()
	{		
		$json_str = $this->input->post('jsonStr', TRUE);
		$json_arrs = json_decode($json_str, TRUE);
		$affec_rows = 0;
		$this->load->model('ospf_local/link_attr');
		foreach ($json_arrs as $link) {		
			$affec_rows += $this->link_attr->update_link_bw(intval($link[0]),intval($link[1]),$link[2],intval($link[3]),$link[4],intval($link[5]));
		}
		if ($affec_rows) {
			echo "ok";
			exit;
		} else {
			echo "failed";
			exit;
		}
	}

	//判断链路是否存在
	public function get_link_exist()
	{
		$time = $this->input->get('time', TRUE);
		$domainA = $this->input->get('domainA', TRUE);
		$routerA = $this->input->get('routerA', TRUE);
		$domainB = $this->input->get('domainB', TRUE);
		$routerB = $this->input->get('routerB', TRUE);
		
		$this->load->model('ospf_local/topo');
		echo $this->topo->get_link_exist($time, $domainA, $routerA, $domainB, $routerB);
		exit;
	}
}
