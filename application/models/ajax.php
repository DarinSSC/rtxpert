<?php
// ------------------------------------------------------------------------

/**
 * RTXpert Ajax Class
 *
 * Ajax model, 根据不同的协议，是否总部，处理用户的ajax请求
 * 
 *
 * @package		RTXpert
 * @subpackage	isis_local
 * @category	application/models/isis_local/topo.php
 * @author		DingZixuan
 * @link		http://wp4dzx.sinaapp.com
 */
class Ajax extends CI_Model {
	
	/**
	 * 数据表前缀
	 *
	 * @var bool
	 * @access public
	 */
	var $prefix = '';
	var $ishq;

	/**
	 * 数据表中间
	 *
	 * @var string
	 * @access public
	 */
	var $protocal = '';
	

	public function __construct()
	{
		$this->load->database();				
	}
	

	// --------------------------------------------------------------------

	/**
	 * 自定义构造函数
	 *
	 * 由于CI的model不允许向构造函数传参数，这里自定义一个需要显示
	 * 调用的构造函数，用于指定查询的时间点或时间段
	 *
	 * @access	public
	 * @param	array
	 * @return	无
	 */	
	public function construct($protocal, $ishq)
	{
		$this->protocal = $protocal;
		$this->ishq = $ishq;
		if ($ishq == 1 ){
			$this->prefix = 'hz_';
		}
	}
	

	// --------------------------------------------------------------------

	/**
	 * 修改布局信息
	 *
	 *
	 * @access	public
	 * @param	
	 * @return	json
	 */  
	public function updateTopoPos($jsonStr)
	{
		$return  = "failed";
		$jsonArrs = json_decode($jsonStr, false);
		

		//isis目前只做单域的检测
		//[1] sys_id [5] x [6] y
		if ($this->protocal == "isis") {
			foreach ($jsonArrs as $jsonArray) {
				//var_dump($jsonArray);exit;
				$this->db->select('*');
				$this->db->from($this->prefix.$this->protocal.'_router_layout');
				$this->db->where('sys_id', $jsonArray[1]);
				$query = $this->db->get();

				if($query->num_rows() > 0) {	//有记录则update
					$this->db->where('sys_id', $jsonArray[1]);
					$this->db->update($this->prefix.$this->protocal.'_router_layout', array('x' => $jsonArray[5], 'y' => $jsonArray[6]));
				} else {	//无记录则insert
					$this->db->insert($this->prefix.$this->protocal.'_router_layout', array('area_id' => '49.0001', 'sys_id' => $jsonArray[1], 'x' => $jsonArray[5], 'y' => $jsonArray[6]));
				}
			}
			$return  = "ok";
		}
		//ospf要根据as_num和router_id一起确定唯一 
		elseif ($this->protocal == "ospf") {
			foreach ($jsonArrs as $jsonArray) {
				$this->db->select('*');
				$this->db->from($this->prefix.$this->protocal.'_router_layout');
				$this->db->where('as_num', $jsonArray[0]);
				$this->db->where('router_id', $jsonArray[1]);
				$query = $this->db->get();

				if($query->num_rows() > 0) {	//有记录则update
					$this->db->where('as_num', $jsonArray[0]);
					$this->db->where('router_id', $jsonArray[1]);
					$this->db->update($this->prefix.$this->protocal.'_router_layout', array('x' => $jsonArray[5], 'y' => $jsonArray[6]));
				} else {	//无记录则insert
					$this->db->insert($this->prefix.$this->protocal.'_router_layout', array('as_num' => $jsonArray[0], 'router_id' => $jsonArray[1], 'x' => $jsonArray[5], 'y' => $jsonArray[6]));
				}
			}
			$return = "ok";
		}
		return $return;
	}

  
    // --------------------------------------------------------------------

	/**
	 * 修改路由器信息
	 *
	 *
	 * @access	public
	 * @param	
	 * @return	json
	 */  
	public function updateRouter($jsonStr)
	{
		$jsonObj = json_decode($jsonStr);
		$rid = $jsonObj->routerid;
		$alias = $jsonObj->alias;
		$description = $jsonObj->description;
		$dpt = $jsonObj->dpt;
		$data = array('alias' => $alias, 'description' => $description, 'dpt' => $dpt);
		if ($this->protocal == 'ospf') {
			$this->db->where('router_id', $rid);
		} else {
			$this->db->where('sys_id', $rid);
		}
		$this->db->update($this->prefix.$this->protocal.'_router_info', $data);
		return 'ok';
	}
	

	// --------------------------------------------------------------------

