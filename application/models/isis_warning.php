<?php
// ------------------------------------------------------------------------

/**
 * RTXpert warning Class
 *
 * warning model，告警model
 *
 * @package		RTXpert
 * @category	application/models/warning.php
 * @author		DingZixuan
 * @link		http://wp4dzx.sinaapp.com
 */
class Isis_warning extends CI_Model {

	/**
	* 登陆用户信息
	*
	* @array(userid, username, usertype, dpt)
	* @access public
	*/
	public $_userdata;

	private $_ishq = 0;
	private $_protocol = '';

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

	/**
	* _default 方法为 $options 数组赋予 $defaults 数组中指定的默认值，
	* 如果 $options 数组中已存在该字段，则会覆盖默认值.
	*
	* @param array $defaults
	* @param array $options
	* @return array
	*/
	private function _default($defaults, $options)
	{
	  return array_merge($defaults, $options);
	}

	/**
	* _required 方法检查 $data 数组中是否包含 $required 数组中的所有key，
	* 如果不包含所有，则返回false.
	*
	* @param array $required
	* @param array $data
	* @return bool
	*/
	private function _required($required, $data)
	{
    foreach ($required as $field) {
        if(!isset($data[$field])) {
            return false;
        }
    }
    return true;
	}

	// --------------------------------------------------------------------

