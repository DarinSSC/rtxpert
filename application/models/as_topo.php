<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/12
 * Time: 11:11
 */
class as_topo extends CI_Model
{
    public function __construct()
    {
        $this->load->database();
    }

    public function itemAlreadyExist($src, $dest, $array){
        $index = 0;
        foreach ($array as $item){
            if($item['src_as'] == $src && $item['dest_as'] == $dest){
                return $index;
            }
            $index++;
        }
        return -1;
    }

    public function getAsTopo($options = array()){
        $this->db->select('*');
        $this->db->from('hz_ospf_link_info');

        $this->db->where('end_time', '9999999999');//筛选出现在仍然有效的记录； 这里有效，指的就是end_time为9999999999
        $this->db->where('n_router_id !=', '');//数据库中存在邻居router_id为空的记录，构造i拓扑时不需要这些记录
        $this->db->order_by("create_time", "desc");
        $query = $this->db->get();
        //获得内容
        $ret['as_list'] = array();
        $ret['inter_link'] = array();
        $ret['inner_link'] = array();
        foreach ($query->result_array() as $row){
            //首先判断在$ret['as_list']中是否已经存在这个AS
            if(!in_array($row['as_num'], $ret['as_list'], true)){
                $ret['as_list'][] = $row['as_num'];
                $ret['inner_link'][$row['as_num']] = array();
            }
            if(!in_array($row['n_as_num'], $ret['as_list'], true)){
                $ret['as_list'][] = $row['n_as_num'];
                $ret['inner_link'][$row['n_as_num']] = array();
            }
            //通过上面的代码，保证了这条记录出现的as一定在$ret['as_list']和$ret['inner_link']中有记录
            if($row['link_type'] == 11){//域间的Link
                $index = $this->itemAlreadyExist($row['as_num'], $row['n_as_num'], $ret['inter_link']);
//                echo $index;
//                echo 'zzzzzzzzzzzzzz';
                if($index > -1){//已经存在
                    $ret['inter_link'][$index]['links'][] = array('src'=>$row['router_id'], 'dest'=>$row['n_router_id']);
                }else{
                    $ret['inter_link'][] = array('src_as'=>$row['as_num'], 'dest_as'=>$row['n_as_num'], 'links'=>array(array('src'=>$row['router_id'], 'dest'=>$row['n_router_id'])));
                }
//                if(!in_array(array('src' => $row['as_num'], 'dest' => $row['n_as_num']), $ret['inter_link'], true)){
//                    $ret['inter_link'][] = array(array('src' => $row['as_num'], 'dest' => $row['n_as_num']) => array());
//                }
//                $ret['inter_link'][] = array('src' => $row['as_num'], 'dest' => $row['n_as_num']);
            }else{//域内的link
                $ret['inner_link'][$row['as_num']][] = array('src' => $row['router_id'], 'dest' => $row['n_router_id']);
            }
        }
        //获取通过筛选过后得到的总数
//        ret['as'] = array_unique($ret['as']);
//        $ret['a'] = count($ret['data']);
        return $ret;
    }
}