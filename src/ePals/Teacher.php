<?php
namespace ePals;

use \ePals\Base\User;
use \Exception;

class Teacher extends User {
    
    public function setRoles($role) {
        throw new Exception('You can\'t set Role in Teacher Class. Role is already Set to Teacher');
    }

    public function setRawDob($rawDob) {
        throw new Exception('You can\'t set RawDob for Teacher');
    }

    public function setGrade($grade) {
        throw new Exception('You can\'t set Grade for Teacher');
    }

    public function getUserType() {
       return 'Educator';
    }
     
    function addStudent($studentAccountId) {
        if(!empty($studentAccountId)) {
            $account = $this->getAccount();
            //Build the URL of the REST endpoint
            if (!empty($account)) {
                 $json = array(
                    'TenantExternalId' => $this->getTenantDomain(),
                    'StudentAccountId' => $studentAccountId,
                    'ModeratorAccountId' => $this->getAccount()
                );
                //Build the URL of the REST endpoint
                $path = "user/setModerator";
                //Make the REST call
                $request = json_encode($json);
                return parent::_postSISURL($path, null, $request);
            }
        }
    }

    function removeStudent($studentAccountId) {
        if(!empty($studentAccountId)) {
            $account = $this->getAccount();
            //Build the URL of the REST endpoint
            if (!empty($account)) {
                 $json = array(
                    'TenantExternalId' => $this->getTenantDomain(),
                    'StudentAccountId' => $studentAccountId,
                    'ModeratorAccountId' => $this->getAccount()
                );
                //Build the URL of the REST endpoint
                $path = "user/removeModerator";
                //Make the REST call
                $request = json_encode($json);
                return parent::_postSISURL($path, null, $request);
            }
        }
    }
    
    function add($schoolId = NULL) {
        if(empty($schoolId)) {
            $schoolId = "defaultschool";
        }
            
        parent::setRoles(array('Educator'));
        $result = parent::add();
        
        try {
            $school = new School();
            $school->set_SIS_Server($this->get_SIS_Server());
            $school->loadSchool($this->getTenantDomain(), $schoolId);
            if ($school->getSchoolId() === $schoolId) {
                $school->addUserToSchool($this->getAccount(), 'Teacher');
            }
        }
        catch (Exception $e) {
            
        }
        return $result;
    }
}