	/**
	 * 获取所有未解决的告警
	 * silverlight 查询接口用
	 * 
	 *
	 * @access	public
	 * @param	
	 * @return	对象数组
	 */  
	public function get_unsolved_warnings()
	{
		$return_array = array();
		$this->db->select('isis_warning.*, warning_dictionary.*');
		$this->db->from('isis_warning');
		$this->db->where('solved', 0);
	  $this->db->join('warning_dictionary', 'isis_warning.code=warning_dictionary.code', 'left');
	  $this->db->order_by("timestamp", "desc");

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
				$return_row['domain'] = "0";
				$return_row['id'] = $row['id'];
				$return_row['timestamp'] = $row['timestamp'];
				$return_row['code'] = $row['code'];
				$return_row['name'] = $row['name'];
				$return_row['level'] = $row['level'];
				$text_params = json_decode($row['text_params']);
				$ind = 0; $text = '';
				for ($i=0; $i<strlen($row['text_template']); $i++) {
					if ($row['text_template'][$i] == '*') {
						$text .= $text_params[$ind];
						$ind++;
					} else {
						$text .= $row['text_template'][$i];
					}
				}
				$return_row['text'] = $text;
				$return_row['handleinfo'] = $row['handle_info'];
				$return_array[] = $return_row;
			}
		}
		return $return_array;
	}

	// --------------------------------------------------------------------

  /**
   * 按参数限定获取告警日志
   * index/warninglog页面展示用
   * 
   *
   * @access  public
   * @param   array()
   * @return  对象数组
   */  
    public function getWarninglogs2($options = array())
  {
  	// var_dump($options);exit;
    ///////////////////AGAIN：因为要获得总数（分页用）和查询结果，不得已查两次！！最好有办法能一次查好////////////////////////
    $ret['data'] = array();
		$this->db->select('isis_warning.*, warning_dictionary.*');
		$this->db->from('isis_warning');
		$this->db->join('warning_dictionary', 'isis_warning.code=warning_dictionary.code', 'left');
		$this->db->where('warning_dictionary.code >', 2000);
		$options = $this->_default(array('sortDirection' => 'desc'), $options);
    // where = 子句拼接
	if(isset($options["name"])) {
		$this->db->where("isis_warning.code", $options["name"] + 2100);
	}
    $qualificationArray = array('level', 'solved');
    foreach($qualificationArray as $qualifier)
    {
        if(isset($options[$qualifier])) {
            $this->db->where($qualifier, $options[$qualifier]);
        }
    }
    // 检索时间
    if (isset($options['sttime'])) {
        $this->db->where('timestamp >=', $options['sttime']);
    }
    if (isset($options['edtime'])) {
        $this->db->where('timestamp <=', $options['edtime']);
    }
    // 排序
    if(isset($options['sortBy'])) {
        $this->db->order_by($options['sortBy'], $options['sortDirection']);
    }
    $tmpl=10;
    $offset=0;
    if(isset($options['limit'])) {
        $tmpl = $options['limit'];
    } 
    if(isset($options['offset'])) {
        $offset = $options['offset'];
    }
    $query2 = $this->db->get();
    $query_sum = 0;
    $ret['total'] = $query_sum;
    $query3 = array();
    if ($query2->num_rows() > 0) {

		foreach ($query2->result_array() as $row2) {
			//----ISIS：根据单位对节点进行过滤----
			//不是系统管理员且进行了单位设置的用户
			if ($this->_userdata['dpt'] != 0) {
				if ($this->warning_filter($row2['code'], $row2['relate_id']))
					continue;
			}
			$query3[] = $row2;
		}    
		$tmp_array = array();
		foreach( $query3 as $key => $value) {
			if(in_array($value['id'], $tmp_array)) {
				unset($query3[$key]);
			} else {
				$tmp_array[] = $value['id'];
			}
		}
		sort($query3);
		$query_sum = count($query3);
		$ret['total'] = $query_sum;
		echo "<script type='text/javascript'>alert(".$query_sum.");</script>";
		$ret_sum = 0;
		for($i = $offset; $i < $query_sum; $i++) {
			$row=$query3[$i];
			//----ISIS：根据单位对节点进行过滤----
			//不是系统管理员且进行了单位设置的用户
			if ($this->_userdata['dpt'] != 0) {
				if ($this->warning_filter($row['code'], $row['relate_id']))
					continue;
			}
			//------------------------------------
			$return_row = array();
			$return_row['domain'] = "0";
			$return_row['id'] = $row['id'];
			$return_row['timestamp'] = $row['timestamp'];
			$return_row['code'] = $row['code'];
			$return_row['name'] = $row['name'];
			$return_row['class'] = $row['class'];
			$return_row['level'] = $row['level'];
			$return_row['parse_time'] = $row['parse_time'];
			$return_row['snmp_time'] = $row['snmp_time'];
			$return_row['handleinfo'] = $row['handle_info'];
			$text_params = json_decode($row['text_params']);
			$ind = 0; $text = '';
			for ($i=0; $i<strlen($row['text_template']); $i++) {
				if ($row['text_template'][$i] == '*') {
					$text .= $text_params[$ind];
					$ind++;
				} else {
					$text .= $row['text_template'][$i];
				}
			}
			$return_row['text'] = $text;
			$return_row['solved'] = $row['solved'];
			$ret['data'][] = $return_row;
			$ret_sum++;
			if($ret_sum == $tmpl) break;
		}
	}
    return $ret;
  }
  public function getWarninglogs($options = array())
  {
  	//var_dump($options);exit;
  	$this->db->select('isis_warning.*, warning_dictionary.*');
		$this->db->from('isis_warning');
		$this->db->join('warning_dictionary', 'isis_warning.code=warning_dictionary.code', 'left');
		$this->db->where('warning_dictionary.code >', 2000);
		$this->db->where('warning_dictionary.code <', 2200);
		$options = $this->_default(array('sortDirection' => 'desc'), $options);
    // where = 子句拼接
	if(isset($options["name"])) {
		$this->db->where("isis_warning.code", $options["name"] + 2100);
	}
    $qualificationArray = array('level', 'solved');
    foreach($qualificationArray as $qualifier)
    {
        if(isset($options[$qualifier])) {
            $this->db->where($qualifier, $options[$qualifier]);
        } 
    }
    // 检索时间
    if (isset($options['sttime'])) {
        $this->db->where('timestamp >=', $options['sttime']);
    }
    if (isset($options['edtime'])) {
        $this->db->where('timestamp <=', $options['edtime']);
    }
    //获得总数
    $query = $this->db->get();
    $ret['total'] = $query->num_rows();
    //echo $ret['total'];exit;

    ///////////////////AGAIN：因为要获得总数（分页用）和查询结果，不得已查两次！！最好有办法能一次查好////////////////////////
    $ret['data'] = array();
		$this->db->select('isis_warning.*, warning_dictionary.*');
		$this->db->from('isis_warning');
		$this->db->join('warning_dictionary', 'isis_warning.code=warning_dictionary.code', 'left');
		$this->db->where('warning_dictionary.code >', 2000);
		$this->db->where('warning_dictionary.code <', 2200);
		$options = $this->_default(array('sortDirection' => 'desc'), $options);
    // where = 子句拼接
	if(isset($options["name"])) {
		$this->db->where("isis_warning.code", $options["name"] + 2100);
	}
    $qualificationArray = array('level', 'solved');
    foreach($qualificationArray as $qualifier)
    {
        if(isset($options[$qualifier])) {
            $this->db->where($qualifier, $options[$qualifier]);
        } 
    }
    // 检索时间
    if (isset($options['sttime'])) {
        $this->db->where('timestamp >=', $options['sttime']);
    }
    if (isset($options['edtime'])) {
        $this->db->where('timestamp <=', $options['edtime']);
    }
    // 分页 
    if(isset($options['limit']) && isset($options['offset'])) {
        $this->db->limit($options['limit'], $options['offset']);
    } else if(isset($options['limit'])) {
        $this->db->limit($options['limit']);
    }
    // 排序
    if(isset($options['sortBy'])) {
        $this->db->order_by($options['sortBy'], $options['sortDirection']);
    }

    $query2 = $this->db->get();
    if ($query->num_rows() > 0) {
			foreach ($query2->result_array() as $row) {
				//----ISIS：根据单位对节点进行过滤----
				//不是系统管理员且进行了单位设置的用户
				if ($this->_userdata['dpt'] != 0) {
					if ($this->warning_filter($row['code'], $row['relate_id']))
						continue;
				}
				//------------------------------------
				$return_row = array();
				$return_row['domain'] = "0";
				$return_row['id'] = $row['id'];
				$return_row['timestamp'] = $row['timestamp'];
				$return_row['code'] = $row['code'];
				$return_row['name'] = $row['name'];
				$return_row['class'] = $row['class'];
				$return_row['level'] = $row['level'];
				$return_row['parse_time'] = $row['parse_time'];
				$return_row['snmp_time'] = $row['snmp_time'];
				$return_row['handleinfo'] = $row['handle_info'];
				$text_params = json_decode($row['text_params']);
				$ind = 0; $text = '';
				for ($i=0; $i<strlen($row['text_template']); $i++) {
					if ($row['text_template'][$i] == '*') {
						$text .= $text_params[$ind];
						$ind++;
					} else {
						$text .= $row['text_template'][$i];
					}
				}
				$return_row['text'] = $text;
				$return_row['solved'] = $row['solved'];
				$ret['data'][] = $return_row;
			}
		}
    return $ret;
  }

	// --------------------------------------------------------------------

	/**
	 * 修改告警处理状态
	 * silverlight&页面接口
	 *
	 *
	 * @access	public
	 * @param	告警id 修改的告警状态
	 * @return	对象数组
	 */
	public function setWarningStatus($wid, $status)
	{
		$data = array('solved' => $status);
		if($wid != -1) {
			$this->db->where('id', $wid);
		}
		$query = $this->db->update('isis_warning', $data);
		if ($query) return $wid;
		else return 0;
	}

	// --------------------------------------------------------------------

	/**
	 * 查询当前数据库中时间戳最大的告警
	 * 暂时没用到
	 *
	 * @access	public
	 * @param	$sttime	$edtime
	 * @return	对象数组
	 */
	public function getMaxWarningTime()
	{
		$this->db->select('MAX(timestamp) as Mtime');
		$this->db->from('isis_warning');
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row()->Mtime;
		} else {
			return 0;
		}	
	}

	private function warning_filter($type, $rel_id)
	{
		$userdpt = $this->_userdata['dpt'];
		//链路
		if ($type == '2101' || $type == '2102') {
			$query = $this->db->get_where('isis_link_info', array('id' => $rel_id));
			$row = $query->row_array();
			if (!(is_null($row) || empty($row))) {
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