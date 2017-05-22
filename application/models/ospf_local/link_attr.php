<?php
// ------------------------------------------------------------------------

/**
 * RTXpert OSPF Local Link_attr Class
 *
 * OSPF Local link_attr model，本地 OSPF 链路信息
 * 
 *
 * @package		RTXpert
 * @subpackage	ospf_local
 * @category	application/models/ospf_local/link_attr.php
 * @author		DingZixuan
 * @link		http://wp4dzx.sinaapp.com
 */
class Link_attr extends CI_Model {
	
	public function __construct()
	{
		$this->load->database();
	}

	public function get_link_by_link($asnum, $rid, $nasnum, $nrid)
	{
		$query = $this->db->get_where('ospf_link_attr', array('as_num' => $asnum, 
															  'router_id' => $rid,
															  'n_as_num' => $nasnum,
															  'n_router_id' => $nrid));
		return $query->row_array();
	}

	public function update_all_link_bw($bw)
	{
		//先把link_attr表中所有记录赋值为$bw
		$data = array('bandwidth' => $bw);
		$this->db->update('ospf_link_attr', $data); 
		//ATTENTION!!!! 这里靠interface_ip唯一来查询在link_info表中而不在link_attr表中的记录
		$time = time();
		$query = "SELECT * FROM ospf_link_info WHERE link_type IN(1,2,11) AND create_time <= {$time} AND end_time > {$time} AND interface_ip NOT IN(SELECT interface_ip FROM ospf_link_attr)";
		$query = $this->db->query($query);
		//再把link_info表中有而link_attr中没有的记录插入到link_attr中
		$insert_batch_arr = array();
		foreach ($query->result_array() as $row) {
		}
	}

	public function update_link_bw($id, $as_num, $router_id, $n_as_num, $n_router_id, $bandwidth)
	{
		$data = array('as_num' => $as_num,
					  'router_id' => $router_id,
					  'n_as_num' => $n_as_num,
					  'n_router_id' => $n_router_id,
					  'bandwidth' => $bandwidth,
					  'timestamp' => time());
		$return = 0;
		//表中无记录，插入
		if ($id == 0) {
			$linkRecord = $this->get_link_by_link($as_num, $router_id, $n_as_num, $n_router_id);
			if (empty($routerRecord)) {	//表中确实没有记录
				$this->db->insert('ospf_link_attr', $data);
			} else {
				$this->db->where('id', $routerRecord['id']);
				$this->db->update('ospf_link_attr', $data); 
			}
		} else {	//表中已有记录，更新
			//$this->db->where('id', $id);
			//2014-05-19：更改更新方式，不用attr_id，带宽设置那里提交的attr_id有问题，还未查到
			$this->db->where('as_num', $as_num);
			$this->db->where('router_id', $router_id);
			$this->db->where('n_as_num', $n_as_num);
			$this->db->where('n_router_id', $n_router_id);
			$this->db->update('ospf_link_attr', $data); 
		}
		$return = $this->db->affected_rows();
		return $return;
	}
}