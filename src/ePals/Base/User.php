<?php
namespace ePals\Base;

use \ePals\Base\Rest;
use \ePals\Utility\Utils;
use \Exception;

class User extends Rest {
	
    private $account; //required: user account id in format [username]@[tenant_Domain]
    private $ePalsEmail; // required: user email in format [username]@[tenant_EmailDomain]
    private $externalEmail; // user external email address ex: nsyed@mac.com
    private $userId; // user external id
    private $firstName; //required: user first or given name
    private $grade; // required: grade in case of student
    private $lastName; //required: user last or sur name
    private $rawDob; // student date of birth in format yyyymmdd ex: 19960101
    private $roles; //req - (will retired) use role in system. possible values are 1. Student 2. Educator 3. DistrictAdmin 4. Parent
    private $tenantDomain; // tenant domain 
    private $internalId; // internal UUID
    private $disabled = false; 
    private $userMetaData =''; // exteral field to hold extended data
    private $encryptedPasword; // user encrypted password
    private $password;

    public function getAccount() {
        return $this->account;
    }

    public function setAccount($account) {
        if(!Utils::check_email_address($account)) {
            throw new Exception("Please set Account ID in email format: username@domain.com");
        }
        $this->account = $account;
    }
        
    public function getEPalsEmail() {
        return $this->ePalsEmail;
    }
        
    public function setEPalsEmail($ePalsEmail) {
        if(!Utils::check_email_address($ePalsEmail)) {
                throw new Exception("Please use ePalsEmail in email format: username@emaildomain.com");
        }
        $this->ePalsEmail = $ePalsEmail;
    }

    public function getExternalEmail() {
        return $this->externalEmail;
    }
        
    public function setExternalEmail($externalEmail) {
        if(!Utils::check_email_address($externalEmail)) {
            throw new Exception("Please use externalEmail in email format username@externalemaildomain.com");
        }
        $this->externalEmail = $externalEmail;
    }
        
    public function getUserId() {
        return $this->userId;
    }

