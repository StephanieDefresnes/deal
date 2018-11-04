<?php
require_once __DIR__ . '/include/init.php';

membreSecurity();

$errors = [];
$titre = $description_courte = $description_longue = $prix = $adresse = $code_postal = $ville = $region = $photoActuelle = $categorie_id = $region_id = $annonce = $publication = '';

if(!empty($_POST)) {  // le formulaire est envoyé
  // cf include/fonctions.php
  sanitizePost();
  // crée des variables à partir d'un tableau
  extract($_POST);

  if(empty($_POST['titre'])) {
    $errors[] = 'Le titre est obligatoire';
  }

  if(empty($_POST['description_courte'])) {
    $errors[] = 'Une description courte est obligatoire';
  }

  if(empty($_POST['prix'])) {
    $errors[] = 'Le prix est obligatoire';
  } else {
    if(!preg_match('/^[0-9]+[.]{0,1}[0-9]{0,2}$/', $prix)) {
      $errors[] = 'Le prix ne doit comporter que des chiffres et point (pas de virgule)';
    };
  }

  if(empty($_POST['categorie_id'])) {
    $errors[] = "La catégorie est obligatoire";
  }

  if (!empty($_FILES['photo']['tmp_name'])) {
      if ($_FILES['photo']['size'] > 1000000) {
          $errors[] = 'La photo ne doit pas faire'
              . ' plus de 1Mo'
          ;
      }
      $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

      if (!in_array($_FILES['photo']['type'], $allowedMimeTypes)) {
          $errors[] = 'La photo doit être une image GIF, JPG ou PNG'
          ;
      }
  }

  if(empty($_POST['adresse'])) {
    $errors[] = "L'adresse est obligatoire";
  }

  if(empty($_POST['code_postal'])) {
    $errors[] = "Le code postal est obligatoire";
  } else {
     if(!preg_match('/^[0-9]{5}$/', $code_postal)) {
       $errors[] = 'Le code postal est doit comporter 5 chiffres';
     };
   }

  if(empty($_POST['ville'])) {
    $errors[] = "La ville est obligatoire";
  }

  if(empty($_POST['region_id'])) {
    $errors[] = "La région est obligatoire";
  }

  // si tous les champs sont correctement remplis
  if(empty($errors)){

    // traitement de l'image
    if (!empty($_FILES['photo']['tmp_name'])) {
        // on retrouve l'extension du fichier à partir
        // de son nom original
        $dotPosition = strrpos($_FILES['photo']['name'], '.');
        $extension = substr($_FILES['photo']['name'], $dotPosition);
        $nomFichier = 'photo_' . substr(md5(time()), 0, 15) . $extension; //  pour créer un nom aléatoire

        // en modification, si le produit avait
        // déjà une photo, on la supprime
        if (!empty($photoActuelle)) {
            unlink(PHOTO_DIR . $photoActuelle);
        }

        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            PHOTO_DIR . $nomFichier
        );

    } else {
        $nomFichier = $photoActuelle;
    }

    if (isset($_GET['id'])) {  // modification
      $stmt = $pdo->prepare('UPDATE annonce SET titre = :titre, description_courte = :description_courte,  description_longue = :description_longue, prix = :prix, photo = :photo, region_id = :region_id, ville = :ville, adresse = :adresse, code_postal = :code_postal, categorie_id = :categorie_id, publication = :publication WHERE id = :id');
      $stmt->bindValue(':id', $_GET['id']);
    } else {  // création
    $req = <<<EOS
    INSERT INTO annonce (
      titre,
      description_courte,
      description_longue,
      prix,
      photo,
      region_id,
      ville,
      adresse,
      code_postal,
      date_enregistrement,
      categorie_id,
      membre_id,
      publication
    ) VALUES (
      :titre,
      :description_courte,
      :description_longue,
      :prix,
      :photo,
      :region_id,
      :ville,
      :adresse,
      :code_postal,
      now(),
      :categorie_id,
      :membre_id,
      :publication
    )
EOS;
    $stmt = $pdo->prepare($req);
    $stmt->bindValue(':membre_id', $_SESSION['membre']['id']);
  } // fin de if(empty($errors)){


    $stmt->bindValue(':titre', $titre);
    $stmt->bindValue(':description_courte', $description_courte);
    if (!empty($description_longue)) {
      $stmt->bindValue(':description_longue', $description_longue);
    } else {
      $stmt->bindValue(':description_longue', null, PDO::PARAM_NULL);
    }
    $stmt->bindValue(':prix', $prix);
    if (!empty($nomFichier)) {
      $stmt->bindValue(':photo', $nomFichier);
    } else {
      $stmt->bindValue(':photo', null, PDO::PARAM_NULL);
    }
    $stmt->bindValue(':region_id', $region_id);
    $stmt->bindValue(':ville', $ville);
    $stmt->bindValue(':adresse', $adresse);
    $stmt->bindValue(':code_postal', $code_postal);
    $stmt->bindValue(':categorie_id', $categorie_id);
    $stmt->bindValue(':publication', $publication);
    $stmt->execute();

    setFlashMessage('L\'annonce est enregistré');
    header('Location: index.php');
    die;
  }

} elseif (isset($_GET['id'])) {
  // s'il y a une id dans l'url et qu'il n'y a pas de retour de formulaire
  // on va chercher le'annonce en bdd
  $req = 'SELECT * FROM annonce WHERE id=' . (int)$_GET['id'];
  $stmt = $pdo->query($req);
  $annonce = $stmt->fetch();
  extract($annonce);
  $photoActuelle = $annonce['photo'];
}

