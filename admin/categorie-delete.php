<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();

$req = 'SELECT count(*) FROM annonce WHERE categorie_id = ' . (int)$_GET['id'];
$stmt = $pdo->query($req);
$nbAnnonces = $stmt->fetchColumn();

if (!empty($nbAnnonces)) {
  setFlashMessage('La catégorie contient des annonces');
} else {
  $req = 'DELETE FROM categorie WHERE id = ' . (int)$_GET['id'];
  $pdo->exec($req);
  setFlashMessage('La catégorie est supprimée');
}

header('Location: categories.php');
die;
