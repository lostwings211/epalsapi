<?php
namespace ePals;

use \ePals\Base\User;
use \Exception;

class Student extends User {

    public function setRoles($role) {
        throw new Exception('You cant set Role in Student Class. Role is already Set to Student');
    }

    public function getUserType() {
        return 'Student';
    }
     
    function addModerator($moderatorAccountId) {
        if(!empty($moderatorAccountId)) {
            $account = $this->getAccount();
            //Build the URL of the REST endpoint
            if (!empty($account)) {
                 $json = array(
                    'TenantExternalId' => $this->getTenantDomain(),
                    'StudentAccountId' => $this->getAccount(),
                    'ModeratorAccountId' => $moderatorAccountId
                );
                //Build the URL of the REST endpoint
                $path = "user/setModerator";
                //Make the REST call
                $request = json_encode($json);
                return parent::_postSISURL($path, null, $request);
            }
        }
    }
       
    function removeModerator($moderatorAccountId) {
        if(!empty($moderatorAccountId)) {
            $account = $this->getAccount();

            //Build the URL of the REST endpoint
            if (isset($account)) {
                $json = array(
                    'TenantExternalId' => $this->getTenantDomain(),
                    'StudentAccountId' => $this->getAccount(),
                    'ModeratorAccountId' => $moderatorAccountId
                );

                //Build the URL of the REST endpoint
                $path = "user/removeModerator";
                //Make the REST call
                $request = json_encode($json);
                return parent::_postSISURL($path, null, $request);
            }
        }
    }
       
    function addMentor($mentorAccountId) {
        if(!empty($mentorAccountId)){
            $mentor = new User($mentorAccountId);
            $mentorInternalID = $mentor->getInternalId();
                
            if(empty($mentorInternalID)) {
                throw new Exception("Unable to find mentor: $mentorAccountId"); 
            }
            $account = $this->getAccount();
                
            //Build the URL of the REST endpoint
            if (!empty($account)) {
                $json = array(
                    'TenantExternalId' => $this->getTenantDomain(),
                    'StudentAccountId' => $this->getAccount(),
                    'MentorExternalId' => $mentor->getUserId()
                );

                //Build the URL of the REST endpoint
                $path = "user/setMentor";
                //Make the REST call
                $request = json_encode($json);
                return parent::_postSISURL($path, null, $request);
            }
        }
    }
  
    function removeMentor($mentorAccountId) {
        if(!empty($mentorAccountId)){
            $mentor = new User($mentorAccountId);
            $mentorInternalId = $mentor->getInternalId();

            if(empty($mentorInternalId)) {
                throw new Exception("Unable to find mentor: $mentorAccountId"); 
            }
            $account = $this->getAccount();
            //Build the URL of the REST endpoint
            if (isset($account)) {
                $json = array(
                    'TenantExternalId' => $this->getTenantDomain(),
                    'StudentAccountId' => $this->getAccount(),
                    'MentorExternalId' => $mentor->getUserId()
                );
                //Build the URL of the REST endpoint
                $path = "user/removeMentor";
                //Make the REST call
                $request = json_encode($json);
                return parent::_postSISURL($path, null, $request);
            }
        }
    }
        
    function addParent($parentAccountId) {
        if(!empty($parentAccountId)) {
            //Build the URL of the REST endpoint
            $path = "user/addParent";
            //Make the REST call
            $user = array(
             'ParentAccountId' => $parentAccountId,
             'StudentAccountId' => $this->getAccount()
            );
            return parent::_postSISURL($path, null, json_encode($user));
        }
    }

    function removeParent($parentAccountId) {
        //Build the URL of the REST endpoint
        $path = "user/removeParent";
        //Make the REST call
        $user = array(
             'ParentAccountId' => $parentAccountId,
             'StudentAccountId' => $this->getAccount()
        );
        return parent::_postSISURL($path, null, json_encode($user));
    }
     
    function add($schoolId = NULL) {
        if(empty($schoolId)) {
            $schoolId = "defaultschool";
        }
            
        parent::setRoles(array('Student'));
        $result = parent::add();
        try {
            $school = new School();
            $school->set_SIS_Server($this->get_SIS_Server());
            $school->loadSchool($this->getTenantDomain(), $schoolId);
            if ($school->getSchoolId() === $schoolId) {
                $school->addUserToSchool($this->getAccount(), $this->getUserType());
            }
        }
        catch (Exception $e) {
            
        }
        return $result;
    }
}

