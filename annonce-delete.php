<?php
require_once __DIR__ . '/include/init.php';

$req = 'SELECT count(*) FROM commentaire WHERE annonce_id = ' . (int)$_GET['id'];
$stmt = $pdo->query($req);
$nbAnnonces = $stmt->fetchColumn();

if (!empty($nbAnnonces)) {
  setFlashMessage('L\'annonce contient des commentaires');
} else {
  $req = 'DELETE FROM annonce WHERE id = ' . (int)$_GET['id'];
  $pdo->exec($req);
  setFlashMessage('L\'Annonce est supprimée');
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
die;
