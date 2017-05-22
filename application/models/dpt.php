<?php
// ------------------------------------------------------------------------

/**
 * RTXpert Dpt Class
 *
 * 单位信息的model，
 * between them.  Memory consumption can also be displayed.
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/benchmark.html
 */
class Dpt extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}
	

	// --------------------------------------------------------------------

	/**
	 * 根据单位ID查询某个单位
	 *
	 * @access	public
	 * @param	int $dptid 单位id
	 * @return	array(dptid, dptname, description)
	 */
	public function getDpt($dptid)
	{
		$query = $this->db->get_where('department', array('dptid' => $dptid));
		return $query->row_array();
	}
	

	// --------------------------------------------------------------------

	/**
	 * 根据单位名查询某个单位
	 *
	 * @access	public
	 * @param	string $dptname 单位名
	 * @return	关联数组
	 */
	public function getDptByName($dname)
	{
		$query = $this->db->get_where('department', array('dptname' => $dname));
		return $query->row_array();
	}

	
	// --------------------------------------------------------------------

	/**
	 * 查询所有单位
	 *
	 * @access	public
	 * @param	无
	 * @return	关联数组
	 */
	public function fetchAll()
	{
		$query = $this->db->get('department');
		return $query->result_array();
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * 增加一个单位
	 *
	 * @access	public
	 * @param	array('dptname', 'description')
	 * @return	int dptid
	 */
	public function addDpt($dptname, $description)
	{
		$data = array('dptname' => $dptname, 'description' => $description);
		$query = $this->db->insert('department', $data);
		return $this->db->insert_id();
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * 更新一个单位
	 *
	 * @access	public
	 * @param	array(dptid, dptname, description)
	 * @return	bool
	 */
	public function updateDpt($dpt = array())
	{
		$this->db->where('dptid', $dpt['dptid']);
		$this->db->update('department', $dpt);
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * 删除一个单位
	 *
	 * @access	public
	 * @param	
	 * @return	
	 */
	public function deleteDpt($dptid)
	{
		//先删除单位的所有用户
		$this->db->delete('users', array('dptid' => $dptid));
		//删除单位
		$this->db->delete('department', array('dptid' => $dptid)); 
	}
	
}