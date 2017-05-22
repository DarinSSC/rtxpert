<?php
// ------------------------------------------------------------------------

/**
 * RTXpert OSPF Local traffic_topo Class
 *
 * OSPF Local traffic_topo model, 本地OSPF流量拓扑的model
 * 
 *
 * @package		RTXpert
 * @subpackage	ospf_local
 * @category	application/models/ospf_local/topo.php
 * @author		DingZixuan
 * @link		http://wp4dzx.sinaapp.com
 */
class Traffic_topo extends CI_Model {
		
	public function __construct()
	{
		$this->load->database();
		$userid = $this->session->userdata('userid');
	    if (!empty($userid)) {
	      $this->_userdata = array('userid' => $userid, 'username' => $this->session->userdata('username'), 'usertype' => $this->session->userdata('usertype'), 'dpt' => $this->session->userdata('dpt'));
	    } else {
	      $this->_userdata = array('userid' => 0, 'username' => "", 'usertype' => 1, 'dpt' => 0);
	    }
	}

	// --------------------------------------------------------------------

	/**
	 * 获取表中最大的周期id 
	 *
	 * @access	public
	 * @param	时间戳
	 * @return	bigint （周期id）
	 */    
	public function get_max_pid()
	{
		$this->db->select_max('pid');
		$query = $this->db->get('ospf_traffic_topo');
		return $query->row()->pid;
	}


	// --------------------------------------------------------------------

	/**
	 * 根据pid获取周期间隔 
	 *
	 * @access	public
	 * @param	bigint （周期id）
	 * @return	int （interval）
	 */   
	public function get_interval($pid)
	{
		$querystr = "SELECT period_interval FROM ospf_traffic_topo WHERE pid = {$pid} LIMIT 1";
		$query = $this->db->query($querystr);
		if ($query->num_rows() > 0) {
			return $query->row()->period_interval;
		} else {
			return 0;
		}	
	}

  	// --------------------------------------------------------------------

	/**
	 * 根据上个周期id，查询是否有流量拓扑更新 
	 * 有更新则返回最新周期id,否则返回0
	 *
	 * @access	public
	 * @param	int pid
	 * @return	bigint 周期id | 0
	 */ 
	public function has_traffic_update($pid)
	{
		$cur_max_pid = $this->get_max_pid();
		return ($cur_max_pid > $pid )?$cur_max_pid:0;
	}


	// --------------------------------------------------------------------

	/**
	 * 获取某时间点对应的周期id 
	 * 若该时间点大于表中所有记录的时间戳，则返回最大周期id
	 *
	 * @access	public
	 * @param	时间戳
	 * @return	bigint （周期id）
	 */    
	public function get_pid_by_timestamp($timestamp)
	{
		/*$sqlstr = "SELECT `pid` FROM `isis_traffic_topo` WHERE `topo_time` = (SELECT min(`topo_time`) FROM `isis_traffic_topo` WHERE `topo_time` - {$timestamp} >= 0) AND batch is not null;";
		$query = $this->db->query($sqlstr);*/
		$formattime = date('YmdHi', $timestamp);
		$sqlstr = "SELECT min(`pid`) AS pid FROM `ospf_traffic_topo` WHERE `pid`  >= {$formattime};";
		$query = $this->db->query($sqlstr);
		if ($query->num_rows() > 0) {
			return $query->row()->pid ? $query->row()->pid : $this->get_max_pid();
		} else {
			return $this->get_max_pid();
		}
	}


	// --------------------------------------------------------------------

