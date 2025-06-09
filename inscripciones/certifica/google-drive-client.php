<?php
// google-drive-client.php

require __DIR__ . '/../../vendor/autoload.php';  

$client = new Google_Client();
// Le indicas dónde está tu JSON de credenciales
$client->setAuthConfig(__DIR__ . '/certifica.json');
// Sólo necesitas permiso para crear/leer archivos en Drive
$client->addScope(Google_Service_Drive::DRIVE_FILE);
// (Opcional) Si tu service account debe actuar como un usuario concreto:
// $client->setSubject('artistico-resultado@pc-sync-jobs.iam.gserviceaccount.com');

$service = new Google_Service_Drive($client);
