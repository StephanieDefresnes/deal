<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();

$req = 'DELETE FROM note WHERE id = ' . (int)$_GET['id'];
$pdo->exec($req);
setFlashMessage('La note est supprimée');


header('Location: notes.php');
die;
?>