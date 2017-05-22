<?php
error_reporting(0);
class Flow extends CI_Model {
    
    private $_timeout = 2000;
    private $_n = 10;
    private $_service_port = 0;
    private $_address = "127.0.0.1";
    private $_protocol = "ospf";

    public function __construct()
    {
        parent::__construct(); 

        $cfg_obj = $this->sys_config->get_cfg_obj();
        $this->_service_port = $this->_cfg_obj->localSet->localFlowQueryPort;
        $this->_address = $this->_cfg_obj->localSet->localIp;
        $this->_protocol = $this->_cfg_obj->localSet->protocol;
    }

    public function local_query($query_str)
    {
        $retArr = array("codeStatus" => 0,
                    "errorMsg" => "",
                    "msg" => "");
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            $retArr['codeStatus'] = -1;
            $retArr['errorMsg'] = "连接本地流查询服务：创建socket失败";
            socket_close($socket);
            return json_encode($retArr);
        }
        $result = socket_connect($socket, $this->_address, $this->_service_port);
        if ($result === false) {
            $retArr['codeStatus'] = -2;
            $retArr['errorMsg'] = "连接本地流查询服务：socket连接失败";
            socket_close($socket);
            return json_encode($retArr);
        }
        $query_str .= "\n";
        $wres = socket_write($socket, $query_str, strlen($query_str));
        if ($wres === false) {
            $retArr['codeStatus'] = -3;
            $retArr['errorMsg'] = "连接本地流查询服务：提交查询失败";
            socket_close($socket);
            return json_encode($retArr);
        }
        $return_str = "";
        while ($out = socket_read($socket, 8192)) {
            $return_str .= $out;
        }
        if (is_null($return_str) || empty($return_str)) {
            $retArr['codeStatus'] = -4;
            $retArr['errorMsg'] = "查询未能成功返回结果";
            socket_close($socket);
            return json_encode($retArr);
        }
        $res = json_decode($return_str);
        if (is_null($res) || empty($res)) {
            $retArr['codeStatus'] = -5;
            $retArr['errorMsg'] = "查询返回结果空";
            socket_close($socket);
            return json_encode($retArr);
        }
        $retArr['result'] = $res;
        socket_close($socket);
        return json_encode($retArr);
    }

    ///////////////////////////////////////全网查询///////////////////////////////////////////////
    //
    public function cross_domain_query($domain, $query_str)
    {
        //获取查询所在AS的查询url
        $remoteUrl = $this->get_remote_domain_url($domain, $query_str);
        if ($remoteUrl == "") {
            return;
        } else {
            $res = $this->curl_remote_domain($remoteUrl);
            return $res;
        }
    }

    //根据domain号和查询参数拼接curl的查询url
    private function get_remote_domain_url($domain, $query_str)
    {
        $url = "";
        $base_url = $this->sys_config->get_cfg_obj()->localSet->baseUrl;
        if ($this->_protocol == "ospf") {
            $asSet =  $this->sys_config->get_cfg_obj()->HQSet->asSet;
            foreach ($asSet as $as) {
                if ($as->asNum == $domain) { //找到该as
                    //拼接查询url
                    $url = "http://".$as->asIp.$base_url."/interface_common/flow_query"."?ishq=0&query_str=".$query_str;
                    break;
                }
            }
        } elseif ($this->protocol == "isis") {
            $areaSet = $this->sys_config->HQSet->areaSet;
            foreach ($areaSet as $area) {
                if ($area->areaId == $domain) { //找到该area
                    //拼接查询url
                    $url = "http://".$area->areaIp.$base_url."/interface_common/flow_query"."?ishq=0&query_str=".$query_str;
                    break;
                }
            }
        }
        return $url;
    }

    //curl查询远程as的本地流查询请求
    private function curl_remote_domain($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->_timeout);//设置超时时间
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_POST, 1);    //处理post数据
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_query_data);
        curl_setopt($ch, CURLOPT_HEADER, 0);    //这里不要header，加块效率
        $output = curl_exec($ch);
        if ($output === FALSE) {
            $output = "error";
        }
        curl_close($ch);
        return $output;
    }

}
