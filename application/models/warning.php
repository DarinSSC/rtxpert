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
class Warning extends CI_Model {

	
	private $_ishq = 0;
	private $_protocol = '';

	public function __construct()
	{
		$this->load->database();
	}

	public function construct($protocol='ospf', $ishq=0)
	{
		$this->_protocol = $protocol;
		$this->_ishq = $ishq;
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
		if ($this->_ishq) {
			$this->db->select('hz_warning.*, warning_dictionary.*');
			$this->db->from('hz_warning');
			$this->db->where('solved', 0);
		  $this->db->join('warning_dictionary', 'hz_warning.code=warning_dictionary.code', 'left');
		  $this->db->order_by("timestamp", "desc");
		} else {
			$this->db->select('warning.*, warning_dictionary.*');
			$this->db->from('warning');
			$this->db->where('solved', 0);
		  $this->db->join('warning_dictionary', 'warning.code=warning_dictionary.code', 'left');
		  $this->db->order_by("timestamp", "desc");
		}
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				$return_row = array();
				if(array_key_exists("as_num", $row)) {	//2014.02.16:ospf总部多as_num字段
					$return_row['domain'] = $row['as_num'];
				} else {
					$return_row['domain'] = "0";
				}
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
  public function getWarninglogs($options = array())
  {
  	//var_dump($options);exit;
	if ($this->_ishq) {
		$this->db->select('hz_warning.*, warning_dictionary.*');
		$this->db->from('hz_warning');
	  $this->db->join('warning_dictionary', 'hz_warning.code=warning_dictionary.code', 'left');
	} else {
		$this->db->select('warning.*, warning_dictionary.*');
		$this->db->from('warning');
	  $this->db->join('warning_dictionary', 'warning.code=warning_dictionary.code', 'left');
	}
	if ($this->_protocol == "ospf") {
		$this->db->where('warning_dictionary.code >', 1000);
		$this->db->where('warning_dictionary.code <', 1200);
	}else {
		$this->db->where('warning_dictionary.code >', 2000);
		$this->db->where('warning_dictionary.code <', 2200);
	}
    $options = $this->_default(array('sortDirection' => 'desc'), $options);
    // where = 子句拼接
	if(isset($options["name"])) {
		if($this->_ishq) {
			$this->db->where("hz_warning.code", $options["name"] + 1100);
		} else {
			$this->db->where("warning.code", $options["name"] + 1100);
		}
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
	if ($this->_ishq) {
		$this->db->select('hz_warning.*, warning_dictionary.*');
		$this->db->from('hz_warning');
	  $this->db->join('warning_dictionary', 'hz_warning.code=warning_dictionary.code', 'left');
	} else {
		$this->db->select('warning.*, warning_dictionary.*');
		$this->db->from('warning');
	  $this->db->join('warning_dictionary', 'warning.code=warning_dictionary.code', 'left');
	}
	if ($this->_protocol == "ospf") {
		$this->db->where('warning_dictionary.code >', 1000);
		$this->db->where('warning_dictionary.code <', 1200);
	}else {
		$this->db->where('warning_dictionary.code >', 2000);
		$this->db->where('warning_dictionary.code <', 2200);
	}
    $options = $this->_default(array('sortDirection' => 'desc'), $options);
    // where = 子句拼接
	if(isset($options["name"])) {
		if($this->_ishq) {
			$this->db->where("hz_warning.code", $options["name"] + 1100);
		} else {
			$this->db->where("warning.code", $options["name"] + 1100);
		}
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
				$return_row = array();
				if(array_key_exists("as_num", $row)) {	//2014.02.16:ospf总部多as_num字段
					$return_row['domain'] = $row['as_num'];
				} else {
					$return_row['domain'] = "0";
				}
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
	 * @param	$sttime	$edtime
	 * @return	对象数组
	 */
	public function setWarningStatus($as, $wid, $status)
	{

		$tablename = $this->_ishq?'hz_warning':'warning';
		$data = array('solved' => $status);
		if($wid != -1) {
			$this->db->where('id', $wid);
		}
		if(($as != -1) && ($this->_ishq)) {	//2014.02.16:总部要加上as_num
			$this->db->where('as_num', $as);
		}
		$query = $this->db->update($tablename, $data);
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
		if ($this->_ishq) {
			$this->db->from('hz_warning');
		} else {
			$this->db->from('warning');
		}
		$query = $this->db->get();
		if ($query->num_rows() > 0) {
			return $query->row()->Mtime;
		} else {
			return 0;
		}	
	}

}