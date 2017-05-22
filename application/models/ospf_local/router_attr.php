<?php
// ------------------------------------------------------------------------

/**
 * RTXpert OSPF Local Router_attr Class
 *
 * OSPF Local router_attr model，本地OSPF路由器信息
 * 
 *
 * @package		RTXpert
 * @subpackage	ospf_local
 * @category	application/models/ospf_local/router_attr.php
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
		$query = $this->db->get_where('ospf_router_attr', array('id' => $id));
		return $query->row_array();
	}

	public function get_router_by_router($asnum, $rid)
	{
		$query = $this->db->get_where('ospf_router_attr', array('as_num' => $asnum, 'router_id' => $rid));
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
	 * @param	array	$arr(id, alias, description, dpt)
	 * @return	
	 */
	public function update_router_attr($id, $as_num, $router_id, $alias, $description, $dptid, $image)
	{
		$data = array('as_num' => $as_num,
					  'router_id' => $router_id,
					  'alias' => $alias,
					  'description' => $description,
					  'dptid' => $dptid,
					  'timestamp' => time(),
					  'image' => $image);
		$return = 0;
		//表中无记录，插入
		if ($id == 0) {
			$routerRecord = $this->get_router_by_router($as_num, $router_id);
			if (empty($routerRecord)) {	//表中确实没有记录
				$this->db->insert('ospf_router_attr', $data);
			} else {
				$this->db->where('id', $routerRecord['id']);
				$this->db->update('ospf_router_attr', $data); 
			}
		} else {	//表中已有记录，更新
			$this->db->where('id', $id);
			$this->db->update('ospf_router_attr', $data); 
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
	 * @param	array	$arr(id, as_num, router_id, x, y)
	 * @return	
	 */
	public function update_router_pos($id, $as_num, $router_id, $x, $y)
	{
		$data = array('as_num' => $as_num,
					  'router_id' => $router_id,
					  'x' => $x,
					  'y' => $y,
					  'timestamp' => time());
		$return = 0;
		//表中无记录，插入
		if ($id == 0) {
			$routerRecord = $this->get_router_by_router($as_num, $router_id);
			if (empty($routerRecord)) {	//表中确实没有记录
				$this->db->insert('ospf_router_attr', $data);
			} else {
				$this->db->where('id', $routerRecord['id']);
				$this->db->update('ospf_router_attr', $data); 
			}
		} else {	//表中已有记录，更新
			$this->db->where('id', $id);
			$this->db->update('ospf_router_attr', $data); 
		}
		$return = $this->db->affected_rows();
		return $return;
	}	
}