<?php
// ------------------------------------------------------------------------

/**
 * RTXpert ISIS Topo Class
 *
 * ISIS topo model, ISIS路由拓扑的model
 * 
 *
 * @package		RTXpert
 * @subpackage	isis
 * @category	application/models/isis/topo.php
 * @author		DingZixuan
 * @link		http://wp4dzx.sinaapp.com
 */
class Topo extends CI_Model {

	/**
	* 登陆用户信息
	*
	* @array(userid, username, usertype, dpt)
	* @access public
	*/
	public $_userdata;
	
	/**
	 * 查询时间点或查询时间段的起时间
	 *
	 * @var decimal
	 * @access public
	 */
	var $sttime;

	/**
	 * 查询时间段的止时间
	 *
	 * @var decimal
	 * @access public
	 */
	var $edtime;

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
	 * 自定义构造函数
	 *
	 * 由于CI的model不允许向构造函数传参数，这里自定义一个需要显示
	 * 调用的构造函数，用于指定查询的时间点或时间段
	 *
	 * @access	public
	 * @param	array
	 * @return	无
	 */	
	public function construct($params = array())
	{
		if (isset($params[0])) {			
			if (isset($params[1])) {
				$this->sttime = ($params[0] <= $params[1])?$params[0]:$params[1];
				$this->edtime = ($params[0] >= $params[1])?$params[0]:$params[1];
			} else {
				$this->sttime = $params[0];
				$this->edtime = 9999999999;
			}
		} else {
			$this->sttime = time();
			$this->edtime = 9999999999;
		}	
	}
	
	
  
    // --------------------------------------------------------------------

	/**
	 * 获取某时刻的完整拓扑
	 *
	 *
	 * @access	public
	 * @param	
	 * @return	{"routers":[],"links":[]}
	 */  
	public function get_full_topo()
	{
		//step0：查询所有L1/L2路由器
		// $l12_routers  = array();
		// $this->db->select('isis_router_info.*');
		// $this->db->from('isis_router_info');
		// $this->db->where('isis_router_info.router_type', 2);
		// $query = $this->db->get();
		// if($query->num_rows > 0) {
		// 	foreach ($query->result_array() as $row) {
		// 		$l12_routers[$row['sys_id']] = $row['area_id'];
		// 	}
		// 

		//step1: 查询所有有效router信息：isis_router_info左外连接isis_router_attr表
		//[id, attr_id, area_id, sys_id, router_type, dptid, alias, description, x, y, hostname, image]
		$routers = array();
		$this->db->select('isis_router_info.id, isis_router_attr.id AS attr_id, isis_router_info.area_id, isis_router_info.sys_id, isis_router_info.router_type, isis_router_attr.dptid, isis_router_attr.alias, isis_router_attr.description, isis_router_attr.x, isis_router_attr.y, isis_router_info.hostname, isis_router_attr.image');
		$this->db->from('isis_router_info');
		$this->db->where('isis_router_info.create_time <=', $this->sttime);
		$this->db->where('isis_router_info.end_time >', $this->sttime);
		$this->db->join('isis_router_attr', 'isis_router_info.area_id = isis_router_attr.area_id AND isis_router_info.sys_id = isis_router_attr.sys_id', 'left outer');
		$query = $this->db->get();

		$count = 0;

		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				//----ISIS：根据单位对节点进行过滤----
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
		//step2: 查询所有有效link信息：isis_link_info左外连接isis_link_attr表
		//[id, attr_id, area_id, sys_id, interface_ip, mask, n_area_id, n_sys_id, link_type, metric, bandwidth, description]
		$links = array();
		$this->db->select('isis_link_info.id, isis_link_attr.id AS attr_id, isis_link_info.area_id, isis_link_info.sys_id, isis_link_info.interface_ip, isis_link_info.mask, isis_link_info.n_area_id, isis_link_info.n_sys_id, isis_link_info.link_type, isis_link_info.metric, isis_link_attr.bandwidth, isis_link_attr.description');
		$this->db->from('isis_link_info');
		$this->db->where('isis_link_info.create_time <=', $this->sttime);
		$this->db->where('isis_link_info.end_time >', $this->sttime);
		//$this->db->where('link_type', 2);
		$this->db->join('isis_link_attr', 'isis_link_info.area_id = isis_link_attr.area_id AND isis_link_info.sys_id = isis_link_attr.sys_id AND isis_link_info.n_area_id = isis_link_attr.n_area_id AND isis_link_info.n_sys_id = isis_link_attr.n_sys_id', 'left outer');
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
		$links2 = $links;
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
		}
		
