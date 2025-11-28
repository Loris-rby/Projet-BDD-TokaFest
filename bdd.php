<?php

$manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

$bulk = new MongoDB\Driver\BulkWrite;

$bulk->insert(['x' => 1]);

$bulk->insert(['x' => 2]);

$bulk->insert(['x' => 3]);

$manager->executeBulkWrite('db.collection', $bulk);

$filter = ['x' => ['$gt' => 1]];

$options = [

    'projection' => ['_id' => 0],

    'sort' => ['x' => -1],

];

$query = new MongoDB\Driver\Query($filter, $options);

$cursor = $manager->executeQuery('db.collection', $query);

foreach ($cursor as $document) {

    var_dump($document);

}

/* Inserez des documents afin que notre requete renvoie des informations */

$bulkWrite = new MongoDB\Driver\BulkWrite;

$bulkWrite->insert(['name' => 'Ceres', 'size' => 946, 'distance' => 2.766]);

$bulkWrite->insert(['name' => 'Vesta', 'size' => 525, 'distance' => 2.362]);

$manager->executeBulkWrite("test.asteroids", $bulkWrite);

/* Requete pour tous les elements de la collection */

$query = new MongoDB\Driver\Query( [] );

/* Interrogez la collection  "asteroids" de la base de donnees "test" */

$cursor = $manager->executeQuery("test.asteroids", $query);

/* $cursor contient maintenant un objet qui entoure le jeu de resultats. 

 * Utilisez foreach() pour iterer sur tous les resultats */

foreach($cursor as $document) {

    print_r($document);

}

?>