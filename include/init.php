<?php
// initialise la session
session_start();

//var_dump($_SESSION);

// la racine du site à partir du répertoire www
// qui correcpond à la racine de http://localhost
define('RACINE_WEB', '/deal/');  //-> '/' pour la racine du nom de domaine
// répertoire photo dans le système de fichier
define('PHOTO_DIR', __DIR__ . '/../photo/');
// url relative au répertoire photo
define('PHOTO_WEB', RACINE_WEB . 'photo/');

define('PHOTO_DEFAULT', 'https://dummyimage.com/200x200/cccccc/000000.jpg&text=Pas+de+photo');
require_once __DIR__ . '/connexion.php';
require_once __DIR__ . '/fonctions.php';
