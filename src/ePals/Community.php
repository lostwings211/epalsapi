<?php
namespace ePals;

use \ePals\Base\Rest;
use \Exception;

class Community extends Rest {
   
    private $id; //uuid
    private $name;
    private $description;
    private $ssorealm;

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getSsorealm() {
        return $this->ssorealm;
    }
   
    public function setName($name) {
        $this->name = $name;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setSsorealm($ssorealm) {
        $this->ssorealm = $ssorealm;
    }
    
    function loadCommunityById($communityuuid) {
        if(!isset($communityuuid)) {
            throw new Exception("communityuuid parameter is empty");
        }
        $path = "/community/uuid/".rawurlencode($communityuuid);
        $community = parent::_getSISURL($path, NULL);
        $this->sisJSONToObject($community);
    }
    
    function loadCommunityByName($name) {
        if(!isset($name) || trim($name) === '') {
            throw new Exception("Community name parameter is empty");
        }
        $path = "/community/".rawurlencode($name);
        $community = parent::_getSISURL($path, NULL);      
        $this->sisJSONToObject($community);
    }
        
    function add() {
       if(!isset($this->name) || trim($this->name)==='') {
            throw new Exception("Community name parameter is empty");
       }
       $path = "community/create";
       $community = array (
           'Name' => $this->name,
           'Description' => $this->description,
           'SSORealm' => $this->ssorealm,
           );
       $response = parent::_postSISURL($path, null, json_encode($community));
       if(isset($response->Id)) {
           $this->id = $response->Id;
           return $response->Id;
       }
       else {
           $this->id = $response;
       }
       return $response;
    }
       
    function update() {
        if( !isset($this->id)) {
            throw new Exception ("Id not set. Please load community via constructor");
        }
        if(!isset($this->name) || trim($this->name)==='') {
              throw new Exception("Community name parameter is empty");
        }
        //Build the URL of the REST endpoint
        $getpath = "community/uuid/".  rawurlencode($this->id);  //Current bug in SIS requires extra .com
        $community = parent::_getSISURL($getpath, NULL);
        $community = $this->updateCommunity($community);
        $updatepath = "community/edit";
        $result = parent::_putSISURL($updatepath, null, json_encode($community));
        return $result;
    }
        
    function addTenant($tenantDomain) {
        if(!isset($this->id)) {
            throw  new Exception("Id not found. Please load Community via constructor!");
        }
        if(!isset($tenantDomain)) {
            throw  new Exception("Tenant Domain is empty.");
        }
        $tenant = new Tenant($tenantDomain);
        $path = "community/".rawurlencode($this->id)."/addTenant";
        $params = "tenantId=".rawurlencode($tenant->getDomain());
        $response = parent::_getSISURL($path, $params);
        return $response;
    }
   
    private function updateCommunity($community) {
        if(!is_null($this->id)) {
            $community->Id = $this->id;
        }
        if(!is_null($this->name)) {
            $community->Name = $this->name;
        }
        if(!is_null($this->description)) {
            $community->Description = $this->description;
        }
        if(!is_null($this->ssorealm)) {
           $community->SSORealm = $this->ssorealm;
           if(isset($community->ssorealm)) {
               $community->ssorealm = $this->ssorealm;
           }
        }
        if(isset($community->NodeId)) {
            unset($community->NodeId);
        }
        if(isset($community->NodeName)) {
            unset($community->NodeName);
        }
        return $community;
    }
         
    private function sisJSONToObject($communityJSON) {
        $this->id = $communityJSON->Id;
        if(isset($communityJSON->Name)) {
            $this->name = $communityJSON->Name;
        }
        if(isset($communityJSON->SSORealm)) {
            $this->ssorealm = $communityJSON->SSORealm;
        }
        if(!isset($communityJSON->SSORealm) && isset($communityJSON->ssorealm)) {
            $this->ssorealm = $communityJSON->ssorealm;
        }
        if(isset($communityJSON->Description)) {
            $this->description = $communityJSON->Description;
        }
    }
}