		//删除links中只有一边节点的部分
		foreach ($links as $key1 => $link) {
			$flagl = false;
			$flagr = false;
			foreach ($routers as $key2 => $router) {
				if ($router[2] == $link[2] && $router[3] == $link[3]) {
					$flagl = true;
				}
				if ($router[2] == $link[6] && $router[3] == $link[7]) {
					$flagr = true;
				}
			}
			if (!($flagl && $flagr)) {
				unset($links[$key1]);
			}
		}

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
	 * 获取自某个时间点的增量拓扑变化事件
	 *
	 * 
	 *
	 * @access	public
	 * @param	
	 * @return	[{"id", "code", "relate_id", "params", "timestamp"}]
	 */  
	public function get_inc_topo($limit = 0, &$time, $period = 0)
	{
		$return_array = array();
		$this->db->select('id, code, relate_id, text_params, timestamp');
		$this->db->from('isis_warning');
		$this->db->where_in('code', array(2101,2102,2103,2104));	//只检索isis的路由器增删和链路增删告警
		$this->db->where('timestamp >', $time);	//检索起点时间
		//2014-07-28:增加period参数，单位分钟，如果此参数不为0，则忽略limit参数，查询sttime开始period分钟以内的所有事件
		//如果此参数为0，则使用limit参数，忽略此参数
		if ($period != 0) {
			$this->db->where('timestamp <=', $time + $period*60);
		} else {
			if ($limit != 0) {	//检索个数限制
				$this->db->limit($limit);
			}
		}
		$this->db->order_by("id");	//按时间正序排列

		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				//----ISIS：根据单位对节点进行过滤----
				//不是系统管理员且进行了单位设置的用户
				if ($this->_userdata['dpt'] != 0) {
					if ($this->warning_filter($row['code'], $row['relate_id']))
						continue;
				}
				//------------------------------------
				$return_row = array();
				$return_row['domain'] = "0";	//isis默认填0
				$return_row['id'] = $row['id'];
				$return_row['code'] = $row['code'];
				$return_row['relate_id'] = $row['relate_id'];
				switch ($row['code']) {
					case '2101':	//链路丢失
						$return_row['params'] = array_values(json_decode($row['text_params']));
						break;

					case '2102':	//链路建立
						$return_row['params'] = array_values($this->get_relate_params(1, $row['relate_id']));
						break;

					case '2103':	//路由器脱离网络
						$return_row['params'] = array_values(json_decode($row['text_params']));
						break;

					case '2104':	//路由器接入网络
						$return_row['params'] = array_values($this->get_relate_params(2, $row['relate_id']));
						break;
					
					default:
						$return_row['params'] = json_decode('[]');
						break;
				}
				$return_row['timestamp'] = $row['timestamp'];
				$return_array[] = $return_row;
			}
			$time = intval($return_array[count($return_array) - 1]['timestamp']);
		}
		return $return_array;
	}

	private function get_relate_params($type, $rel_id)
	{
		if ($type == 1) {		//链路建立
			//[id, attr_id, area_id, sys_id, interface_ip, mask, n_area_id, n_sys_id, link_type, metric, bandwidth, description]
			$this->db->select('isis_link_info.id, isis_link_attr.id AS attr_id, isis_link_info.area_id, isis_link_info.sys_id, isis_link_info.interface_ip, isis_link_info.mask, isis_link_info.n_area_id, isis_link_info.n_sys_id, isis_link_info.link_type, isis_link_info.metric, isis_link_attr.bandwidth, isis_link_attr.description');
			$this->db->from('isis_link_info');
			$this->db->where('isis_link_info.id', $rel_id);
			$this->db->join('isis_link_attr', 'isis_link_info.area_id = isis_link_attr.area_id AND isis_link_info.sys_id = isis_link_attr.sys_id AND isis_link_info.n_area_id = isis_link_attr.n_area_id AND isis_link_info.n_sys_id = isis_link_attr.n_sys_id', 'left outer');
			$query = $this->db->get();
			$row = $query->row_array();
			if (!is_null($row)) {
				if(is_null($row['attr_id'])) {
					$row['attr_id'] = '0';
				} 
				if(is_null($row['bandwidth'])) {
					$row['bandwidth'] = '0';
				}
				if(is_null($row['description'])) {
					$row['description'] = '';
				}
			} else {
				$row = array();
			}
			return $row;
		} elseif ($type == 2) {	//路由器接入
			//[id, attr_id, area_id, sys_id, router_type, dptid, alias, description, x, y, hostname, image]
			$this->db->select('isis_router_info.id, isis_router_attr.id AS attr_id, isis_router_info.area_id, isis_router_info.sys_id, isis_router_info.router_type, isis_router_attr.dptid, isis_router_attr.alias, isis_router_attr.description, isis_router_attr.x, isis_router_attr.y, isis_router_info.hostname, isis_router_attr.image');
			$this->db->from('isis_router_info');
			$this->db->where('isis_router_info.id', $rel_id);
			$this->db->join('isis_router_attr', 'isis_router_info.area_id = isis_router_attr.area_id AND isis_router_info.sys_id = isis_router_attr.sys_id', 'left outer');
			$query = $this->db->get();
			$row = $query->row_array();
			if (!is_null($row)) {
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
			} else {
				$row = array();
			}
			return $row;
		}
	}

	private function warning_filter($type, $rel_id)
	{
		$userdpt = $this->_userdata['dpt'];
		//链路
		if ($type == '2101' || $type == '2102') {
			$query = $this->db->get_where('isis_link_info', array('id' => $rel_id));
			$row = $query->row_array();
			if (!is_null($row)) {
				$query2 = $this->db->get_where('isis_router_attr', array('area_id' => $row['area_id'], 'sys_id' => $row['sys_id']));
				$row2 = $query2->row_array();
				if (empty($row2)) {	//未分配单位的路由器也过滤掉
					return true;
				}	else {
					if ($row2['dptid'] != $userdpt)
						return true;
				}	
				$query3 = $this->db->get_where('isis_router_attr', array('area_id' => $row['n_area_id'], 'sys_id' => $row['n_sys_id']));
				$row3 = $query3->row_array();
				if (empty($row3)) {	//未分配单位的路由器也过滤掉
					return true;
				} else {
					if ($row3['dptid'] != $userdpt)
						return true;
				}
			}
		}
		//路由器
		if ($type == '2103' || $type == '2104') {
			$this->db->select('isis_router_attr.dptid');
			$this->db->from('isis_router_info');
			$this->db->where('isis_router_info.id', $rel_id);
			$this->db->join('isis_router_attr', 'isis_router_info.area_id = isis_router_attr.area_id AND isis_router_info.sys_id = isis_router_attr.sys_id', 'left outer');
			$query = $this->db->get();
			$row = $query->row_array();
			if (empty($row)) {	//未分配单位的路由器也过滤掉
				return true;
			} else {
				if ($row['dptid'] != $userdpt)
					return true;
			}
			return false;
		}
		return false;	//不是路由告警不过滤
	}

}