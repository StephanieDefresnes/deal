<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();

$req = 'SELECT count(*) FROM annonce WHERE membre_id = ' . (int)$_GET['id'];
$stmt = $pdo->query($req);
$nbAnnonces = $stmt->fetchColumn();

if (!empty($nbAnnonces)) {
  setFlashMessage('Le membre a des annonces');
} else {
  $req = 'DELETE FROM membre WHERE id = ' . (int)$_GET['id'];
  $pdo->exec($req);
  setFlashMessage('Le membre est supprimé');
}

header('Location: membres.php');
die;