	/**
	 * 根据周期id查询流量拓扑 
	 * 
	 *
	 * @access	public
	 * @param	时间戳，返回格式
	 * @return	
	 *          查询失败  空数组
	 *			{"routers":[],"links":[]}
	 *			routers = array(id, attr_id, as_num, router_id, router_type, dptid, alias, description, x, y, image)
	 *			links = array(id, attr_id, as_num, router_id, interface_ip, mask, n_as_num, n_router_id, area_id, link_type, metric, bandwidth, description, bytes, traffic_id)
	 */    
	public function get_traffic_by_pid($pid)
	{
		$timestamp = strtotime($pid);
		//step1: 查询所有有效router信息：ospf_router_info左外连接ospf_router_attr表
		//[id, attr_id, as_num, router_id, router_type, dptid, alias, description, x, y, image]
		$routers = array();
		$this->db->select('ospf_router_info.id, ospf_router_attr.id AS attr_id, ospf_router_info.as_num, ospf_router_info.router_id, ospf_router_info.router_type, ospf_router_attr.dptid, ospf_router_attr.alias, ospf_router_attr.description, ospf_router_attr.x, ospf_router_attr.y, ospf_router_attr.image');
		$this->db->from('ospf_router_info');
		$this->db->where('ospf_router_info.create_time <=', $timestamp);
		$this->db->where('ospf_router_info.end_time >', $timestamp);
		//$this->db->group_by('router_id');	//13-05-08 删掉多余routers
		$this->db->join('ospf_router_attr', 'ospf_router_info.as_num = ospf_router_attr.as_num AND ospf_router_info.router_id = ospf_router_attr.router_id', 'left outer');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				//----OSPF：根据单位对节点进行过滤----
				//不是系统管理员且进行了单位设置的用户
				if ($this->_userdata['dpt'] != 0) {
					if (intval($row['dptid']) != intval($this->_userdata['dpt']))
						continue;
				}
				//------------------------------------
				if(is_null($row['attr_id'])) {
					$row['attr_id'] = '0';
				}
				if(is_null($row['x'])) {
					$row['x'] = '0';
				} 
				if(is_null($row['y'])) {
					$row['y'] = '0';
				}
				if(is_null($row['alias'])) {
					$row['alias'] = '';
				}
				if(is_null($row['description'])) {
					$row['description'] = '';
				}
				if(is_null($row['dptid'])) {
					$row['dptid'] = '0';
				}
				if(is_null($row['image'])) {
					$row['image'] = '0';
				}
			    $routers[] = array_values($row);
			}
		}
		//step2: 查询所有有效link信息：ospf_link_info左外连接ospf_link_attr表
		//[id, attr_id, as_num, router_id, interface_ip, mask, n_as_num, n_router_id, area_id, link_type, metric, bandwidth, description, bytes, traffic_id]
		$links = array();
		$this->db->select('ospf_link_info.id, ospf_link_attr.id AS attr_id, ospf_link_info.as_num, ospf_link_info.router_id, ospf_link_info.interface_ip, ospf_link_info.mask, ospf_link_info.n_as_num, ospf_link_info.n_router_id, ospf_link_info.area_id, ospf_link_info.link_type, ospf_link_info.metric, ospf_link_attr.bandwidth, ospf_link_attr.description, ospf_traffic_topo.bytes, ospf_traffic_topo.id AS traffic_id');
		$this->db->from('ospf_traffic_topo');
		$this->db->where('ospf_traffic_topo.pid', $pid);	//找到该周期对应的时间戳
		$this->db->join('ospf_link_info', 'ospf_traffic_topo.link_id = ospf_link_info.id');
		$this->db->join('ospf_link_attr', 'ospf_link_info.as_num = ospf_link_attr.as_num AND ospf_link_info.router_id = ospf_link_attr.router_id AND ospf_link_info.n_as_num = ospf_link_attr.n_as_num AND ospf_link_info.n_router_id = ospf_link_attr.n_router_id', 'left outer');
		$query = $this->db->get();	
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				if(is_null($row['attr_id'])) {
					$row['attr_id'] = '0';
				} 
				if(is_null($row['bandwidth'])) {
					$row['bandwidth'] = '0';
				}
				if(is_null($row['description'])) {
					$row['description'] = '';
				}
			   $links[] = array_values($row);
			}
		}
		//双向检测
		/*$links2 = $links;
		foreach ($links as $key1 => $link1) {
			$flag = true;
			foreach ($links2 as $key2 => $link2) {
				if ($link1[2] == $link2[6] && $link1[3] == $link2[7] && $link1[6] == $link2[2] && $link1[7] == $link2[3]){	//找到互为邻居
					$flag = false;
					unset($links2[$key2]);
					break;
				}
			}
			if ($flag) {
				unset($links[$key1]);
			}
		}*/
		//删除掉links中没有的router
		foreach ($routers as $key1 => $router) {
			$flag = true;
			foreach ($links as $key2 => $link) {
				if ($router[2] == $link[2] && $router[3] == $link[3]) {
					$flag = false;
					break;
				}	
			}
			if ($flag) {
				unset($routers[$key1]);
			}
		}
		$return_array = array('routers' => array_values($routers), 'links' => array_values($links));
		return $return_array;
	}


	// --------------------------------------------------------------------

	/**
	 * 获取在某时间段之间的所有流量拓扑 
	 * 
	 *
	 * @access	public
	 * @param	时间戳
	 * @return	[{"period_id":111111111111,"interval":2, "period_traffic":{"routers":[],"links":[]}}]
	 */ 
	public function get_history_traffic($stpid, $limit)
	{
		$return_array = array();
		$sqlstr = "SELECT `pid`, `period_interval` FROM `ospf_traffic_topo` WHERE `pid` >= $stpid group by pid limit {$limit};";
		$query = $this->db->query($sqlstr);
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row)
			{
				$return_row =array();
				$return_row['period_id'] = $row['pid'];
				$return_row['interval'] = $row['period_interval'];
				$return_row['period_traffic'] = $this->get_traffic_by_pid($row['pid']);
				$return_array[] = $return_row;
			}
		} 
		return $return_array;
	}
	
}
