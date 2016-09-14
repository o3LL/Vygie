#!/usr/bin/php

<?php
/*
        Script d'import des données des écoles

        -- Changer l'url si besoin
        -- Spécifier le nom 'utilisateur de la base
        -- Spécifier le mot de passe de la base
        -- Le hostname de MySQL
        -- Le nom de la base MySQL
        -- Le nom de la table
*/


// Import the data loader util
require_once("app/models/DataImporter.class.php");

// Instanciate the object with the current settings
$DI = new DataImporter('config.ini');
$result = $DI->Import();

?>
