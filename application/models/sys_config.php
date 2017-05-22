<?php
class Sys_config extends CI_Model {
  
  public $_config_json;
  public $_config_str;

  public function __construct()
  {
    $this->load->helper('file');
    $string = read_file('./config.json');
    $this->_config_str = $string;
    $this->_config_json = json_decode($string);
  }
  
  /**
   * 获得配置文件的json对象
   *
   * @access public
   * @param 无
   * @return json object
   */
  public function get_cfg_obj()
  {
    return $this->_config_json;
  }
  
  /**
   * 获得配置文件的json字符串格式
   *
   * @access public
   * @param 无
   * @return string
   */
  public function get_cfg_str()
  {
    return $this->_config_str;
  }
  
  /**
   * 存储json对象到配置文件
   *
   * @access public
   * @param json object
   * @return 无
   */
  public function save_cfg_obj($obj)
  {
    $string = $this->get_pretty_json(json_encode($obj));
    if ( ! write_file('./config.json', $string)) {
      return false;
    } else {
       return true;
    }
  }

  /**
   * 存储json字符串到配置文件
   *
   * @access public
   * @param string
   * @return 无
   */
  public function save_cfg_str($json_str)
  {
    if ( ! write_file('./config.json', $json_str)) {
      return false;
    } else {
       return true;
    }
  }

  /**
   * 前端请求配置接口
   *
   * @access public
   * @param string
   * @return 无
   */
  public function get_init_str()
  {
    $init_str = "";
    $init_arr = array();
    //user
    $init_arr['user'] = array('username'=>'dzx','userid'=>1, 'usertype'=>1, 'dpt'=>0);
    //departments
    $this->load->model('dpt');
    $init_arr['dpt'] = $this->dpt->fetchAll();
    //sysconfig
    $init_arr['protocol'] = $this->_config_json->localSet->protocol;
    $init_arr['protocol'] = $this->_config_json->localSet->protocol;
    if ($init_arr['protocol'] == 'ospf') {
      $init_arr['as_num'] = $this->_config_json->localSet->asNum;
    }
    elseif ($init_arr['protocol'] == 'isis') {
      $init_arr['area_id'] = $this->_config_json->localSet->areaId;
    }  
    $init_arr['isHQ'] = $this->_config_json->localSet->isHQ;
    $init_arr['topN'] = $this->_config_json->localSet->topN;
    $init_arr['interval'] = $this->_config_json->localSet->interval;
    $init_arr['style_set'] = $this->_config_json->styleSet;
    //observe_ports
    $init_arr['observe_ports'] = $this->_config_json->localSet->observePorts;

    $init_str = json_encode($init_arr);
    return $init_str;
  }
  
  /**
   * 重新读取配置文件
   *
   * @access public
   * @param 无
   * @return 无
   */
  public function flush_cfg()
  {
    $this->load->helper('file');
    $string = read_file('./config.json');
    $this->_config_str = $string;
    $this->_config_json = json_decode($string);
  }

  /**
   * 重新读取配置文件
   *
   * @access public
   * @param 无
   * @return 无
   */
  public function get_pretty_json($json)
  {
      $tab = "  "; 
      $new_json = ""; 
      $indent_level = 0; 
      $in_string = false; 

      $json_obj = json_decode($json); 

      if($json_obj === false) 
          return false; 

      $json = json_encode($json_obj); 
      $len = strlen($json); 

      for($c = 0; $c < $len; $c++) 
      { 
          $char = $json[$c]; 
          switch($char) 
          { 
              case '{': 
              case '[': 
                  if(!$in_string) 
                  { 
                      $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1); 
                      $indent_level++; 
                  } 
                  else 
                  { 
                      $new_json .= $char; 
                  } 
                  break; 
              case '}': 
              case ']': 
                  if(!$in_string) 
                  { 
                      $indent_level--; 
                      $new_json .= "\n" . str_repeat($tab, $indent_level) . $char; 
                  } 
                  else 
                  { 
                      $new_json .= $char; 
                  } 
                  break; 
              case ',': 
                  if(!$in_string) 
                  { 
                      $new_json .= ",\n" . str_repeat($tab, $indent_level); 
                  } 
                  else 
                  { 
                      $new_json .= $char; 
                  } 
                  break; 
              case ':': 
                  if(!$in_string) 
                  { 
                      $new_json .= ": "; 
                  } 
                  else 
                  { 
                      $new_json .= $char; 
                  } 
                  break; 
              case '"': 
                  if($c > 0 && $json[$c-1] != '\\') 
                  { 
                      $in_string = !$in_string; 
                  } 
              default: 
                  $new_json .= $char; 
                  break;                    
          } 
      } 

      return $new_json;
  }

}