<?php
namespace ePals;

use \ePals\Base\Rest;
use ePals\Course;
use \Exception;

class School extends Rest {
    private $collapsedName;
    private $optionsString;
    private $description;
    private $schoolId;
    private $name;
    private $tenantUUID;
    private $id;
    private $tenantDomain;

    public function getCollapsedName() {
        return $this->collapsedName;
    }

    public function getOptionsString() {
        return $this->optionsString;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getSchoolId() {
        return $this->schoolId;
    }

    public function getName() {
        return $this->name;
    }

    public function getTenantUUID() {
        return $this->tenantUUID;
    }

    public function getId() {
        return $this->id;
    }

    public function getTenantDomain() {
        return $this->tenantDomain;
    }

    public function setOptionsString($optionsString) {
        $this->optionsString = $optionsString;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setSchoolId($schoolId) {
        $this->schoolId = $schoolId;
    }

    public function setName($name) {
        $this->name = $name;
    }

    function loadSchool($tenantDomain, $schoolId) {
       $path = "tenant/" . $tenantDomain . "/school/" . $schoolId;
       $school = parent::_getSISURL($path, null);
       $this->sisJSONToObject($school);
       $this->tenantDomain = $tenantDomain;
    }

    public function exists($tenantDomain, $schoolId) {
        $path = "tenant/" . $tenantDomain . "/school/" . $schoolId;
        try {
            $school = parent::_getSISURL($path, null);
            if(isset($school->Id)) {
                return true;
            }
        }
        catch(Exception $e) {
           return false;
        }
    }

    function add($tenantDomain) {
        $path = "tenant/" . rawurlencode($tenantDomain) . "/school/create";
        //Make the REST call and decode the returned JSON string
        $school = array(
            'OptionsString' => $this->optionsString,
            'Description' => $this->getDescription(),
            'ExternalId' => $this->schoolId,
            'Name' => $this->name
        );

        $response = parent::_postSISURL($path, null, json_encode($school));
        $this->tenantDomain = $tenantDomain;
        $this->createDefaultCourse();
        return $response;
    }
        
    function update() {
        if(empty($this->tenantDomain)) {
            throw new Exception ("tenantDomain is empty");
        }
        if(empty($this->schoolId)) {
            throw new Exception ("schoolId is empty");
        }

        $path = "tenant/" . rawurlencode($this->tenantDomain) . "/school/edit";
        $school = array(
            'OptionsString' => $this->optionsString,
            'Description' => $this->description,
            'ExternalId' => $this->schoolId,
            'Name' => $this->name,
            'Id' => $this->id
        );
        $response = parent::_putSISURL($path, null, json_encode($school));
        return $response;
    }

    public function addUserToSchool($accountId, $userType) {
        if(empty($accountId) || empty($userType)) {
            throw new Exception("Account or userType parameter is empty.");
        }
        
        $userType = strtolower($userType);
        if(empty($this->tenantDomain) || empty($this->schoolId)) {
            throw new Exception("TenantDomain or SchoolId property is empty. Please load school");
        }

        $path = "tenant/" . rawurlencode($this->tenantDomain) . "/" . rawurlencode($this->schoolId) . "/" . rawurlencode($userType) . "/addUser";
        $params = "userId=" . rawurlencode($accountId);
        return parent::_getSISURL($path, $params);
    }
        
    private function sisJSONToObject($schoolJSON) {
        $this->collapsedName = $schoolJSON->CollapsedName;
        $this->setOptionsString($schoolJSON->OptionsString);

        if(isset($schoolJSON->Description)) {
            $this->setDescription($schoolJSON->Description);
        }

        $this->setSchoolId($schoolJSON->ExternalId);
        $this->setName($schoolJSON->Name);
        $this->tenantUUID =$schoolJSON->TenantId;
        $this->id = $schoolJSON->Id;
    }
     
    function createDefaultCourse() {
        if(empty($this->tenantDomain)) {
            throw new Exception('Tenant domain is empty. Please load Tenant.');
        }
        $api_server = $this->get_SIS_Server();
        $tenantdomain = $this->tenantDomain;
        $schoolId = $this->getSchoolId();
        $defaultcourseID = "defaultcourse";
        try {
            $course = new Course();
            $course->set_SIS_Server($api_server);
            $course->loadCourse($tenantdomain, $schoolId, $defaultcourseID);
        }  
        catch (Exception $e) {
            // School Doesnt Exist
            $course = new Course();
            $course->set_SIS_Server($api_server);
            $course->setTitle($defaultcourseID);
            $course->setCourseId($defaultcourseID);
            $course->setDescription('default course for orphans accounts');
            $course->add($this->tenantDomain, $schoolId);
        }
    }
}

