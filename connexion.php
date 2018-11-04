<?php
require_once __DIR__ . '/include/init.php';

$pseudo = '';
$errors = [];

if (!empty($_POST)){
  sanitizePost();
  extract($_POST);

  if(empty($_POST['pseudo'])) {
    $errors[] = 'Le pseudo est obligatoire';
  }
  if(empty($_POST['mdp'])) {
    $errors[] = 'Le mot de passe est obligatoire';
  }
  if(empty($errors)) {
    $req = 'SELECT * FROM membre WHERE pseudo = ' . $pdo->quote($pseudo);
    $stmt = $pdo->query($req);
    $membre = $stmt->fetch();

    // s'il ya a un membre en bdd avec cet email
    if (!empty ($membre)){
      // vérification du mdp
      if (password_verify($mdp, $membre['mdp'])) {
        // connecter un utilisateur, c'est l'enregistrer dans la session
        $_SESSION['membre'] = $membre;
        // rediretion vers l'accueil
        header('Location: index.php');
        die;  // ou exist; - stoppe l'exécution du script
      }
    }
    $errors[] = 'Pseudo ou mot de passe incorrect';
  }
}

include __DIR__ . '/layout/top.php';
?>

<h1>Connexion</h1>

<div class="col-md-6 col-md-offset-3">

  <?php
  // syntaxe longue avec : .. endif; à l'intérieur du HTML au lieu des accolade
  if (!empty($errors)) :
    ?>
    <div class="alert alert-danger">
      <strong>Le formulaire contient des erreurs</strong><br>
      <?php
      // '<?=' est la notation courte pour '<?php echo'
      // on affiche les valeurs des élélments du tableau séparés par <br>
      ?>
      <?= implode('<br>', $errors); ?>
    </div>
    <?php
  endif;
  ?>

  <form method="post">

    <div class="form-group">
      <label>Pseudo</label>
      <input type="text" name="pseudo" value="<?= $pseudo; ?>" class="form-control">
    </div>

    <div class="form-group">
      <label>Mot de passe</label>
      <input type="password" name="mdp" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">Valider</button>

  </form>
  <br>
</div>

<?php include __DIR__ . '/layout/bottom.php'; ?>
