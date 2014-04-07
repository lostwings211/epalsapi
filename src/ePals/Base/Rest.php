<?php
namespace ePals\Base;

use \Exception;

class Rest {
    
    private $sis_server;
    private $pm_server;
    
    public function get_SIS_Server() {
        return $this->sis_server;
    }
    
    public function get_PM_Server() {
        return $this->pm_server;
    }
    
    public function set_SIS_Server($sis_server) {
        $this->sis_server = $sis_server;
    }
    
    public function set_PM_Server($pm_server) {
        $this->pm_server = $pm_server;
    }
    
    public function _getPMURL($path, $params) {
        if(!isset($this->pm_server) || trim($this->pm_server)==='') {
            throw new Exception("pm_server is not set!");
        }
        $res = null;
        try {
            $res = $this->CurlURL("GET", $this->pm_server, $path, $params);
        } 
        catch (Exception $e) {
            error_log("Couldn't getURL: $e)");
        }
        return $res;
    }
        
    public function _getSISURL($path, $params) {
        if(!isset($this->sis_server) || trim($this->sis_server)==='') {
            throw new Exception("sis_server is not set!");
        }
        try {
            return $this->CurlURL("GET", $this->sis_server, $path, $params);
        } 
        catch (Exception $e) {
            error_log("In _getSISURL: $e");
            throw new Exception($e->getMessage());
        }
    }
	
    public function _postSISURL($path, $params, $postdata = false) {
        if(!isset($this->sis_server) || trim($this->sis_server)==='') {
            throw new Exception("sis_server is not set!");
        }
        return $this->CurlURL("POST", $this->sis_server, $path, $params, $postdata);
    }
	
    public function _putSISURL($path, $params, $postdata) {
        if(!isset($this->sis_server) || trim($this->sis_server)==='') {
            throw new Exception("sis_server is not set!");
        }
        return $this->CurlURL("PUT", $this->sis_server, $path, $params, $postdata);
    }
    
    private function CurlURL($ops, $server, $path, $params, $postdata=null) {
        $url = $server . $path . "?" . (strlen($params) == 0 ? "" : $params . "&") . "format=json";
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
        curl_setopt($ch, CURLOPT_URL, $url);
        
        if($ops === "POST") {
            if (isset($postdata)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);   
            }
        }
        if($ops === "PUT") {
            if (isset($postdata)) {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);   
            }
        }
        $result = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_status == "200" or $http_status == "201") {
            
            $jsonobj =  json_decode($result);
            if($jsonobj == null) { 
                return $result;
            }
            else { 
                return $jsonobj;
            }
        } 
        else {
            throw new Exception($result);
        }
    }
}