	/**
	 * 修改带宽信息
	 *
	 *
	 * @access	public
	 * @param	
	 * @return	json
	 */  
	public function updateBandwidth($jsonStr)
	{
		$return  = "failed";
		$jsonObj = json_decode($jsonStr);
		
		if ($jsonObj->RsAother > 0) {	//除此之外的所有链路都设为某一带宽
			$default = $jsonObj->RsAother;
			//先批量更新
			$this->db->update($this->prefix.$this->protocal.'_link_info', array('bandwidth' => $default));
			//再有选择更新
			$links = $jsonObj->links;
			foreach ($links as $link) {
				if ($this->protocal == 'isis') {
					//$this->db->where('area_id', $link[0]);
					$this->db->where('sys_id', $link[1]);
					//$this->db->where('n_area_id', $link[2]);
					$this->db->where('n_sys_id', $link[3]);
				}
				elseif ($this->protocal == 'ospf') {
					$this->db->where('as_num', $link[0]);
					$this->db->where('router_id', $link[1]);
					$this->db->where('n_as_num', $link[2]);
					$this->db->where('n_router_id', $link[3]);
				}
				$this->db->update($this->prefix.$this->protocal.'_link_info', array('bandwidth' => $link[4]));
			}
			$return = 'ok';
		} 

		else {
			$links = $jsonObj->links;
			foreach ($links as $link) {
				if ($this->protocal == 'isis') {
					//$this->db->where('area_id', $link[0]);
					$this->db->where('sys_id', $link[1]);
					//$this->db->where('n_area_id', $link[2]);
					$this->db->where('n_sys_id', $link[3]);
				}
				elseif ($this->protocal == 'ospf') {
					$this->db->where('as_num', $link[0]);
					$this->db->where('router_id', $link[1]);
					$this->db->where('n_as_num', $link[2]);
					$this->db->where('n_router_id', $link[3]);
				}
				$this->db->update($this->prefix.$this->protocal.'_link_info', array('bandwidth' => $link[4]));
			}
			$return = 'ok';
		}
		return $return;
	}


	// --------------------------------------------------------------------

	/**
	 * 修改告警处理
	 *
	 *
	 * @access	public
	 * @param	
	 * @return	json
	 */  
	public function setWarningStatus($jsonStr)
	{
		$return  = 0;
		$jsonObj = json_decode($jsonStr);
		$alarmId = $jsonObj->AlarmId;
		$solved = $jsonObj->Solved;
		$this->load->model('warning');
		$this->warning->setIsHq($this->ishq);
		$return = $this->warning->setWarningStatus($alarmId, $solved);
		return $return;
	}


	// --------------------------------------------------------------------

	/**
	 * 获取路由器接口信息
	 *
	 *
	 * @access	public
	 * @param	
	 * @return	json
	 */  
	public function getRouterInterfaces($jsonStr)
	{
		$return  = array();
		$jsonObj = json_decode($jsonStr);
		$as_num = $jsonObj->asnum;
		$router_id = $jsonObj->routerid;
		$return['as_num'] = $as_num;
		$return['router_id'] = $router_id;
		if ($this->protocal == 'ospf' && $this->ishq == 0) {
			$this->load->model('ospf_local/link_info');
			$return['interfaces'] = $this->link_info->getRouterInterfaces($as_num, $router_id);
		} elseif ($this->protocal == 'ospf' && $this->ishq == 1) {
			$this->load->model('ospf_whole/link_info');
			$return['interfaces'] = $this->link_info->getRouterInterfaces($as_num, $router_id);
		}
		return $return;
	}


	// --------------------------------------------------------------------

	/**
	 * 获取最近周期id
	 *
	 *
	 * @access	public
	 * @param	
	 * @return	json
	 */  
	public function getLatesePeriodId($limit)
	{
		$modelPrefix = $this->ishq?'_whole':'_local';
		$this->load->model($this->protocal.$modelPrefix.'/traffic_topo');
		return $this->traffic_topo->getLatesePeriodId($limit);
	}


	// --------------------------------------------------------------------

	/**
	 * 获取最近周期id
	 *
	 *
	 * @access	public
	 * @param	
	 * @return	json
	 */  
	public function getTrafficTopoByPid($jsonStr)
	{
		$jsonObj = json_decode($jsonStr);
		$pid = $jsonObj->pid;
		$modelPrefix = $this->ishq?'_whole':'_local';
		$this->load->model($this->protocal.$modelPrefix.'/traffic_topo');
		return $this->traffic_topo->getTrafficTopoByPid($pid);
	}


	// --------------------------------------------------------------------

	/**
	 * 获取链路上业务类型流量
	 *
	 *
	 * @access	public
	 * @param	
	 * @return	json
	 */  
	public function getProtoTraffic($jsonStr)
	{
		$jsonObj = json_decode($jsonStr);
		$pid = $jsonObj->pid;
		$link1 = $jsonObj->link1;
		$link2 = $jsonObj->link2;

		$retArr = array('routerA' => '', 'routerB' => '', 'A2B' => array(), 'B2A' => array());

		//根据link_id查routerA和routerB
		if ($this->protocal == "ospf") {
			$this->db->select('router_id as routerA, n_router_id as routerB');
		} elseif ($this->protocal == "isis") {
			$this->db->select('sys_id as routerA, n_sys_id as routerB');
		}
		$this->db->from($this->prefix.$this->protocal.'_link_info');
		$this->db->where('id', $link1);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			 $row = $query->row();
			 $routerA = $row->routerA;
			 $routerB = $row->routerB;
		}
		$retArr['routerA'] = $routerA;
		$retArr['routerB'] = $routerB;

		//根据link1查业务流量
		//$A2B = array();
		$this->db->select('ftp, telnet, http, other, batch as total');
		$this->db->from($this->prefix.$this->protocal.'_traffic_topo');
		$this->db->where('pid', $pid);
		$this->db->where('link_id', $link1);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$A2B = $query->row();
		}

		//根据link2查业务流量
		//$B2A = array();
		$this->db->select('ftp, telnet, http, other, batch as total');
		$this->db->from($this->prefix.$this->protocal.'_traffic_topo');
		$this->db->where('pid', $pid);
		$this->db->where('link_id', $link2);
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			$B2A = $query->row();
		}
		$retArr['A2B'] = $A2B;
		$retArr['B2A'] = $B2A;
		return $retArr;
	}
}