// récupération des catégories
$reqCat = 'SELECT * FROM categorie';
$stmtCat = $pdo->query($reqCat);
$categories = $stmtCat->fetchAll();

// récupération des régions
$reqRegion = 'SELECT * FROM region';
$stmtReg = $pdo->query($reqRegion);
$regions = $stmtReg->fetchAll();

include __DIR__ . '/layout/top.php';
?>

<h1>Dépôt d'annonce</h1>
<?php
displayFlashMessage();

if (!empty($errors)) :
?>
    <div class="alert alert-danger">
        <strong>Le formulaire contient des erreurs</strong>
        <br>
        <?= implode('<br>', $errors); ?>
    </div>
<?php
endif;
?>

<div class="container-fluid">

  <form method="post" enctype="multipart/form-data">

    <div class="row">

      <div class="col-md-6 col-xs-12">

        <div class="form-group">
          <label>Titre</label>
          <input type="text" name="titre" value="<?= $titre; ?>" class="form-control" placeholder="Titre de l'annonce">
        </div>

        <div class="form-group">
          <label>Description courte</label>
          <textarea type="text" name="description_courte" class="form-control" placeholder="Description courte de votre annonce"><?= $description_courte; ?></textarea>
        </div>

        <div class="form-group">
          <label>Description (suite)</label>
          <textarea type="text" name="description_longue" class="form-control" placeholder="Suite de la description de votre annonce"><?= $description_longue; ?></textarea>
        </div>

        <div class="form-group">
          <label>Prix</label>
          <input type="text" name="prix" value="<?= $prix; ?>" class="form-control" placeholder="Prix figurant dans l'annonce">
        </div>

        <div class="form-group">
          <label>Catégorie</label>
          <select name="categorie_id" class="form-control">
            <option value="">Toutes les catégories</option>
            <?php
            foreach ($categories as $categorie) :
            ?>
            <option value="<?= $categorie['id']; ?>"<?php if ($categorie_id == $categorie['id']){echo ' selected';} ?>><?= $categorie['titre']; ?> : <?= $categorie['mots_cles']; ?></option>
              <?php
            endforeach;
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>Photo</label>
          <input type="file" name="photo">
        </div>

        <input type="hidden" name="photoActuelle" value="<?= $photoActuelle; ?>">
        <?php
        if (!empty($photoActuelle)) :
        ?>
          <p><img src="<?= PHOTO_WEB . $photoActuelle; ?>" height="150px"></p>
        <?php
        endif;
        ?>

      </div>

      <div class="col-md-6 col-xs-12">

        <div class="form-group">
          <label>Adresse</label>
          <textarea type="text" name="adresse" class="form-control" placeholder="L'adresse figurant dans l'annonce"><?= $adresse; ?></textarea>
        </div>

        <div class="form-group">
          <label>Code Postal</label>
          <input type="text" name="code_postal" value="<?= $code_postal; ?>" class="form-control" placeholder="Le code postal figurant dans l'annonce">
        </div>

        <div class="form-group">
          <label>Ville</label>
          <input type="text" name="ville" value="<?= $ville; ?>" class="form-control" placeholder="Le ville figurant dans l'annonce">
        </div>

        <div class="form-group">
          <label>Région</label>
          <select name="region_id" class="form-control">
            <option value="">Toutes les régions</option>
            <?php
            foreach ($regions as $region) :
            ?>
            <option value="<?= $region['id']; ?>"<?php if ($region_id == $region['id']){echo ' selected';} ?>><?= $region['nom']; ?></option>
            <?php
            endforeach;
            ?>
          </select>
        </div>

        <div class="form-group">
          <label>Publication</label>
          <select name="publication" class="form-control">
            <option value="active" <?php if ($publication == 'active'){echo 'selected';} ?>>Active</option>
            <option value="inactive" <?php if ($publication == 'inactive'){echo 'selected';} ?>>Inactive (lorsque l'annonce n'est plus d'actualité)</option>
          </select>
        </div>

      </div>

    </div>

    <div class="text-center">
      <button type="submit" class="btn btn-primary">Enregistrer</button>
      <a href="<?= $_SERVER['HTTP_REFERER']; ?>" class="btn btn-default">Retour</a>
    </div>

  </form>
  <br>
</div>

<?php
include __DIR__ . '/layout/bottom.php';
?>
