<?php
namespace ePals;

use \ePals\Base\Record;
use \ePals\Base\User;
use \Exception;

class UserPreference extends Record {
    
    public $id;
    protected $preferences;
    private $mode;
    
    private $sis_server;
    
    public function get_SIS_Server() {
        return $this->sis_server;
    }
    
    public function set_SIS_Server($sis_server) {
        $this->sis_server = $sis_server;
    }

    public function loadUserPreference($user) {
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
        if ((empty($key)) || is_null($key)) {
            throw new Exception("Key can't be run");
        }
        $tmp = array($key => $value);
         if (is_null($this->preferences)) {
            $this->preferences = $tmp;
            $res = parent::add();
        } else {
            $this->preferences = array_merge($this->preferences,$tmp);
            $res = parent::update();
        }
        return $res;
    }
    
    function getAll() {
        return $this->preferences;
    }
    
    function get($key) {
        if ((empty($key)) || is_null($key)) {
            throw new Exception("Key can't be run");
        }
        return $this->preferences[$key];
    }
}

