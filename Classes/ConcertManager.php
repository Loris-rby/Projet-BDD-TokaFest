<?php
require_once 'Manager.php';

class ConcertManager extends Manager {
    protected $collection = 'concerts';

        public function __construct() {
        parent::__construct();
    }

    // Ajouter un concert
    public function create($data) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($data);
        $this->manager->executeBulkWrite("$this->dbName.$this->collection", $bulk);
    }

    // Modifier un concert
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
