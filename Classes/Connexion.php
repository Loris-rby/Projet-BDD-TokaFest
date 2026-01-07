<?php
class Connexion { // Singleton
    private static $instance = null;
    
    private $manager;
    private $dbName = 'tokafest_db';

    // Infos de connexion sécurisée
    private $dbUser = 'adminBDD';
    private $dbPass = 'admin123'; 
    private $dbHost = 'localhost';
    private $dbPort = '27017';
private function __construct() {
        try {
            // Encode le mdp
            $encodedPass = urlencode($this->dbPass);

            $uri = "mongodb://{$this->dbUser}:{$encodedPass}@{$this->dbHost}:{$this->dbPort}/{$this->dbName}?authSource={$this->dbName}";
            
            // Debug (A supprimer une fois que ça marche !)
            // echo "Tentative connexion : " . $uri; 

            $this->manager = new MongoDB\Driver\Manager($uri);
            
            // Petit test rapide pour vérifier que la connexion est VRAIMENT établie
            // (Le Manager ne se connecte réellement qu'à la première requête)
            $command = new MongoDB\Driver\Command(['ping' => 1]);
            $this->manager->executeCommand($this->dbName, $command);

        } catch (Exception $e) {
            // Affiche l'erreur exacte pour qu'on puisse comprendre si ça plante encore
            die("Erreur critique BDD : " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Connexion();
        }
        return self::$instance;
    }

    public function getManager() {
        return $this->manager;
    }

    public function getDbName() {
        return $this->dbName;
    }
}
?>