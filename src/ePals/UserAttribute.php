<?php
namespace ePals;

use \ePals\Base\Record;
use \ePals\Base\User;
use \Exception;

class UserAttribute extends Record {
    
    public $id;
    protected $attributes;
    private $mode;
    private $sis_server;
    
    public function get_SIS_Server() {
        return $this->sis_server;
    }
    
    public function set_SIS_Server($sis_server) {
        $this->sis_server = $sis_server;
    }
    
    public function loadUserAttribute($user) {
        $u = new User();
        $u->set_SIS_Server($this->get_SIS_Server());
        $u->loadUser($user);
        if($u->getAccount()) {
            parent::__construct();
            $this->id = $user;
            $res = parent::get();
        }
        else {
            throw new Exception("User $user does not exist!");
        }
    }
   
    public function add($key,$value) {
        
        if ((empty($this->id)) || is_null($this->id)) {
            throw new Exception("Id can't be null, call loadUserAttribute() method to load the user first");
        }
        if ((empty($key)) || is_null($key)) {
            throw new Exception("Key can't be null");
        }
        $tmp = array($key => $value);
        if (is_null($this->attributes)) {
            $this->attributes = $tmp;
            $res = parent::add();
        } else {
            $this->attributes = array_merge($this->attributes,$tmp);
            $res = parent::update();
        }
        return $res;
    }
    
    public function getAll() {
        if ((empty($this->id)) || is_null($this->id)) {
            throw new Exception("Id can't be null, call loadUserAttribute() method to load the user first");
        }
        return $this->attributes;
    }
    
    public function get($key) {
        if ((empty($this->id)) || is_null($this->id)) {
            throw new Exception("Id can't be null, call loadUserAttribute() method to load the user first");
        }
        if ((empty($key)) || is_null($key)) {
            throw new Exception("Key can't be run");
        }
        if (!array_key_exists($key, $this->attributes)) {
            throw new Exception ("Attribute $key does not exist!");
        }
        return $this->attributes[$key];
    }
}
