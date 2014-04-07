<?php
namespace ePals;

use \ePals\Base\Rest;
use \Exception;

class Section extends Rest {

    private $id;  //uuid
    private $sectionId; // ExternalId
    private $tenantDomain;
    private $schoolId;
    private $courseId;
    private $notes;
    private $meetingTimes;
    private $startDate;
    private $endDate;
     
    public function getSectionId() {
        return $this->sectionId;
    }

    public function getTenantDomain() {
        return $this->tenantDomain;
    }

    public function getSchoolId() {
        return $this->schoolId;
    }

    public function getCourseId() {
        return $this->courseId;
    }

    public function getNotes() {
        return $this->notes;
    }

    public function getMeetingTimes() {
        return $this->meetingTimes;
    }

    public function getStartDate() {
        return $this->startDate;
    }

    public function getId() {
        return $this->id;
    }

    public function getEndDate() {
        return $this->endDate;
    }

    public function setSectionId($sectionId) {
        $this->sectionId = $sectionId;
    }

    public function setNotes($notes) {
        $this->notes = $notes;
    }

    public function setMeetingTimes($meetingTimes) {
        $this->meetingTimes = $meetingTimes;
    }

    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }
    
    function loadSection($tenantDomain, $schoolId, $courseId, $sectionId) {
        if (empty($tenantDomain) || empty($schoolId) || empty($courseId) || empty($sectionId)) {
            throw new Exception("Either of these parameters are empty : tenantDomain, schoolId, courseId, sectionId (name)");
        }
        //Build the URL of the REST endpoint
        $path = "tenant/" . rawurlencode($tenantDomain) . "/school/" . rawurlencode($schoolId) . "/course/" . rawurlencode($courseId) . "/section/" . rawurlencode($sectionId);
        //Make the REST call and decode the returned JSON string
        $section = parent::_getSISURL($path, null);
        if (!isset($section)) {
            throw new Exception("Record not found in graph!");
        }
        $this->sisJSONToObject($section);
        $this->tenantDomain = $tenantDomain;
        $this->schoolId = $schoolId;
        $this->courseId = $courseId;
    }
     
    function update() {
        if (empty($this->tenantDomain) || empty($this->schoolId) || empty($this->courseId) || empty($this->sectionId)) {
            throw new Exception("Either of these parameters are empty : tenantDomain, schoolId, courseId, sectionId(name)");
        }

        $section = array(
            'ExternalId' => $this->sectionId,
            'Notes' => $this->notes,
            'MeetingTimes' => $this->meetingTimes,
            'StartDate' => $this->startDate,
            'EndDate' => $this->endDate
        );

        $section = array_filter($section, 'strlen');
        //Build the URL of the REST endpoint
        $path = "tenant/" . rawurlencode($this->tenantDomain) . "/school/" . rawurlencode($this->schoolId) . "/course/" .
                rawurlencode($this->courseId) . "/section/edit";
        //Make the REST call and decode the returned JSON string
        $request = json_encode($section);
        return parent::_putSISURL($path, null, $request);
    }
     
    function add($tenantDomain, $schoolId, $courseId) {
        if (empty($tenantDomain) || empty($schoolId) || empty($courseId) || empty($this->sectionId)) {
            throw new Exception("Either of these parameters or properties are empty : tenantDomain, schoolId, courseId, sectionId(name)");
        }

        //Build the URL of the REST endpoint
        $path = "tenant/" . rawurlencode($tenantDomain) . "/school/" . rawurlencode($schoolId) . "/course/" .
                rawurlencode($courseId) . "/section/create";

        //Make the REST call and decode the returned JSON string
        $section = array (
            'ExternalId' => $this->sectionId,
            'Notes' => $this->notes,
            'MeetingTimes' => $this->meetingTimes,
            'StartDate' => $this->startDate,
            'EndDate' => $this->endDate
        );

        $response = parent::_postSISURL($path, null, json_encode($section));
        $this->tenantDomain = $tenantDomain;
        $this->schoolId = $schoolId;
        $this->courseId = $courseId;

        //Return the Course portion of the decoded JSON object
        return $response;
    }
    
    function addSectionEnrollment($userAccountId, $userType) {
        if (empty($userAccountId) || empty($userType) || empty($this->tenantDomain) || empty($this->schoolId) || empty($this->courseId) || empty($this->sectionId)) {
            throw new Exception("Either of these parameters or properties are empty : userAccountId, userType, tenantDomain, schoolId, courseId, sectionId");
        }

        // REST API accepts student or teacher as usertype
        if (strtolower($userType) == 'educator')
            $userType = 'teacher';

        $_tenantDomain = rawurlencode($this->tenantDomain);
        $_schoolId = rawurlencode($this->schoolId);
        $_courseId = rawurlencode($this->courseId);
        $_sectionId = rawurlencode($this->sectionId);
        $userType = rawurlencode($userType);
        $userAccountId = rawurlencode($userAccountId);
        $params = "userId=$userAccountId";

        // endpoint of service
        $url = "/tenant/$_tenantDomain/$_schoolId/$_courseId/$_sectionId/$userType/addUser";
        return parent::_getSISURL($url, $params);
    }
    
    function deleteSectionEnrollment($userAccountId, $userType) {
        $tenantId = rawurlencode($this->tenantDomain);
        $enrollment = array(
            'SchoolExternalId' => $this->schoolId,
            'CourseExternalId' => $this->courseId,
            'SectionExternalId' => $this->sectionId,
            'UserAccountId' => $userAccountId,
            'MembershipType' => strtoupper($userType)
        );

        $post = array($enrollment);
        // delete enrollment end point
        $url = "tenant/section/unenroll/$tenantId";
        // encode json before passing to method
        return parent::_deleteSISURLSimple($url, json_encode($post));
    }
     
    function getMembers(){
        if(!isset($this->id)) {
             throw new Exception("UUID is not set. Please load details via constructor");
        }
        $path = "accessmanager/getUsers";
        $param = "sectionid=".rawurlencode($this->id);
        $members = parent::_getPMURL($path, $param);
        return $members->getUsers[0]->Users;
    }
     
    function isEnrolled($userAccountId) {
        $mems = $this->getMembers();
        foreach ($mems as $mem) {
            if(strtolower($mem->accountId) == strtolower($userAccountId)) {
                 return true;
            }
         }
         return false;
    }
     
    private function sisJSONToObject($sectionJSON) {
        $this->setSectionId($sectionJSON->ExternalId);
        $this->id  = $sectionJSON->Id;
        if(isset($sectionJSON->Notes)) {
            $this->setNotes($sectionJSON->Notes);
        }
        if(isset($sectionJSON->EndDate)) {
            $this->setEndDate($sectionJSON->EndDate);
        }
        if(isset($sectionJSON->StartDate)) {
            $this->setStartDate($sectionJSON->StartDate);
        }
        if(isset($sectionJSON->MeetingTimes)) {
            $this->setMeetingTimes($sectionJSON->MeetingTimes);
        }
     }
}

