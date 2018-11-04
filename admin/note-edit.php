<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();

$note = $avis = '';
$errors = [];

// formulaire de notation
if (isset($_POST['note'])) {
  extract($_POST);
  sanitizePost();

  // test des valeurs du formulaires
  if(empty($_POST['note'])) {
    $errors[] = 'La note est obligatoire';
  }
  if(empty($_POST['avis'])) {
    $errors[] = 'L\'avis est obligatoire';
  }

  // si le formulaire est bien rempli
  if(empty($errors)) {
    $stmt = $pdo->prepare('UPDATE note SET note = :note, avis = :avis WHERE id = :id');
    $stmt->bindValue(':note', $_POST['note']);
    $stmt->bindValue(':avis', $_POST['avis']);
    $stmt->bindValue(':id', $_GET['id']);
    $stmt->execute();
    // afficher le message de confirmation
    setFlashMessage('La note est enregistrée');
    header('Location: notes.php');
    die;
  }
} elseif (isset($_GET['id'])) {
  // s'il y a une id dans l'url et qu'il n'y a pas de retour de formulaire
  // on va chercher la commentaire en bdd
  $req = 'SELECT * FROM note WHERE id = ' . (int)$_GET['id'];
  $stmt = $pdo->query($req);
  $note = $stmt->fetch();

  extract($note);
}
include __DIR__ . '/../layout/top.php';
?>

<h1>Notes</h1>

<div class="container">

            <form method="post">
              <div class="form-group">
                <select name="note" class="form-control" style="width: 10em">
                  <option value="">Note</option>
                  <option value="1" <?php if ($note == '1'){echo 'selected';} ?>>★</option>
                  <option value="2" <?php if ($note == '2'){echo 'selected';} ?>>★★</option>
                  <option value="3" <?php if ($note == '3'){echo 'selected';} ?>>★★★</option>
                  <option value="4" <?php if ($note == '4'){echo 'selected';} ?>>★★★★</option>
                  <option value="5" <?php if ($note == '5'){echo 'selected';} ?>>★★★★★</option>
                </select>
              </div>
              <div class="form-group">
                <label>Avis :</label>
                <textarea name="avis" class="form-control" rows="4" cols="80"><?= $avis; ?></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Valider</button>
            </form>

</div>

<?php
include __DIR__ . '/../layout/bottom.php';
?>
