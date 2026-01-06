<?php
require_once 'Manager.php';

class SceneManager extends Manager {
    protected $collection = 'scenes';

        public function __construct() {
        parent::__construct();
    }

    // Ajouter une Scène
    public function create($data) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($data);
        $this->manager->executeBulkWrite("$this->dbName.$this->collection", $bulk);
    }

    // Modifier une Scène
    public function update($id, $data) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update(
            ['_id' => new MongoDB\BSON\ObjectId($id)],
            ['$set' => $data]
        );
        $this->manager->executeBulkWrite("$this->dbName.$this->collection", $bulk);
    }
}
?>