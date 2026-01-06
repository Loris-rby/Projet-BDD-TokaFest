<?php
require_once 'Manager.php';

class StandManager extends Manager {
    protected $collection = 'stands';
        public function __construct() {
        parent::__construct();
    }

    // Ajouter un stand
    public function create($data) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($data);
        $this->manager->executeBulkWrite("$this->dbName.$this->collection", $bulk);
    }

    // Modifier un stand
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
