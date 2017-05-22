<?php
// ------------------------------------------------------------------------

/**
 * RTXpert Bgp_link_info Class
 *
 * 边界链路的model，
 * 
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/benchmark.html
 */
class Bgp_link_info extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}

	// --------------------------------------------------------------------

	/**
	 * 获取bgp_link_info表中的所有边界链路
	 *
	 * @access	public
	 * @param	
	 * @return	array()
	 */
	public function get_bgp_links()
	{
		$query = $this->db->get('bgp_link_info');
		return $query->result_array();
	}

	// --------------------------------------------------------------------
	
	/**
	 * 获取link_info表中的所有边界链路
	 *
	 * @access	public
	 * @param	
	 * @return	array()
	 */
	public function get_border_links($time)
	{
		$this->db->where('link_type', 11);
		$this->db->where('create_time <=', $time);
		$this->db->where('end_time >', $time);
		$query = $this->db->get('ospf_link_info');
		return $query->result_array();
	}

	// --------------------------------------------------------------------

	/**
	 * 更新bgp_link_info表中的所有边界链路
	 *
	 * @access	public
	 * @param		array(bgp_id, as_num, router_id, interface_ip, n_as_num, n_router_id, n_interface_ip, mask, metric)
	 * @return	
	 */
	public function update_bgp_links($update_bgp_arr)
	{
		$t = time();
		$cur_bgp_links = $this->get_bgp_links();	//bgp_link_info表中所有数据
		//step1.先insert与update：遍历用户提交的所有链路
		//1.如果其在bgp_link_info表中，则update bgp_link_info表
		//2.如果不在bgp_link_info表中，则insert bgp_link_info表
		$update = "";
		$insert = "";
		foreach ($update_bgp_arr as $uplink) {
			$data = $uplink;
			unset($data['bgp_id']);
			$flag = false;	//当前bgp_link_info表中的链路在不在$update_bgp_arr中
			foreach ($cur_bgp_links as $cbkey => $cblink) {
				if ($uplink['as_num'] == $cblink['as_num'] && $uplink['router_id'] == $cblink['router_id'] && $uplink['n_as_num'] == $cblink['n_as_num'] && $uplink['n_router_id'] == $cblink['n_router_id']) {
					//当前bgp_link_info表中有此链路，更新之
					$this->db->where('id', $cblink['id']);
					$this->db->update('bgp_link_info', $data);
					$update .= $cblink['id']." ";
					$flag = true;
					unset($cur_bgp_links[$cbkey]);		//从$bgp_link_info中删掉
				}
			}
			if (!$flag) {		//当前bgp_link_info表中没有此链路，插入之	
				$this->db->insert('bgp_link_info', $data);
				$insert .= $data['interface_ip'];
			}
		}

		//step2.剩下的是bgp_link_info中有，而用户提交中没有的，则从bgp_link_info中删除
		foreach ($cur_bgp_links as $cbkey => $cblink) {
			$this->db->where('id', $cblink['id']);
			$this->db->delete('bgp_link_info');
		}
	}

	



	// --------------------------------------------------------------------
	
	/**
	 * 同步bgp_link_info表到ospf_link_info表中的边界链路
	 * 以bgp_link_info表为基准同步
	 *
	 * @access	public
	 * @param	int $dptid 单位id
	 * @return	array(dptid, dptname, description)
	 */
	public function sync_border_links()
	{
		$t = time();
		$blinks_in_bgp = $this->get_bgp_links();						//bgp_link_info表中的所有数据
		$blinks_in_linkinfo = $this->get_border_links($t);	//ospf_link_info表中的所有数据
		//step1.先insert与update：遍历bgp_link_info表中所有链路
		//1.如果其在ospf_link_info表中，则update ospf_link_info表，并insert ospf_data_backup表
		//2.如果不在ospf_link_info表中，则insert ospf_link_info表，并insert ospf_data_backup表
		$update = "";
		$insert = "";
		foreach ($blinks_in_bgp as $bbkey => $bblink) {
			//更新的ospf_link_info数据
			$data1 = $bblink;
			$data1['create_time'] = $t;
			unset($data1['id']); unset($data1['n_interface_ip']);
			$data1['link_type'] = 11; 
			//更新的ospf_data_backup数据
			$data2 = array('tableName' => 'ospf_link_info',
										 'hz_tableName' => 'hz_ospf_link_info',
										 'timestamp' => $t);
			$flag = false;	//当前bgp_link_info表中的链路在不在$ospf_link_info中
			foreach ($blinks_in_linkinfo as $blkey => $bllink) {
				if ($bblink['as_num'] == $bllink['as_num'] && $bblink['router_id'] == $bllink['router_id'] && $bblink['n_as_num'] == $bllink['n_as_num'] && $bblink['n_router_id'] == $bllink['n_router_id']) {
					$flag = true;
					//当前ospf_link_info表中有此链路，更新之

					$this->db->where('id', $bllink['id']);
					$this->db->update('ospf_link_info', $data1);
					$data2['type'] = 2;
					$data2['relate_id'] = $bllink['id'];
					$this->db->insert('ospf_data_backup', $data2);
					unset($blinks_in_linkinfo[$blkey]);		//从$blinks_in_linkinfo中删掉
				}
			}
			if (!$flag) {		//当前ospf_link_info表中没有此链路，插入之
				$this->db->insert('ospf_link_info', $data1);
				$insert_id = $this->db->insert_id();
				$data2['type'] = 1;
				$data2['relate_id'] = $insert_id;
				$this->db->insert('ospf_data_backup', $data2);
			}
		}
		//step2.剩下的是ospf_link_info中有，而bgp_link_info表中没有的，则从ospf_link_info中删除（end_time置过期），并insert ospf_data_backup表
		foreach ($blinks_in_linkinfo as $blkey => $bllink) {
			$bllink['end_time'] = $t;
			$this->db->where('id', $bllink['id']);
			$this->db->update('ospf_link_info', $bllink);
			$data2 = array('tableName' => 'ospf_link_info',
										 'type' => 2,
										 'hz_tableName' => 'hz_ospf_link_info',
										 'relate_id' => $bllink['id'],
										 'timestamp' => $t);
			$this->db->insert('ospf_data_backup', $data2);
		}
	}
	
}