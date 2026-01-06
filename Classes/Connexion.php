<?php
class Connexion { //Singleton
    private static $instance = null;
    
    private $manager;
    private $dbName = 'tokafest_db';

    private function __construct() {
        try {
            $this->manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
        } catch (Exception $e) {
            die("Erreur de connexion à la BDD : " . $e->getMessage());
        }
    }

    // Méthode statique pour récupérer l'instance unique
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Connexion();
        }
        return self::$instance;
    }

    // Getter pour récupérer le Manager MongoDB
    public function getManager() {
        return $this->manager;
    }

    // Getter pour récupérer le nom de la BDD
    public function getDbName() {
        return $this->dbName;
    }
}
?>