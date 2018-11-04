<?php
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
  PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
];

$pdo = new PDO('mysql:host=localhost;dbname=deal', 'root', '', $options);
