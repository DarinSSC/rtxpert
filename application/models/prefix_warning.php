<?php

/**
 * Created by PhpStorm.
 * User: wangcong
 * Date: 2017/5/23
 * Time: 13:42
 */
class prefix_warning extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    public function extendRow($row){
        $text = $row['text_params'];
        $len = strlen($text);
        $res = substr($text, 1, $len-2);
        $list = explode(',', $res);
        $pre = substr($list[1], 1, strlen($list[1])-2);
        $pre_len = substr($list[2], 1, strlen($list[2])-2);
        $row['prefix'] = $pre.'/'.$pre_len;
//        echo $para;
        return $row;
    }

    public function getPrefixWarning(){
        $this->db->select('*');
        $this->db->from('warning');
        $this->db->where('code', '1301');
        $this->db->or_where('code', '1302');
        $this->db->order_by("timestamp", 'asc');
        $query = $this->db->get();
        $ret = array('new_warning'=>array(), 'withdraw_warning'=>array());
        $new_warning_num = 0;
        $withdraw_warning_num = 0;
        foreach ($query->result_array() as $row){
            if($row['code'] == '1301'){
                $ret['new_warning'][] = $this->extendRow($row);
                $new_warning_num++;
            }elseif($row['code'] == '1302'){
                $ret['withdraw_warning'][] = $this->extendRow($row);
                $withdraw_warning_num++;
            }
        }
//        echo $ret['new_warning'][0]['code'];
        $ret['new_warning_num'] = $new_warning_num;
        $ret['withdraw_warning_num'] = $withdraw_warning_num;
        return $ret;
    }
}