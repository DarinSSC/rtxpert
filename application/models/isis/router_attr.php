<?php
// ------------------------------------------------------------------------

/**
 * RTXpert ISIS Router_attr Class
 *
 * ISIS router_attr model，ISIS路由器信息
 * 
 *
 * @package		RTXpert
 * @subpackage	isis
 * @category	application/models/isis/router_attr.php
 * @author		DingZixuan
 * @link		http://wp4dzx.sinaapp.com
 */
class Router_attr extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}

	public function get_router_by_id($id)
	{
		$query = $this->db->get_where('isis_router_attr', array('id' => $id));
		return $query->row_array();
	}

	public function get_router_by_router($areaid, $rid)
	{
		$query = $this->db->get_where('isis_router_attr', array('area_id' => $areaid, 'sys_id' => $rid));
		return $query->row_array();
	}

	// --------------------------------------------------------------------

	/**
	 * 修改路由器信息
	 *
	 * Multiple calls to this function can be made so that several
	 * execution points can be timed
	 *
	 * @access	public
	 * @param	array	$arr(id, area_id, sys_id, alias, description, dptid)
	 * @return	
	 */
	public function update_router_attr($id, $area_id, $sys_id, $alias, $description, $dptid, $image)
	{
		$data = array('area_id' => $area_id,
					  'sys_id' => $sys_id,
					  'alias' => $alias,
					  'description' => $description,
					  'dptid' => $dptid,
					  'timestamp' => time(),
					  'image' => $image);
		$return = 0;
		//表中无记录，插入
		if ($id == 0) {
			$routerRecord = $this->get_router_by_router($area_id, $sys_id);
			if (empty($routerRecord)) {	//表中确实没有记录
				$this->db->insert('isis_router_attr', $data);
			} else {
				$this->db->where('id', $routerRecord['id']);
				$this->db->update('isis_router_attr', $data); 
			}
		} else {	//表中已有记录，更新
			$this->db->where('id', $id);
			$this->db->update('isis_router_attr', $data); 
		}
		$return = $this->db->affected_rows();
		return $return;
	}

	// --------------------------------------------------------------------

	/**
	 * 修改路由器布局
	 *
	 * Multiple calls to this function can be made so that several
	 * execution points can be timed
	 *
	 * @access	public
	 * @param	array	$arr(id, area_id, sys_id, x, y)
	 * @return	
	 */
	public function update_router_pos($id, $area_id, $sys_id, $x, $y)
	{
		$data = array('area_id' => $area_id,
					  'sys_id' => $sys_id,
					  'x' => $x,
					  'y' => $y,
					  'timestamp' => time());
		$return = 0;
		//表中无记录，插入
		if ($id == 0) {
			$routerRecord = $this->get_router_by_router($area_id, $sys_id);
			if (empty($routerRecord)) {	//表中确实没有记录
				$this->db->insert('isis_router_attr', $data);
			} else {
				$this->db->where('id', $routerRecord['id']);
				$this->db->update('isis_router_attr', $data); 
			}
		} else {	//表中已有记录，更新
			$this->db->where('id', $id);
			$this->db->update('isis_router_attr', $data); 
		}
		$return = $this->db->affected_rows();
		return $return;
	}	
}