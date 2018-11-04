<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();
$membre = $civilite = $nom = $prenom = $email = $telephone = $pseudo = $role = $membre = '';
$errors = [];

$req = ('SELECT * FROM membre');
$stmt = $pdo->query($req);
$membres = $stmt->fetchAll();


if (!empty($_POST)) {
  extract($_POST);
  sanitizePost();

  // test des valeurs du formulaires
  if(empty($_POST['pseudo'])) {
    $errors[] = 'Le pseudo est obligatoire';
  } else {
    $req = 'SELECT count(*) FROM membre WHERE pseudo = ' . $pdo->quote($_POST['pseudo']);

    // en modification, on exclut de la requête le membre que l'on est en train de modifier
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
    // en modification, on exclut de la requête l"email que l'on est en train de modifier
    if (isset($_GET['id'])) {
      $req .= ' AND id != ' . (int)$_GET['id'];
    }
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
    if(!preg_match('/^[0-9]{10}$/', $telephone)) {
      $errors[] = 'Le téléphone est doit comporter 10 chiffres';
    }
  }

  if(!empty($_POST['mdp']) && !preg_match('/^[a-zA-Z0-9_-]{6,20}$/', $_POST['mdp'])) {
    $errors[] = 'Le mot de passe doit faire entre 6 et 20 caractères et ne contenir que des lettres, des chiffres les caractères _ et -';
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
        // enregitrement du message de confirmation en session
        // puis redirection vers la liste
        setFlashMessage('Le membre est enregistré');
        header('Location: membres.php#top');
        die;
      }
    }
  // si le mdp est renseigné
  } else {
    // et si le formulaire est bien rempli
    if(empty($errors)) {
    $encodePassword = password_hash($mdp, PASSWORD_BCRYPT);
      if (isset($_GET['id'])) {  // modification du membre avec mdp
        $stmt = $pdo->prepare('UPDATE membre SET pseudo = :pseudo, civilite = :civilite, nom = :nom, prenom = :prenom, email = :email, telephone = :telephone, role = :role, mdp = :mdp WHERE id = :id');
        $stmt->bindValue(':id', $_GET['id']);
      } else {
        $stmt = $pdo->prepare('INSERT INTO membre (pseudo, mdp, civilite, nom, prenom, email, telephone, date_enregistrement, role) VALUES (:pseudo, :mdp, :civilite, :nom, :prenom, :email, :telephone, now(), :role)');
      }
      $stmt->bindValue(':pseudo', $pseudo);
      $stmt->bindValue(':mdp', $encodePassword);
      $stmt->bindValue(':civilite', $civilite);
      $stmt->bindValue(':nom', $nom);
      $stmt->bindValue(':prenom', $prenom);
      $stmt->bindValue(':email', $email);
      $stmt->bindValue(':telephone', $telephone);
      $stmt->bindValue(':role', $role);
      $stmt->execute();
      // enregitrement du message de confirmation en session
      // puis redirection vers la liste
      setFlashMessage('Le membre est enregistré');
      header('Location: membres.php#top');
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

include __DIR__ . '/../layout/top.php';
?>

<h1 id="top">Membres</h1>
<?php
displayFlashMessage();
?>
<div class="container-fluid">

  <table class="table">
    <caption>Gestion des membres</caption>
    <tr>
      <th>ID</th>
      <th>Pseudo</th>
      <th>Civilité</th>
      <th>Nom</th>
      <th>Prénom</th>
      <th>Email</th>
      <th>Téléphone</th>
      <th>Statut</th>
      <th>Date d'enregitrement</th>
      <th class="text-right">Actions</th>
    </tr>
    <?php
    foreach ($membres as $membre) :
    ?>
      <tr>
        <td><?= $membre['id']; ?></td>
        <td><?= $membre['pseudo']; ?></td>
        <td><?= $membre['civilite'] ;?></td>
        <td><?= $membre['nom']; ?></td>
        <td><?= $membre['prenom']; ?></td>
        <td><?= $membre['email']; ?></td>
        <td><?= formatTel($membre['telephone']) ;?></td>
        <td><?= $membre['role'] ;?></td>
        <td><?= date('d-m-Y H:i', strtotime($membre['date_enregistrement'])); ?></td>
        <td class="text-right">
          <a href="../profil.php?id=<?= $membre['id']; ?>" class="btn btn-default" title="Voir"><i class="fa fa-search"></i></a>
          <a href="membres.php?id=<?= $membre['id']; ?>#edit" class="btn btn-primary" title="Modifier"><i class="fa fa-pencil-square-o"></i></a>
          <a href="membre-delete.php?id=<?= $membre['id']; ?>" class="btn btn-danger"  title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce membre ?');"><i class="fa fa-trash"></i></a>
        </td>
      </tr>
    <?php
    endforeach;
    ?>
  </table>

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
          <label>Pseudo</label>
          <input type="text" name="pseudo" value="<?= $pseudo; ?>" class="form-control">
        </div>

        <div class="form-group">
          <label>Civilité</label>
          <select name="civilite" class="form-control">
            <option value="">--</option>
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

        <div class="form-group">
          <label>Rôle</label>
          <select name="role" class="form-control">
            <option value="">--</option>
            <option value="user" <?php if ($role == 'user'){echo 'selected';} ?>>Membre</option>
            <option value="admin" <?php if ($role == 'admin'){echo 'selected';} ?>>Admin</option>
          </select>
        </div>

        <div class="form-group">
          <label>Mot de passe</label>
          <input type="password" name="mdp" class="form-control" placeholder="Votre mot de passe">
        </div>

        <button type="submit" class="btn btn-primary">Enregistrer</button>

      </form>

    <br>
  </div>

</div>

<?php
include __DIR__ . '/../layout/bottom.php';
?>
