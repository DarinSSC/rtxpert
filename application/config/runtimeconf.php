<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| user_type mapping
|--------------------------------------------------------------------------
|
| 数据库中存储的用户角色的映射
| 
|
*/
$config['runtimeconf']['user_type'] = array('1'=>'admin', '2'=>'user');


/*
|--------------------------------------------------------------------------
| ospf router_type mapping
|--------------------------------------------------------------------------
|
| ospf 路由器类型的映射
| 
|
*/
$config['runtimeconf']['ospf_router_type'] = array('1' => 'normal', '2'=>'ABR', '3'=>'ASBR');


/*
|--------------------------------------------------------------------------
| ospf link_type mapping
|--------------------------------------------------------------------------
|
| ospf 链路类型的映射
| 
|
*/
$config['runtimeconf']['ospf_link_type'] = array('1'=>'point-to-point', '2'=>'transit', '3'=>'stub', '4'=>'virtual link', '11'=>'border link');


/*
|--------------------------------------------------------------------------
| isis router_type mapping
|--------------------------------------------------------------------------
|
| isis 路由器类型的映射
| 
|
*/
$config['runtimeconf']['isis_router_type'] = array('1'=>'L1', '2'=>'L2', '3'=>'L1/L2');


/*
|--------------------------------------------------------------------------
| isis link_type mapping
|--------------------------------------------------------------------------
|
| isis 链路类型的映射
| 
|
*/
$config['runtimeconf']['isis_link_type'] = array();


/*
|--------------------------------------------------------------------------
| inc_topo_type mapping
|--------------------------------------------------------------------------
|
| 增量拓扑变化事件的映射
| 
|
*/
$config['runtimeconf']['inc_topo_type'] = array('1'=>'link_add', '2'=>'link_miss');
//$config['runtimeconf']['inc_topo_type'] = array('add_router' => 1, 'update_router' => 2, 'delete_router' => 3, 'add_link' => 11, 'update_link' => 12, 'delete_link' => 13);

/*
|--------------------------------------------------------------------------
| warning_class mapping
|--------------------------------------------------------------------------
|
| 告警类别的映射
| 
|
*/
$config['runtimeconf']['warning_class'] = array('1'=>'route', '2'=>'traffic');

/*
|--------------------------------------------------------------------------
| route_warning_type mapping
|--------------------------------------------------------------------------
|
| 路由告警类别的映射
| 
|
*/
$config['runtimeconf']['route_warning_type'] = array('1'=>'link_add', '2'=>'link_miss');

/*
|--------------------------------------------------------------------------
| SilverLight settings
|--------------------------------------------------------------------------
|
| 数据库中存储的用户角色的映射
| 
|
*/
//$config['runtimeconf']['silverlight'] = array();


/* End of file runtimeconf.php */
/* Location: ./application/config/config/runtimeconf.php */
