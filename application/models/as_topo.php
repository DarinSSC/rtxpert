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

//    public function construct($protocol='ospf', $ishq=0)
//    {
//        $this->_protocol = $protocol;
//        $this->_ishq = $ishq;
//    }
    public function extractFromRow($row, $as_set=True, $as_num=-1){
        $ret_data_row = array();
        if($as_set){//参数中有AS
            $ret_data_row['AS'] = $as_num;
        }else{//没有AS参数
            if($row['asPath']==''){
                $ret_data_row['AS'] = $row['n_asNum'];
            }else{
                $ret_data_row['AS'] = $row['asPath'][strlen($row['asPath'])-1];
            }
        }
        $ret_data_row['prefix'] = $row['networkNum'].'/'.$row['prefixLen'];
        $ret_data_row['create_time'] = $row['create_time'];
        $ret_data_row['end_time'] = $row['end_time'];
        return $ret_data_row;
    }

    public function getAsTopo($options = array()){
        $this->db->select('*');
        $this->db->from('hz_ospf_link_info');
        //对于AS号的筛选，不能直接用以下的方式进行。
        //因为这里的origin，与之前BGPmon的报文中origin的意义不同；这里AS的筛选只能通过as_path的最后一位进行
//        if(isset($options['as_num'])){
//            $this->db->where('origin', $options['as_num']);
//        }
        //这里对时间的筛选，起始是取了截止时间小于create_time或者开始时间大于start_time的互补集合
        if(isset($options['start_time'])){//选择了初始时间
            $this->db->where('end_time >=', $options['start_time']);
        }
        if(isset($options['end_time'])){//选择了截止时间
            $this->db->where('create_time <=', $options['end_time']);
        }
        //按照networkNum和prefixLen进行分组,同一个前缀只获取一次
//        $this->db->group_by(array("origin", "networkNum", "prefixLen"));
        $this->db->order_by("create_time", "asc");
        //获得结果(还没有使用AS号进行筛选)
        $query = $this->db->get();
//        echo $ret['total'];
        //获得内容
        //这里看之前的代码，说要访问两次数据库，但我没看出来为什么
        $ret['data'] = array();
        $ret['as'] = array();
        foreach ($query->result_array() as $row){
            //首先获取所有的as号，用来显示AS的下拉菜单
            if($row['asPath']==''){
                //这里的参数全部都是string类型
                //满足条件1
//                $ret['as'][] = [$row['n_asNum'] => $row['n_asNum']];
                $ret['as'][] = $row['n_asNum'];
                continue;
            }elseif($row['asPath']!=='') {
                //满足条件2
//                $ret_data_row = $this->extractFromRow($row, $as_set=True, $as_num=$options['as_num']);
//                $ret['as'][] = [$row['asPath'][strlen($row['asPath'])-1] => $row['asPath'][strlen($row['asPath'])-1]];
                $ret['as'][] = $row['asPath'][strlen($row['asPath'])-1];
                continue;
            }
            if(isset($options['as_num'])){//对AS号进行筛选
                //对于给定的AS，存在两种情况
                //1. asPath为空 => 数据库中的iBGP报文，且n_asNum=$options['as_num']
                //2. asPath不为空 => 跨AS, 且asPath的最后一个AS=$options['as_num']
                if($row['asPath']=='' && $row['n_asNum']==$options['as_num']){
                    //这里的参数全部都是string类型
                    //满足条件1
                    $ret_data_row = $this->extractFromRow($row, $as_set=True, $as_num=$options['as_num']);
                    $ret['data'][] = $ret_data_row;
                    continue;
                }
                if($row['asPath']!=='' && $row['asPath'][strlen($row['asPath'])-1]==$options['as_num']) {
                    //满足条件2
                    $ret_data_row = $this->extractFromRow($row, $as_set=True, $as_num=$options['as_num']);
                    $ret['data'][] = $ret_data_row;
                    continue;
                }
            }else{
                $ret_data_row = $this->extractFromRow($row, $as_set=False);
                $ret['data'][] = $ret_data_row;
            }
        }
        //获取通过筛选过后得到的总数
        $ret['as'] = array_unique($ret['as']);
        $ret['total'] = count($ret['data']);
        return $ret;
    }
}