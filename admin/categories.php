<?php
require_once __DIR__ . '/../include/init.php';

// appel à la fonction qui empêche d'accéder à la page si on n'est pas admin
adminSecurity();

// lister toutes les catégories dans un tableau HTML
$stmt = $pdo->query('SELECT * FROM categorie order by id');
$categories = $stmt->fetchAll();

$titre = $mots_cles = $categorie = '';

//si le formulaire a été envoyé
// vérifier que le champs titre a été rempli et qu'il est unique.
// Si oui enregistrer la catégorie en bdd sinon on affiche un message d'erreur
$errors = [];

if (!empty($_POST)) {
  extract($_POST);
  sanitizePost();

  // test des valeurs du formulaires
  if(empty($_POST['titre'])) {
    $errors[] = 'Le titre est obligatoire';
  } else {
    $req = 'SELECT count(*) FROM categorie WHERE titre = ' . $pdo->quote($_POST['titre']);

    // en modification, on exclut de la requête la catégorie que l'on est en train de modifier
    if (isset($_GET['id'])) {
      $req .= ' AND id != ' . (int)$_GET['id'];
    }

    $stmt = $pdo->query($req);
    $nb = $stmt->fetchColumn();

    if ($nb != 0) {
      $errors[] = "La catégorie  $titre existe déjà";
    }
  }
  // fin du test

  // si le formulaire est bien rempli
  if(empty($errors)) {

    if (isset($_GET['id'])) {  // modification
      $stmt = $pdo->prepare('UPDATE categorie SET titre = :titre, mots_cles = :mots_cles WHERE id = :id');
      $stmt->bindValue(':titre', $titre);
      $stmt->bindValue(':mots_cles', $mots_cles);
      $stmt->bindValue(':id', $_GET['id']);
      $stmt->execute();
    } else {  // création
      $stmt = $pdo->prepare('INSERT INTO categorie (titre, mots_cles) VALUES (:titre, :mots_cles)');
      $stmt->bindValue(':titre', $titre);
      $stmt->bindValue(':mots_cles', $mots_cles);
      $stmt->execute();
    }


    // enregitrement du message de confirmation en session
    // puis redirection vers la liste
    setFlashMessage('La catégorie est enregistrée');
    header('Location: categories.php');
    die;
  }
} elseif (isset($_GET['id'])) {
  // s'il y a une id dans l'url et qu'il n'y a pas de retour de formulaire
  // on va chercher la catégorie en bdd
  $req = 'SELECT * FROM categorie WHERE id = ' . (int)$_GET['id'];
  $stmt = $pdo->query($req);
  $categorie = $stmt->fetch();
  extract($categorie);
}

include __DIR__ . '/../layout/top.php';

?>

<h1>Gestion catégories</h1>

<!-- liste des catégories -->
<table class="table">
  <caption>Catégories</caption>
  <tr>
    <th>ID</th>
    <th>Noms</th>
    <th>Mots clés</th>
    <th class="text-right">Actions</th>
  </tr>
  <?php
  foreach ($categories as $categorie) :
  ?>
    <tr>
      <td><?= $categorie['id']; ?></td>
      <td><?= $categorie['titre']; ?></td>
      <td><?= $categorie['mots_cles']; ?></td>
      <td class="text-right">
        <a href="categories.php?id=<?= $categorie['id']; ?>#edit" class="btn btn-primary"><i class="fa fa-pencil-square-o"></i></a>
        <a href="categorie-delete.php?id=<?= $categorie['id']; ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette categorie ?');"><i class="fa fa-trash"></i></a>
      </td>
    </tr>
  <?php
  endforeach;
  ?>
</table>

<!-- formulaires des catégories -->
<div class="col-md-6 col-md-offset-3" id="edit">
  <?php
  if (!empty($errors)) :
  ?>
  <div class="alert alert-danger">
    <strong>Le formulaire contient des erreurs</strong><br>
    <?= implode('<br>', $errors); ?>
  </div>
  <?php
  endif;
  ?>

  <form method="post">
    <div class="form-group">
      <label>Titre</label>
      <input type="text" class="form-control" name="titre" value="<?= $titre; ?>">
    </div>
    <div class="form-group">
      <label>Mots clés</label>
      <textarea name="mots_cles" class="form-control" rows="8" cols="80"><?= $mots_cles; ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Enregistrer</button>
    <a href="categories.php" class="btn btn-default">Retour</a>
  </form>
</div>
<?php
include __DIR__ . '/../layout/bottom.php';
?>
