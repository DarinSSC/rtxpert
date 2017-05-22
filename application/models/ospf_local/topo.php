<?php
// ------------------------------------------------------------------------

/**
 * RTXpert OSPF Local Topo Class
 *
 * OSPF Local topo model, 本地OSPF路由拓扑的model
 * 
 *
 * @package		RTXpert
 * @subpackage	ospf_local
 * @category	application/models/ospf_local/topo.php
 * @author		DingZixuan
 * @link		http://wp4dzx.sinaapp.com
 */
class Topo extends CI_Model {
	
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
		//step1: 查询所有有效router信息：ospf_router_info左外连接ospf_router_attr表
		//[id, attr_id, as_num, router_id, router_type, dptid, alias, description, x, y, image]
		$routers = array();
		$this->db->select('ospf_router_info.id, ospf_router_attr.id AS attr_id, ospf_router_info.as_num, ospf_router_info.router_id, ospf_router_info.router_type, ospf_router_attr.dptid, ospf_router_attr.alias, ospf_router_attr.description, ospf_router_attr.x, ospf_router_attr.y, ospf_router_attr.image');
		$this->db->from('ospf_router_info');
		$this->db->where('ospf_router_info.create_time <=', $this->sttime);
		$this->db->where('ospf_router_info.end_time >', $this->sttime);
		//$this->db->group_by('router_id');	//13-05-08 删掉多余routers
		$this->db->join('ospf_router_attr', 'ospf_router_info.as_num = ospf_router_attr.as_num AND ospf_router_info.router_id = ospf_router_attr.router_id', 'left outer');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row)
			{
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
		//[id, attr_id, as_num, router_id, interface_ip, mask, n_as_num, n_router_id, area_id, link_type, metric, bandwidth, description]
		$links = array();
		$this->db->select('ospf_link_info.id, ospf_link_attr.id AS attr_id, ospf_link_info.as_num, ospf_link_info.router_id, ospf_link_info.interface_ip, ospf_link_info.mask, ospf_link_info.n_as_num, ospf_link_info.n_router_id, ospf_link_info.area_id, ospf_link_info.link_type, ospf_link_info.metric, ospf_link_attr.bandwidth, ospf_link_attr.description');
		$this->db->from('ospf_link_info');
		$this->db->where('ospf_link_info.create_time <=', $this->sttime);
		$this->db->where('ospf_link_info.end_time >', $this->sttime);
		$this->db->where_in('link_type', array(1,2,11));
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
		$this->db->from('warning');
		$this->db->where_in('code', array(1101,1102,1103,1104));	//只检索ospf的路由器增删和链路增删告警
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
				//----OSPF：根据单位对节点进行过滤----
				//不是系统管理员且进行了单位设置的用户
				if ($this->_userdata['dpt'] != 0) {
					if (intval($row['dptid']) != intval($this->_userdata['dpt']))
						continue;
				}
				//------------------------------------
				$return_row = array();
				$return_row['id'] = $row['id'];
				$return_row['code'] = $row['code'];
				$return_row['relate_id'] = $row['relate_id'];
				switch ($row['code']) {
					case '1101':	//链路丢失
						$return_row['params'] = array_values(json_decode($row['text_params']));
						break;

					case '1102':	//链路建立
						$return_row['params'] = array_values($this->get_relate_params(1, $row['relate_id']));
						break;

					case '1103':	//路由器脱离网络
						$return_row['params'] = array_values(json_decode($row['text_params']));
						break;

					case '1104':	//路由器接入网络
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
			$this->db->select('ospf_link_info.id, ospf_link_attr.id AS attr_id, ospf_link_info.as_num, ospf_link_info.router_id, ospf_link_info.interface_ip, ospf_link_info.mask, ospf_link_info.n_as_num, ospf_link_info.n_router_id, ospf_link_info.area_id, ospf_link_info.link_type, ospf_link_info.metric, ospf_link_attr.bandwidth, ospf_link_attr.description');
			$this->db->from('ospf_link_info');
			$this->db->where('ospf_link_info.id', $rel_id);
			$this->db->join('ospf_link_attr', 'ospf_link_info.as_num = ospf_link_attr.as_num AND ospf_link_info.router_id = ospf_link_attr.router_id AND ospf_link_info.n_as_num = ospf_link_attr.n_as_num AND ospf_link_info.n_router_id = ospf_link_attr.n_router_id', 'left outer');
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
			$this->db->select('ospf_router_info.id, ospf_router_attr.id AS attr_id, ospf_router_info.as_num, ospf_router_info.router_id, ospf_router_info.router_type, ospf_router_attr.dptid, ospf_router_attr.alias, ospf_router_attr.description, ospf_router_attr.x, ospf_router_attr.y, ospf_router_attr.image');
			$this->db->from('ospf_router_info');
			$this->db->where('ospf_router_info.id', $rel_id);
			$this->db->join('ospf_router_attr', 'ospf_router_info.as_num = ospf_router_attr.as_num AND ospf_router_info.router_id = ospf_router_attr.router_id', 'left outer');
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

	// --------------------------------------------------------------------

	/**
	 * 查看某条链路及其两端路由器是否在link表和router表中存在
	 *
	 * 
	 *
	 * @access	public
	 * @param	
	 * @return	[{"id", "code", "relate_id", "params", "timestamp"}]
	 */  
	public function get_link_exist($time, $as1, $router1, $as2, $router2)
	{
		//先查router是否都在
		$this->db->select('*');
		$this->db->from('ospf_router_info');
		$this->db->where('as_num', $as1);
		$this->db->where('router_id', $router1);
		$this->db->where('create_time <=', $time);
		$this->db->where('end_time >', $time);
		$query = $this->db->get();
		if ($query->num_rows() < 1) {
			return 0;
		}
		$this->db->select('*');
		$this->db->from('ospf_router_info');
		$this->db->where('as_num', $as2);
		$this->db->where('router_id', $router2);
		$this->db->where('create_time <=', $time);
		$this->db->where('end_time >', $time);
		$query = $this->db->get();
		if ($query->num_rows() < 1) {
			return 0;
		}
		//再检查link是否都在
		$this->db->select('*');
		$this->db->from('ospf_link_info');
		$this->db->where('as_num', $as1);
		$this->db->where('router_id', $router1);
		$this->db->where('n_as_num', $as2);
		$this->db->where('n_router_id', $router2);
		$this->db->where('create_time <=', $time);
		$this->db->where('end_time >', $time);
		$query = $this->db->get();
		if ($query->num_rows() < 1) {
			return 0;
		}
		$this->db->select('*');
		$this->db->from('ospf_link_info');
		$this->db->where('as_num', $as2);
		$this->db->where('router_id', $router2);
		$this->db->where('n_as_num', $as1);
		$this->db->where('n_router_id', $router1);
		$this->db->where('create_time <=', $time);
		$this->db->where('end_time >', $time);
		$query = $this->db->get();
		if ($query->num_rows() < 1) {
			return 0;
		}
		return 1;
	}

}