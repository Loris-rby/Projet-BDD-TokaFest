<?php
require_once 'Manager.php';

class ArtisteManager extends Manager { //Classe fille - Gere les artistes
    protected $collection = 'artistes';

    public function __construct() {
        parent::__construct();
    }

    // Ajouter un artiste
    public function create($data) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->insert($data);
        $this->manager->executeBulkWrite("$this->dbName.$this->collection", $bulk);
    }

    // Modifier un artiste
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