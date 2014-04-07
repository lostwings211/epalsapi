<?php
namespace ePals;

use \ePals\Base\Record;

class Project extends Record {

    protected $projectname;
    protected $description;
    protected $metadata;
    
    public function getProjectname() {
        return $this->projectname;
    }
    
    public function getDescription() {
        return $this->description;
    }
    
    public function getMetadata() {
        return $this->metadata;
    }
    
    public function setProjectname($projectname) {
        $this->projectname = $projectname;
    }
    
    public function setDescription($description) {
        $this->description = $description;
    }
            
    public function setMetadata($metadata) {
        if(is_array($metadata)) {
            $this->metadata = $metadata;
        }
        else {
            throw new Exception("metadata variable needs to be in the form of an PHP key/value pair Array!");
        } 
    } 
    
    function load($id) {
        if(empty($id)) {
            throw new Exception("id variable cannot be left empty in the load() method!");
            return ;
        }
        $this->id = $id;
        parent::get();
        if(empty($this->projectname)){
            $this->id = null;
        }
    }
    
    public function add() {
        if(empty($this->projectname)) {
            throw new Exception("projectname field of the object cannot be left empty!");
            return;
        }
        if(!empty($this->id)) {
            throw new Exception("id field of the object has already been tampered before invoke add() method!");
            return;
        }
        parent::add();
    }
    
    public function addMetadata($key, $value) {
        if (empty($key)) {
            throw new Exception("key varialbe cannot be left empty!");
            return; 
        }
        
        $tmp = array($key => $value);
        if (is_null($this->metadata)) {
            $this->metadata = $tmp;
        }
        else {
            $this->metadata = array_merge($this->metadata,$tmp);
        }
        $this->update();
    }
    
    public function update() {
        if(empty($this->id)) {
            throw new Exception ("id field of the object cannot be left empyt for update() method");
            return;
        }
        parent::update();
    }   
}


