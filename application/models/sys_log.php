<?php
// ------------------------------------------------------------------------

/**
 * RTXpert sys_log Class
 *
 * sys_log model，系统日志model
 *
 * @package     RTXpert
 * @category    application/models/sys_log.php
 * @author      DingZixuan
 * @link        http://wp4dzx.sinaapp.com
 */
class Sys_log extends CI_Model {

    public function __construct()
    {
        $this->load->database();
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
     * 增加一条系统日志
     * 
     *
     * @access  public
     * @param   array()
     * @return  insert_id
     */  
    public function add_sys_log($code, $params = array())
    {
        $text_params = json_encode(array_values($params));
        $sql = "INSERT INTO syslog(`code`, `text_params`, `timestamp`) VALUES($code,".$this->db->escape($text_params).", UNIX_TIMESTAMP());";
        // $this->db->set('code', $code);
        // $this->db->set('text_params', $text_params);
        // $this->db->set('timestamp', UNIX_TIMESTAMP());
        // Execute the query
        // $this->db->insert('syslog');
        $this->db->query($sql);
        // Return the ID of the inserted row, 
        // or false if the row could not be inserted
        return $this->db->insert_id();
    }

    // --------------------------------------------------------------------

    /**
     * 按参数限定获取系统日志
     *
     *
     * @access  public
     * @param   array()
     * @return  对象数组
     */  
    public function get_sys_logs($options = array())
    {
        $this->db->select('syslog.*, syslog_dictionary.*');
        $this->db->from('syslog');
        $this->db->join('syslog_dictionary', 'syslog.code=syslog_dictionary.code', 'left');
        //var_dump($options);
        $options = $this->_default(array('sortDirection' => 'desc'), $options);
        // where = 字句拼接，暂时没有
        $qualificationArray = array();
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


        ///////////////////AGAIN：因为要获得总数（分页用）和查询结果，不得已查两次！！最好有办法能一次查好////////////////////////
        $ret['data'] = array();
        $this->db->select('syslog.*, syslog_dictionary.*');
        $this->db->from('syslog');
        $this->db->join('syslog_dictionary', 'syslog.code=syslog_dictionary.code', 'left');
        $options = $this->_default(array('sortDirection' => 'desc'), $options);
        // where = 字句拼接，暂时没有
        $qualificationArray = array();
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
        if ($query2->num_rows() > 0) {
            foreach ($query2->result_array() as $row) {
                $return_row = array();
                $return_row['id'] = $row['id'];
                $return_row['timestamp'] = $row['timestamp'];
                $return_row['code'] = $row['code'];
                $return_row['name'] = $row['name'];
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
                $ret['data'][] = $return_row;
            }
        }
        return $ret;
    }

}