    public function setUserId($userId) {
        $this->userId = $userId;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    public function getGrade() {
        return $this->grade;
    }

        
    public function setGrade($grade) {
        $this->grade = $grade;
    }
    
    public function getLastName() {
        return $this->lastName;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    public function getRawDob() {
        return $this->rawDob;
    }

    public function setRawDob($rawDob) {
        $this->rawDob = $rawDob;
    }

    public function getRoles() {
        return $this->roles;
    }
        
    public function setRoles($role) {
        $this->roles = $role;
    }
    
    public function getTenantDomain() {
        if(!empty($this->account) && strrpos($this->account, '@') > 0){
            return substr(strrchr($this->account, "@"), 1);
        }
        return $this->tenantDomain;
    }

    public function getInternalId() {
        return $this->internalId;
    }

    public function isDeleted() {
        return $this->disabled;
    }

    public function getUserMetaData() {
        return $this->userMetaData;
    }

    public function setUserMetaData($userMetaData) {
        $this->userMetaData = $userMetaData;
    }

    public function getEncryptedPasword() {
        return $this->encryptedPasword;
    }

    public function setPassword($password) {
        $this->password = $password;
    }
     
    function verifyPassword($verifypassword) {      
        if(empty($this->account)) {
            throw new Exception("Account property is empty. Please load user!");
        }
        if(empty($verifypassword)) {
            throw new Exception("Password provided is empty.");
        }
        $path = "/accessmanager/validateUserPassword";
        $params = "userid=".rawurlencode($this->account)."&password=".rawurlencode($verifypassword);
        $result = parent::_getPMURL($path, $params);
        return $result->validateUserPasswordModule[0]->UserPasswordCheck;
    }
    
    public static function userExists($email) {
        if(empty($email)){
            throw new Exception('email provided is empty!');
        } 
        $r = new Rest();
        $res = FALSE;
        $path = "/accessmanager/getUser";
        $params = "email=".rawurlencode($email);
        try {
            $user = $r->_getPMURL($path, $params);
        } 
        catch (Exception $e) {
            error_log("userExists: $e");
            return FALSE;
        }
        if (!empty($user->getUser[0]->user->accountId)) {
            $res = TRUE;
        }
        return $res;
    }

    function loadUser($account){
        if(empty($account)) {
           throw new Exception("Account provided is empty");
        }
        $usr = null;
        if(!Utils::check_email_address($account)) {
            throw new Exception("Please provide account in email format: username@domain.com");
        }
        $path = "user/account/".rawurlencode($account);
        try {
            $usr = parent::_getSISURL($path, NULL);
        } catch (Exception $e) {
            error_log("loadUser: $e");
            throw new Exception($e->getMessage());
        }
        $this->sisJSONToObject($usr);
    }
    
    function update(){
        if( !isset($this->roles) || empty($this->roles) || !isset($this->roles[0]) || empty($this->roles[0]))
              throw new Exception ("User Role is not set!");

        if(!isset($this->account))
              throw  new Exception("Account is not set. Please load Object via Constructor");
           
        //Build the URL of the REST endpoint
        $getpath = "user/account/" . rawurlencode($this->getAccount());  //Current bug in SIS requires extra .com
        $user = parent::_getSISURL($getpath, NULL);
        $user = $this->updateUser($user);

        //Build the URL of the REST endpoint
        $tmpRoles = $this->getRoles();
        $updatepath = "user/" . rawurlencode($tmpRoles[0]) . "/edit";
        $result = parent::_postSISURL($updatepath, null, json_encode($user));

        if($result->NodeId > 0 && !empty($this->password))
            $this->updatePassword ($this->password);

        return $result;
    }
 
    function delete(){
        if(!isset($this->account))
            throw  new Exception("Account is not set. Please load Object via Constructor");
            
        $user = new User($this->account);
        $user->disabled = true;
        $result =  $user->update();
        $this->disabled = true;
        return $result;
    }

    function retrive(){
        if(!isset($this->account))
            throw  new Exception("Account is not set. Please load Object via Constructor");
           
        $user = new User($this->account);
        $user->disabled = false;
        $result =  $user->update();
        $this->disabled = false;
        return $result;
    }
       
    function add() {
        if( !isset($this->roles) || empty($this->roles) || !isset($this->roles[0]) || empty($this->roles[0]) )
            throw new Exception ("User Role is not set!");
        if(!isset($this->account))
            throw  new Exception("Account is not set");
        if(!isset($this->password))
            throw  new Exception("Password is not set");
        if(!isset($this->firstName))
            throw  new Exception("FirstName is not set");
        if(!isset($this->lastName))
            throw  new Exception("LastName is not set");
            
        $path = "user/" . rawurlencode($this->roles[0]) . "/create";
        $userArray = array (
            'ExternalId' => $this->userId,
            'FirstName' => $this->firstName,
            'LastName' => $this->lastName,
            'Rawdob' => $this->rawDob,
            'ExternalEmail' => $this->externalEmail,
            'EPalsEmail' => $this->ePalsEmail,
            'Account' => $this->account,
            'Grade' => $this->grade,
            'Roles' => $this->roles,
            'Password' => $this->password,
            'Disabled' => $this->disabled,
            'OptionsString' => $this->userMetaData

            );
        $response = parent::_postSISURL($path, null, json_encode($userArray));
        return $response;
    }
      
    public function updatePassword($newpassword) {
        if(!isset($this->account))
            throw new Exception ("User AccountID not set");
        if(!isset($newpassword))
            throw new Exception ("NewPassword not provided");
        
        $path = "user/" . rawurlencode($this->account) . "/setPassword";
        $response = parent::_putSISURL($path, null, $newpassword);
        if($response == 'Success')
            $this->setPassword ($newpassword);
        return $response;
    }
       
    function getGroups() {
        if(!isset($this->account))
            throw new Exception("AccountId not set!");
        $path = "/accessmanager/getGroups";
        $param = "email=".rawurlencode($this->ePalsEmail);
        $result = parent::_getPMURL($path, $param);
        $groups = $result->getGroupsModule[0]->Groups;
        return $groups;
    }
 
    function getTeacherGroups() {
        if(!isset($this->account)) {
            throw new Exception("AccountId not set!");
        }
        $path = "/accessmanager/getClasses";
        $param = "account=".rawurlencode($this->account);
        $sections = parent::_getPMURL($path, $param);
        $classes = $sections->getClassesModule;
        $teachergroups = array();
        foreach($classes as $cls){
           array_push($teachergroups, $cls->Object->{'course.ExternalId'});
        }
        return $teachergroups;
    }
         
    private function updateUser($user)
    {
        if(!is_null($this->account))
            $user->Account = $this->getAccount();

       if(!is_null($this->ePalsEmail))
            $user->EPalsEmail = $this->getEPalsEmail();

       if(!is_null($this->userId))
            $user->ExternalId = $this->getUserId();

       if(!is_null($this->firstName))
            $user->FirstName = $this->getFirstName();

       if(!is_null($this->lastName))
            $user->LastName = $this->getLastName();

       if(!is_null($this->rawDob))
            $user->Rawdob = $this->getRawDob();

       if(!is_null($this->encryptedPasword))
            $user->Password = $this->encryptedPasword;

       if(!is_null($this->externalEmail))
            $user->ExternalEmail = $this->getExternalEmail();

       if(!is_null($this->grade))
           $user->Grade =  $this->getGrade();

       if(isset($this->disabled))
           $user->Disabled = $this->isDeleted();

       if(!is_null($this->roles))
           $user->Roles =  $this->getRoles();

       if(!is_null($this->userMetaData))
          $user->OptionsString = $this->getUserMetaData();

       if(isset($user->NodeId))
            unset($user->NodeId);

       if(isset($user->Id))
            unset($user->Id);

       if(isset($user->NodeName))
            unset($user->NodeName);

        return $user;
    }
       
    private function sisJSONToObject($userJSON) {
        $this->setAccount($userJSON->Account);

        if($userJSON->EPalsEmail)
            $this->setEPalsEmail($userJSON->EPalsEmail);

        if($userJSON->ExternalEmail)
            $this->setExternalEmail($userJSON->ExternalEmail);

        if($userJSON->FirstName)
            $this->setFirstName($userJSON->FirstName);

        if($userJSON->LastName)
            $this->setLastName($userJSON->LastName);

        $this->internalId = $userJSON->Id;

        if($userJSON->Roles)
            $this->roles = $userJSON->Roles;

        if($userJSON->Disabled)
            $this->disabled = $userJSON->Disabled;

        if($userJSON->OptionsString)
            $this->setUserMetaData($userJSON->OptionsString);

        if($userJSON->ExternalId)
            $this->setUserId($userJSON->ExternalId);

        if(isset($userJSON->Grade))
            $this->grade = $userJSON->Grade;

        if($userJSON->Password)
            $this->encryptedPasword = $userJSON->Password;

        if(isset($userJSON->Rawdob))
            $this->rawDob = $userJSON->Rawdob;
    }
}

