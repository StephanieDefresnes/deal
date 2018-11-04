<?php
require_once __DIR__ . '/include/init.php';

membreSecurity();

$civilite = $nom = $prenom = $email = $telephone = $pseudo = $role = $membre = $id = '';

$errors = [];

if (!empty($_POST)) {
  extract($_POST);
  sanitizePost();

  // test des valeurs du formulaires
  if(empty($_POST['pseudo'])) {
    $errors[] = 'Le pseudo est obligatoire';
  } else {
    $req = 'SELECT count(*) FROM membre WHERE pseudo = ' . $pdo->quote($_POST['pseudo']);

    // en modification, on exclut de la requête la catégorie que l'on est en train de modifier
    if (isset($_GET['id'])) {
      $req .= ' AND id != ' . (int)$_GET['id'];
    }

    $stmt = $pdo->query($req);
    $nb = $stmt->fetchColumn();

    if ($nb != 0) {
      $errors[] = "Le pseudo  $pseudo existe déjà";
    }
  }

  if(empty($_POST['civilite'])) {
    $errors[] = 'Le genre est obligatoire';
  }

  if(empty($_POST['nom'])) {
    $errors[] = 'Le nom est obligatoire';
  }

  if(empty($_POST['prenom'])) {
    $errors[] = 'Le prénom est obligatoire';
  }

  if(empty($_POST['email'])) {
    $errors[] = 'Le email est obligatoire';
  } else {
    $req = 'SELECT count(*) FROM membre WHERE email = ' . $pdo->quote($_POST['email']);

    // en modification, on exclut de la requête l"email que l'on est en train de modifier
    if (isset($_GET['id'])) {
      $req .= ' AND id != ' . (int)$_GET['id'];
    }

    $stmt = $pdo->query($req);
    $nb = $stmt->fetchColumn();

    if ($nb != 0) {
      $errors[] = "Le email $email existe déjà";
    }
  }

  if(empty($_POST['telephone'])) {
    $errors[] = 'Le telephone est obligatoire';
  } else {
    if(!preg_match('/^[0-9]{10}$/', $telephone)) {
      $errors[] = 'Le telephone est doit comporter 10 chiffres';
    };
  }

  // fin du test

  // si le mdp et vide
  if(empty($_POST['mdp'])) {

    // et si le formulaire est bien rempli
    if(empty($errors)) {
      if (isset($_GET['id'])) {  // modification du membre sans mdp
        $stmt = $pdo->prepare('UPDATE membre SET pseudo = :pseudo, civilite = :civilite, nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, role = :role WHERE id = :id');
        $stmt->bindValue(':pseudo', $pseudo);
        $stmt->bindValue(':civilite', $civilite);
        $stmt->bindValue(':nom', $nom);
        $stmt->bindValue(':prenom', $prenom);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':telephone', $telephone);
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':id', $_GET['id']);
        $stmt->execute();
      }
      // enregitrement du message de confirmation en session
      // puis redirection vers la liste
      setFlashMessage('Le membre est enregistré');
      header('Location: profil.php?id=' . (int)$_GET['id']);
      die;
    }
  // si mdp est bien renseigné
  } elseif (preg_match('/^[a-zA-Z0-9_-]{6,20}$/', $_POST['mdp'])) {
    // et si le formulaire est bien rempli
    if(empty($errors)) {
      if (isset($_GET['id'])) {  // modification du membre avec mdp
        $stmt = $pdo->prepare('UPDATE membre SET pseudo = :pseudo, civilite = :civilite, nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, role = :role WHERE id = :id');
        $stmt->bindValue(':pseudo', $pseudo);
        $stmt->bindValue(':civilite', $civilite);
        $stmt->bindValue(':nom', $nom);
        $stmt->bindValue(':prenom', $prenom);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':telephone', $telephone);
        $stmt->bindValue(':role', $role);
        $stmt->bindValue(':mdp', $encodePassword);
        $stmt->bindValue(':id', $_GET['id']);
        $stmt->execute();
      }
      // enregitrement du message de confirmation en session
      // puis redirection vers la liste
      setFlashMessage('Le membre est enregistré');
      header('Location: profil.php?id=' . (int)$_GET['id']);
      die;
    }
  }
} elseif (isset($_GET['id'])) {
  // s'il y a une id dans l'url et qu'il n'y a pas de retour de formulaire
  // on va chercher le membre en bdd
  $req = 'SELECT * FROM membre WHERE id=' . (int)$_GET['id'];
  $stmt = $pdo->query($req);
  $membre = $stmt->fetch();
  extract($membre);
}

include __DIR__ . '/layout/top.php';
?>

<h1>Edition membre</h1>
<div class="col-md-6 col-md-offset-3">
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
      <label>Pseudo</label>
      <input type="text" name="pseudo" value="<?= $pseudo; ?>" class="form-control">
    </div>

    <div class="form-group">
      <label>Civilité</label>
      <select name="civilite" class="form-control">
        <option value="M." <?php if ($civilite == 'M.'){echo 'selected';} ?>>M.</option>
        <option value="Mme" <?php if ($civilite == 'Mme'){echo 'selected';} ?>>Mme</option>
      </select>
    </div>

    <div class="form-group">
      <label>Nom</label>
      <input type="text" name="nom" value="<?= $nom; ?>" class="form-control">
    </div>

    <div class="form-group">
      <label>Prénom</label>
      <input type="text" name="prenom" value="<?= $prenom; ?>" class="form-control">
    </div>

    <div class="form-group">
      <label>Email</label>
      <input type="text" name="email" value="<?= $email; ?>" class="form-control">
    </div>

    <div class="form-group">
      <label>Téléphone</label>
      <input type="text" name="telephone" value="<?= $telephone; ?>" class="form-control">
    </div>

    <?php
    if (isUserConnected() && $_SESSION['membre']['id'] == $id):
    ?>

      <div class="form-group">
        <label>Mot de passe</label>
        <input type="password" name="mdp" class="form-control" placeholder="Votre mot de passe">
      </div>

      <div class="form-group">
        <label>Confirmation du mot de passe</label>
        <input type="password" name="confirmation_mdp" class="form-control" placeholder="A nouveau votre mot de passe">
      </div>

    <?php
    endif;
    ?>

    <button type="submit" class="btn btn-primary">Enregistrer</button>
    <a href="profil.php?id=<?= (int)$_GET['id']; ?>" class="btn btn-default">Retour</a>
  </form>
  <br>
</div>

<?php
include __DIR__ . '/layout/bottom.php';
?>
