<?php
class User extends CI_Model {

	public function __construct()
	{
		$this->load->database();
	}
	
	// --------------------------------------------------------------------

	/**
	 * 根据用户名和密码进行登录检测
	 *
	 * @access	public
	 * @param	$username, $password
	 * @return	array $user
	 */
	public function checkLogin($username, $password)
	{
		$query = $this->db->get_where('users', array('username' => $username));
		$user = array();
		if ($query->num_rows() > 0) {
			if ($password == $query->row()->password) {
				$user['success'] = 0;
				$user['userid'] = $query->row()->userid;
				$user['username'] = $query->row()->username;
				$user['usertype'] = $query->row()->usertype;
				$user['dpt'] = $query->row()->dptid;
				
			} else {
				$user['success'] = -1;	//密码不正确
			}
		} else {	
			$user['success'] = -2;	//用户名不存在
		}
		return $user;
	}

	// --------------------------------------------------------------------

	/**
	 * 修改密码
	 *
	 * @access	public
	 * @param	$username, $password
	 * @return	int -1
	 */
	public function resetPassword($uid, $oripw, $newpw)
	{
		$query = $this->db->get_where('users', array('userid' => $uid));
		if ($query->num_rows() > 0) {
			if ($password == $query->row()->password) {
				$user['success'] = 1;
				$user['userid'] = $query->row()->userid;
				$user['username'] = $query->row()->username;
				$user['usergender'] = $query->row()->usergender;
				$user['usertype'] = $query->row()->usertype;
				$user['dpt'] = $query->row()->dptid;
			} else {
				$user['success'] = -1;
			}
		} else {
			$user['success'] = -2;
		}
		return $user;
	}
	
	// --------------------------------------------------------------------

	/**
	 * 根据用户ID查询某个用户
	 *
	 * @access	public
	 * @param	int $uid 用户id
	 * @return	关联数组
	 */
	public function getUser($uid)
	{
		$query = $this->db->get_where('users', array('userid' => $uid));
		return $query->row_array();
	}


	// --------------------------------------------------------------------

	/**
	 * 根据用户名查询某个用户
	 *
	 * @access	public
	 * @param	string $uname 用户名
	 * @return	关联数组
	 */
	public function getUserByName($uname)
	{
		$query = $this->db->get_where('users', array('username' => $uname));
		return $query->row_array();
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * 查询所有用户
	 *
	 * @access	public
	 * @param	无
	 * @return	关联数组
	 */
	public function fetchAll()
	{
		$query = $this->db->get('users');
		return $query->result_array();
	}

	
	// --------------------------------------------------------------------

	/**
	 * 增加一个用户
	 *
	 * @access	public
	 * @param	
	 * @return	
	 */
	public function addUser($username, $password, $dpt, $usertype)
	{
		$data = array('username' => $username, 'password' => md5($password), 'dptid' => $dpt, 'usertype' => $usertype);
		$query = $this->db->insert('users', $data);
		return $this->db->insert_id();
	}
		
	
	// --------------------------------------------------------------------

	/**
	 * 更新一个用户
	 *
	 * @access	public
	 * @param	
	 * @return	
	 */
	public function updateUser($user = array())
	{
		//$data = array('username' => $username, 'password' => md5($password),'usertype' => $usertype,'dpt' => $dpt);
		$this->db->where('userid', $user['userid']);
		$this->db->update('users', $user); 
	}
	
	
	// --------------------------------------------------------------------

	/**
	 * 删除一个用户
	 *
	 * @access	public
	 * @param	
	 * @return	
	 */
	public function deleteUser($uid)
	{
		$this->db->delete('users', array('userid' => $uid)); 
	}
	
}



