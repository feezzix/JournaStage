<?php

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Si on est en production sur ton sous-domaine, la base est directement la racine
if ($host === 'journastage.camillefezandelle.cloud') {
  $baseUrl = "$protocol://$host";
} else {
  // En local, on récupère le dossier parent
  $scriptName = dirname($_SERVER['SCRIPT_NAME']);
  
  // Correction de la casse : si le serveur trouve "journastage" en minuscules, 
  // on le force proprement en "JournaStage"
  $scriptName = str_replace('/journastage', '/JournaStage', $scriptName);
  
  $baseUrl = rtrim("$protocol://$host$scriptName", '/');
}

define('BASE_URL', $baseUrl);
