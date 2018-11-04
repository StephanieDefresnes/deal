<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();

$req = 'SELECT count(*) FROM commentaire WHERE reponse_id = ' . (int)$_GET['id'];
$stmt = $pdo->query($req);
$nbAnnonces = $stmt->fetchColumn();

if (!empty($nbAnnonces)) {
  setFlashMessage('Le commentaire a des réponses');
} else {
  $req = 'DELETE FROM commentaire WHERE id = ' . (int)$_GET['id'];
  $pdo->exec($req);
  setFlashMessage('Le commentaire est supprimé');
}

header('Location: commentaires.php');
die;
