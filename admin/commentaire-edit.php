<?php
require_once __DIR__ . '/../include/init.php';

$commentaire = '';
$errors = [];

if (!empty($_POST)) {
  extract($_POST);
  sanitizePost();

  // test des valeurs du formulaires
  if(empty($_POST['commentaire'])) {
    $errors[] = 'Le commentaire est obligatoire';
  }
  // fin du test

  // si le formulaire est bien rempli
  if(empty($errors)) {

    if (isset($_GET['id'])) {  // modification
      $stmt = $pdo->prepare('UPDATE commentaire SET commentaire = :commentaire WHERE id = :id');
      $stmt->bindValue(':commentaire', $_POST['commentaire']);
      $stmt->bindValue(':id', $_GET['id']);
      $stmt->execute();


    // enregitrement du message de confirmation en session
    // puis redirection vers la liste
    setFlashMessage('Le commentaire est enregistré');
    header('Location: commentaires.php');
    die;
    }
  }
} elseif (isset($_GET['id'])) {
  // s'il y a une id dans l'url et qu'il n'y a pas de retour de formulaire
  // on va chercher la commentaire en bdd
  $req = 'SELECT * FROM commentaire WHERE id = ' . (int)$_GET['id'];
  $stmt = $pdo->query($req);
  $commentaire = $stmt->fetch();

  extract($commentaire);
}
include __DIR__ . '/../layout/top.php';
?>

<h1>Edition commentaire</h1>

<div class="container-fluid">

  <form method="post">
    <div class="form-group">
      <label>Commentaire</label>
      <textarea name="commentaire" class="form-control" rows="8" cols="80"><?= $commentaire; ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Envoyer</button>
    <a href="commentaires.php" type="submit" class="btn btn-default">Retour à la gestion des commentaires</a>
  </form>

<?php
include __DIR__ . '/../layout/bottom.php';
?>
