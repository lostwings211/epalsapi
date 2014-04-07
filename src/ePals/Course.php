<?php
namespace ePals;

use \ePals\Base\Rest;
use \Exception;

class Course extends Rest {

    private $courseId; // required: Course ID
    private $tenantDomain; // course tenant id
    private $schoolId;// school external id
    private $description; // 
    private $title; // reqiCourse title
    private $department; // course department
    private $notes; // course notes
    private $prerequisites; // course pre-requisites
    private $credits; //course credits
    
    public function getDescription() {
        return $this->description;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDepartment() {
        return $this->department;
    }

    public function getNotes() {
        return $this->notes;
    }

    public function getPrerequisites() {
        return $this->prerequisites;
    }

    public function getCredits() {
        return $this->credits;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDepartment($department) {
        $this->department = $department;
    }

    public function setNotes($notes) {
        $this->notes = $notes;
    }

    public function setPrerequisites($prerequisites) {
        $this->prerequisites = $prerequisites;
    }

    public function setCredits($credits) {
        $this->credits = $credits;
    }

    public function getCourseId() {
        return $this->courseId;
    }

    public function getTenantDomain() {
        return $this->tenantDomain;
    }

    public function getSchoolId() {
        return $this->schoolId;
    }

    public function setCourseId($courseId) {
        $this->courseId = $courseId;
    }
         
    function loadCourse($tenantDomain, $schoolId, $courseId) {
        if(empty($tenantDomain) || empty($schoolId) || empty($courseId)) {
           throw new Exception("Either of these parameters are empty : tenantDomain, schoolId, courseId");
        }

        $path = "tenant/" . rawurlencode($tenantDomain) . "/school/" . rawurlencode($schoolId) . "/course/" . rawurlencode($courseId);
        $course = parent::_getSISURL($path, null);
        $this->sisJSONToObject($course);
        $this->schoolId = $schoolId;
        $this->tenantDomain = $tenantDomain;
    }
       
    function update() {
        if(empty($this->tenantDomain) || empty($this->schoolId) || empty($this->courseId) || empty($this->title)) {
            throw new Exception("Either of these properties are empty : tenantDomain, schoolId, courseId, title");
        }
        $course = array(
            'ExternalId' => $this->courseId,
            'Description' => $this->description,
            'Title' => $this->title,
            'Department' => $this->department,
            'Notes' => $this->notes,
            'Prerequisites' => $this->prerequisites,
            'Credits' => $this->credits
        );

        //Build the URL of the REST endpoint
        $path = "tenant/" . rawurlencode($this->tenantDomain) . "/school/" . rawurlencode($this->schoolId) ."/course/edit";

        //Make the REST call and decode the returned JSON string
        $request = json_encode($course);
        return parent::_putSISURL($path, null, $request);
    }
        
    function add($tenantDomain, $schoolId) {
        if(empty($tenantDomain) || empty($schoolId) || empty($this->courseId) || empty($this->title)) {
            throw new Exception("Either of these parameters/properties are empty : tenantExternalId, schoolExternalId, courseExternalId, title");
        }

        //Build the URL of the REST endpoint
        $path = "tenant/" . rawurlencode($tenantDomain) . "/school/" . rawurlencode($schoolId) ."/course/create";
        $course = array(
            'ExternalId' => $this->courseId,
            'Description' => $this->description,
            'Title' => $this->title,
            'Department' => $this->department,
            'Notes' => $this->notes,
            'Prerequisites' => $this->prerequisites,
            'Credits' => $this->credits
        );
        $course = array_filter($course, 'strlen');

        //Make the REST call and decode the returned JSON string
        $response = parent::_postSISURL($path, null, json_encode($course));

        $this->tenantDomain = $tenantDomain;
        $this->schoolId = $schoolId;
        $this->createDefaultSection();

        //Return the Course portion of the decoded JSON object
        return $response;
    }
        
    private function sisJSONToObject($courseJSON) {
        $this->setCourseId($courseJSON->ExternalId);
        if(isset($courseJSON->Title))
            $this->setTitle($courseJSON->Title);
        if(isset($courseJSON->Description))
            $this->setDescription($courseJSON->Description);
        if(isset($courseJSON->Department))
            $this->setDepartment($courseJSON->Department);
        if(isset($courseJSON->Notes))
            $this->setNotes($courseJSON->Notes);
        if(isset($courseJSON->Prerequisites))
            $this->setPrerequisites($courseJSON->Prerequisites);
        if(isset($courseJSON->Credits))
            $this->setCredits($courseJSON->Credits);
    }
        
    function createDefaultSection() {
        $api_server = $this->get_SIS_Server();
        $defaultSchoolId = $this->schoolId;
        $defaultCourseId = $this->courseId;
        $defaultSectionId = "defaultsection";
        try {
            //$section = new Section($this->tenantDomain, $defaultSchoolId, $defaultCourseId, $defaultSectionId);
            $section = new Section();
            $section->set_SIS_Server($api_server);
            $section->loadSection($this->tenantDomain, $defaultSchoolId, $defaultCourseId, $defaultSectionId);
        }
        catch (Exception $e) {
             // School Doesnt Exist
             $section = new Section();
             $section->set_SIS_Server($api_server);
             $section->setSectionId($defaultSectionId);
             $section->add($this->tenantDomain, $defaultSchoolId, $defaultCourseId);
        }
        if($section->getSectionId() !== $defaultSectionId) {
            $section = new Section();
            $section->set_SIS_Server($api_server);
            $section->setSectionId($defaultSectionId);
            $section->add($this->tenantDomain, $defaultSchoolId, $defaultCourseId);
        }
    }
}

