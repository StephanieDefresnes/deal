<?php
require_once __DIR__ . '/include/init.php';

$errors = [];
$civilite = $nom = $prenom = $email = $telephone = $pseudo = '';

if(!empty($_POST)) {  // le formulaire est envoyé
  // cf include/fonctions.php
  sanitizePost();
  // crée des variables à partir d'un tableau
  // $_POST['nom'] = 'Anest' crée $nom = 'Anest'
  extract($_POST);

  if(empty($_POST['pseudo'])) {
    $errors[] = 'Le pseudo est obligatoire';
    // test de la validité de l'adresse mail
  } elseif(!preg_match('/^[A-Za-z0-9]{6,20}$/', $_POST['pseudo'])) {
        $errors[] = 'Le pseudo est doit comporter au moins 6 lettres/chiffres, au plus 20';
  } else {
    // compte le nb d'uilisateurs qui ont l'email venant du formulaire
    $req = 'SELECT count(*) FROM membre WHERE pseudo = ' . $pdo->quote($_POST['pseudo']);
    // $pdo->query() envoie la requête à la bdd et retourne un objet PDOStatement
    $stmt = $pdo->query($req);
    $nb = $stmt->fetchColumn();
    // si l'email existe déjà
    if ($nb != 0) {
      $errors[] = 'Ce pseudo existe déjà';
    }
  }

  if(empty($_POST['mdp'])) {
    $errors[] = 'Le mot de passe est obligatoire';
    // test du mdp sur l'expression régulière
  } elseif (!preg_match('/^[a-zA-Z0-9_-]{6,20}$/', $_POST['mdp'])) {
    $errors[] = 'Le mot de passe doit faire entre 6 et 20 caractères et ne contenir que des lettres, des chiffres les caractères _ et -';
  }

  if(empty($_POST['confirmation_mdp'])) {
    $errors[] = 'La confirmation du mot de passe est obligatoire';
  } elseif ($_POST['confirmation_mdp'] != $_POST['mdp']) {
    $error[] = 'Le mot de passe et sa confirmation ne sont pas identiques';
  }

  if(empty($_POST['civilite'])) {
    $errors[] = 'La civilité est obligatoire';
  }

  if(empty($_POST['nom'])) {
    $errors[] = 'Le nom est obligatoire';
  }

  if(empty($_POST['prenom'])) {
    $errors[] = 'Le prénom est obligatoire';
  }

  if(empty($_POST['email'])) {
    $errors[] = 'L\'email est obligatoire';
  // test de la validité de l'adresse mail
  } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'L\'email est invalide';
  // test de l'unicité de l'adresse mail
  } else {
    // compte le nb d'uilisateurs qui ont l'email venant du formulaire
    $req = 'SELECT count(*) FROM membre WHERE email = ' . $pdo->quote($_POST['email']);
    // $pdo->query() envoie la requête à la bdd et retourne un objet PDOStatement
    $stmt = $pdo->query($req);
    $nb = $stmt->fetchColumn();
    // si l'email existe déjà
    if ($nb != 0) {
      $errors[] = 'Cet email existe déjà';
    }
  }

  if(empty($_POST['telephone'])) {
    $errors[] = 'Le telephone est obligatoire';
  } else {
    if(!preg_match('/^[0-9]{10}$/', $_POST['telephone'])) {
      $errors[] = 'Le telephone est doit comporter 10 chiffres';
    }
  }

  // si tous les champs sont correctement remplis
  if(empty($errors)){
    // encrypte le mdp avec l'algo bcrypt
    $encodePassword = password_hash($mdp, PASSWORD_BCRYPT);

    $req = <<<EOS
    INSERT INTO membre (
      pseudo,
      mdp,
      civilite,
      nom,
      prenom,
      email,
      telephone,
      date_enregistrement
    ) VALUES (
      :pseudo,
      :mdp,
      :civilite,
      :nom,
      :prenom,
      :email,
      :telephone,
      now()
    )
EOS;
    $stmt =$pdo->prepare($req);
    $stmt->bindValue(':pseudo', $pseudo);
    $stmt->bindValue(':mdp', $encodePassword);
    $stmt->bindValue(':civilite', $civilite);
    $stmt->bindValue(':nom', $nom);
    $stmt->bindValue(':prenom', $prenom);
    $stmt->bindValue(':email', $email);
    $stmt->bindValue(':telephone', $telephone);
    $stmt->execute();
    // $success est utilisé dans le HTML pour afficher le message de confirmation
    $success = true;
  } // fin de if(empty($errors)){

} //fin de if(!empty($_POST)) {

include __DIR__ . '/layout/top.php';
?>

<h1>Inscription</h1>

<div class="col-md-6 col-md-offset-3">

  <?php
  // syntaxe longue avec : .. endif; à l'intérieur du HTML au lieu des accolade
  if (!empty($errors)) :
    ?>
    <div class="alert alert-danger">
      <strong>Le formulaire contient des erreurs</strong><br>
      <?= implode('<br>', $errors); ?>
    </div>
    <?php
  endif;

  if (isset($success)):
    ?>
    <div class="alert alert-success">
      <strong>Votre compte a été créé avec succès !</strong><br>
    </div>
    <?php
  endif;
  ?>

  <form method="post">

    <div class="form-group">
      <label>Pseudo</label>
      <input type="text" name="pseudo" value="<?= $pseudo; ?>" class="form-control" placeholder="Votre pseudo">
    </div>

    <div class="form-group">
      <label>Mot de passe</label>
      <input type="password" name="mdp" class="form-control" placeholder="Votre mot de passe">
    </div>

    <div class="form-group">
      <label>Confirmation du mot de passe</label>
      <input type="password" name="confirmation_mdp" class="form-control" placeholder="A nouveau votre mot de passe">
    </div>

    <div class="form-group">
      <label>Civilité</label>
      <select name="civilite" class="form-control">
        <option value="">--</option>
        <option value="M." <?php if ($civilite =='M.'){echo 'selected';} ?>>M.</option>
        <option value="Mme" <?php if ($civilite =='Mme'){echo 'selected';} ?>>Mme</option>
      </select>
    </div>

    <div class="form-group">
      <label>Nom</label>
      <input type="text" name="nom" value="<?= $nom; ?>" class="form-control" placeholder="Votre nom">
    </div>

    <div class="form-group">
      <label>Prénom</label>
      <input type="text" name="prenom" value="<?= $prenom; ?>" class="form-control" placeholder="Votre prénom">
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="text" name="email" value="<?= $email; ?>" class="form-control" placeholder="exemple@exemple.fr">
    </div>

    <div class="form-group">
      <label>Téléphone</label>
      <input type="text" name="telephone" value="<?= $telephone; ?>" class="form-control" placeholder="0123456789">
    </div>

    <button type="submit" class="btn btn-primary">Inscription</button>

  </form>
  <br>
</div>

<?php include __DIR__ . '/layout/bottom.php'; ?>
