<?php
// ------------------------------------------------------------------------

/**
 * RTXpert ISIS Link_attr Class
 *
 * ISIS link_attr model，ISIS链路信息
 * 
 *
 * @package		RTXpert
 * @subpackage	isis
 * @category	application/models/isis/link_attr.php
 * @author		DingZixuan
 * @link		http://wp4dzx.sinaapp.com
 */
class Link_attr extends CI_Model {
	
	public function __construct()
	{
		$this->load->database();
	}

	public function get_link_by_link($area, $sys, $narea, $nsys)
	{
		$query = $this->db->get_where('isis_link_attr', array('area_id' => $area, 
															  'sys_id' => $sys,
															  'n_area_id' => $narea,
															  'n_sys_id' => $nsys));
		return $query->row_array();
	}

	public function update_link_bw($id, $area_id, $sys_id, $n_area_id, $n_sys_id, $bandwidth)
	{
		$data = array('area_id' => $area_id,
					  'sys_id' => $sys_id,
					  'n_area_id' => $n_area_id,
					  'n_sys_id' => $n_sys_id,
					  'bandwidth' => $bandwidth,
					  'timestamp' => time());
		$return = 0;
		//表中无记录，插入
		if ($id == 0) {
			$linkRecord = $this->get_link_by_link($area_id, $sys_id, $n_area_id, $n_sys_id);
			if (empty($routerRecord)) {	//表中确实没有记录
				$this->db->insert('isis_link_attr', $data);
			} else {
				$this->db->where('id', $routerRecord['id']);
				$this->db->update('isis_link_attr', $data); 
			}
		} else {	//表中已有记录，更新
			//$this->db->where('id', $id);
			//2014-05-19：更改更新方式，不用attr_id，带宽设置那里提交的attr_id有问题，还未查到
			$this->db->where('area_id', $area_id);
			$this->db->where('sys_id', $sys_id);
			$this->db->where('n_area_id', $n_area_id);
			$this->db->where('n_sys_id', $n_sys_id);
			$this->db->update('isis_link_attr', $data); 
		}
		$return = $this->db->affected_rows();
		return $return;
	}
}