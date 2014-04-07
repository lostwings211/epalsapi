<?php
namespace ePals\Base;

use \ePals\Utility\UUID;
use \Elasticsearch\Client as Elasticsearch_Client;

Class Record {
    
    public $createDate;
    public $id;
    public $modifyDate;
    public $memcache;
    public $elasticSearchClient;
    public $name;
    public $tenant;
    private $elasticsearch_server;
    
    public function get_ElasticSearch_Server() {
        return $this->elasticsearch_server;
    }
    
    public function set_ElasticSearch_Server($elasticsearch_server) {
        $this->elasticsearch_server = $elasticsearch_server;
        $es_server_array =  array($elasticsearch_server);
        $this->elasticSearchClient = new Elasticsearch_Client(array('hosts' => $es_server_array));
    }
    
    function __construct() {
        $this->tenant = 'community.epals.com';
        $this->name = strtolower(get_called_class());
    }
    
    function add() {
        if(!isset($this->elasticsearch_server) || trim($this->elasticsearch_server)==='') {
            throw new Exception("elastic search server is not set!");
        }
        if (is_null($this->id)) {
            $uuid = new UUID();
            $this->id = $uuid->create();
        }
        $this->createDate = time();
        $currentAtts = get_object_vars($this);
        $currentAtts = $this->cleanAtts($currentAtts);
        $params = array();
        $params['body']  = $currentAtts;
        $params['index'] = $this->tenant;
        $params['type']  = $this->name;
        $params["id"] = $this->id;
        $res = $this->elasticSearchClient->index($params);
        return $res;
    }
    
    function get() {
        if(!isset($this->elasticsearch_server) || trim($this->elasticsearch_server)==='') {
            throw new Exception("elastic search server is not set!");
        }
        $params['index'] = $this->tenant;
        $params['type']  = $this->name;
        $params['id'] = $this->id;
        $params['ignore'] =  array(404,400);
        try {
            $results = $this->elasticSearchClient->get($params);
        } catch (Exception $e) {
            return FALSE;
        }
        $data = null;

        if ((isset($results["exists"]) && $results["exists"]) ||
            (isset($results["found"]) && $results["found"])) {
            $data = $results["_source"];
            $keys = array_keys($data);
            if ($keys) {
                foreach ($keys as $key) {
                    $this->$key = $data[$key];
                }
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    function search() {
        if(!isset($this->elasticsearch_server) || trim($this->elasticsearch_server)==='') {
            throw new Exception("elastic search server is not set!");
        }
        $params['index'] = $this->tenant;
        $params['type']  = $this->name;
        $params['body']['query']['match']["id"] = $this->id;
        $results = $this->elasticSearchClient->search($params);
        $data = null;
        if (isset ($results["hits"]["hits"][0]["_source"])) {
            $data = $results["hits"]["hits"][0]["_source"];
            $keys = array_keys($data);
            if ($keys) {
                foreach ($keys as $key) {
                    $this->$key = $data[$key];
                }
            }
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    function getByKey($key,$value) {
        if(!isset($this->elasticsearch_server) || trim($this->elasticsearch_server)==='') {
            throw new Exception("elastic search server is not set!");
        }
        $params['index'] = $this->tenant;
        $params['type']  = $this->name;
        $params['body']['query']['match'][$key] = $value;
        $results = $this->elasticSearchClient->search($params);
        return $results['hits']['hits'];
    }
    
    function getByQuery($query, $filter) {
        $filter = array();
        $filter['term']['my_field'] = 'abc';
        $jsonQuery = json_encode($query);
        $jsonFilter = json_encode($filter);
        print("My filter is \n $filter and my query is \n $query");
        $query = array();
        $query['match']['my_other_field'] = 'xyz';
        $params['index'] = $this->tenant;
        $params['type']  = $this->name;
        $params['body']['query']['filtered'] = array(
            "filter" => $filter,
             "query"  => $query
        );
    }
    
    function toArray() {
        $currentAtts = get_object_vars($this);
        $currentAtts = $this->cleanAtts($currentAtts);
        return $currentAtts;
    }
    
    function update() {
        if(!isset($this->elasticsearch_server) || trim($this->elasticsearch_server)==='') {
            throw new Exception("elastic search server is not set!");
        }
        $this->modifyDate = time();
        $currentAtts = get_object_vars($this);
        $currentAtts = $this->cleanAtts($currentAtts);
        unset($currentAtts["id"]);
        $params = array();
        $params['body']  = array("doc" => $currentAtts);
        $params['index'] = $this->tenant;
        $params['type']  = $this->name;
        $params['id'] = $this->id;
        $res = $this->elasticSearchClient->update($params);
    }
    
    function delete() {
        if(!isset($this->elasticsearch_server) || trim($this->elasticsearch_server)==='') {
            throw new Exception("elastic search server is not set!");
        }
        $deleteParams = array();
        $deleteParams['index'] = $this->tenant;
        $deleteParams['type'] = $this->name;
        $deleteParams['id'] = $this->id;
        $res = $this->elasticSearchClient->delete($deleteParams);
        return $res;
    }
    
    private function cleanAtts ($currentAtts) {
        unset($currentAtts["m"]);
        unset($currentAtts["collection"]);
        unset($currentAtts["memcache"]);
        unset($currentAtts["elasticSearchClient"]);
        unset($currentAtts["tenant"]);
        unset($currentAtts["name"]);
        return $currentAtts;
    }
}
