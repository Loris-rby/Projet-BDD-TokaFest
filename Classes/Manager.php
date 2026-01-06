<?php
require_once 'Connexion.php';

class Manager { //Classe mère
    protected $manager;
    protected $dbName;
    protected $collection; 

    public function __construct() {
        $db = Connexion::getInstance();
        $this->manager = $db->getManager();
        $this->dbName = $db->getDbName();
    }

    // Récupérer tous les documents (avec option de tri)
    public function findAll($sort = []) {
        $options = [];
        if (!empty($sort)) {
            $options['sort'] = $sort;
        }
        
        $query = new MongoDB\Driver\Query([], $options);
        $cursor = $this->manager->executeQuery("$this->dbName.$this->collection", $query);
        
        return $cursor->toArray();
    }

    // Récupérer un seul doc par son ID
    public function findById($id) {
        try {
            $filter = ['_id' => new MongoDB\BSON\ObjectId($id)];
            $query = new MongoDB\Driver\Query($filter);
            $cursor = $this->manager->executeQuery("$this->dbName.$this->collection", $query);
            $result = $cursor->toArray();
            
            return count($result) > 0 ? $result[0] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    // Supprimer un doc par son ID
    public function delete($id) {
        try {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->delete(['_id' => new MongoDB\BSON\ObjectId($id)]);
            $this->manager->executeBulkWrite("$this->dbName.$this->collection", $bulk);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>