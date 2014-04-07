<?php
namespace ePals;

use \ePals\Base\User;
use \Exception;

class Parental extends User {
    
    public function setRoles($role) {
        throw new Exception('You cant set Role in this Class. Role is already Set to Parent');
    }

    public function setRawDob($rawDob) {
        throw new Exception('You can\'t set RawDob for Teacher');
    }

    public function setGrade($grade) {
        throw new Exception('You can\'t set Grade for Teacher');
    }

    public function getUserType() {
        return 'Parent';
    }
     
    function addStudent($studentAccountId) {
        $parentAccount = $this->getAccount();
        if(empty($studentAccountId) || empty($parentAccount)) {
            throw new Exception("StudentAccountId or ParentAccountID is not set");
        }

        $student = new Student($studentAccountId);
        return $student->addParent($this->getAccount());
    }
        
    function removeStudent($studentAccountId) {
        $parentAccount = $this->getAccount();
        if(empty($studentAccountId) || empty($parentAccount)) {
            throw new Exception("StudentAccountId or ParentAccountID is not set");
        }
        $student = new Student($studentAccountId);
        return $student->removeParent($this->getAccount());
    }
       
    function add($schoolId = NULL) {
        if(empty($schoolId)) {
            $schoolId = "defaultschool";
        }
            
        parent::setRoles(array('Parent'));
        $result =  parent::add();
        
        try {
            $school = new School();
            $school->set_SIS_Server($this->get_SIS_Server());
            $school->loadSchool($this->getTenantDomain(), $schoolId);
            if ($school->getSchoolId() === $schoolId) {
                $school->addUserToSchool($this->getAccount(), $this->getUserType());
            }
        }
        catch (Exception $ex) {
            
        }
        return $result;
    }
}

