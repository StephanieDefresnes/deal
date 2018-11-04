<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();
$annonce = $region = $categorie = '';
$varTri = 'a.id';
extract($_POST);

$req = 'SELECT a.*, m.pseudo pseudo_membre, c.titre titre_categorie, r.nom nom_region
        FROM annonce a
        LEFT JOIN membre m ON m.id = a.membre_id
        LEFT JOIN categorie c ON c.id = a.categorie_id
        LEFT JOIN region r ON r.id = a.region_id
        GROUP BY a.id
        ORDER BY ' . $varTri;
$reqFiltre = $pdo->prepare($req);
$reqFiltre->execute();
$annonces = $reqFiltre->fetchAll();

include __DIR__ . '/../layout/top.php';
?>

<h1>Annonces</h1>
<?php
displayFlashMessage();
?>
<div class="container-fluid">

  <form method="post">
    <div class="form-group col-md-3">
      <select name="varTri" class="form-control" onchange="this.form.submit()">
        <option value="<?= 'a.id'; ?>" <?php if ($varTri == 'a.id'){echo 'selected';}?>>Trier par id croissant</option>
        <option value="<?= 'a.id DESC'; ?>" <?php if ($varTri == 'a.id DESC'){echo 'selected';}?>>Trier par id décroissant</option>
        <option value="<?= 'a.titre'; ?>" <?php if ($varTri == 'a.titre'){echo 'selected';}?>>Trier par titre</option>
        <option value="<?= 'a.titre DESC'; ?>" <?php if ($varTri == 'a.titre DESC'){echo 'selected';}?>>Trier par titre décroissant</option>
        <option value="<?= 'a.prix'; ?>" <?php if ($varTri == 'a.prix'){echo 'selected';}?>>Trier par prix</option>
        <option value="<?= 'a.prix DESC'; ?>" <?php if ($varTri == 'a.prix DESC'){echo 'selected';}?>>Trier par prix décroissant</option>
        <option value="<?= 'c.titre'; ?>" <?php if ($varTri == 'c.titre'){echo 'selected';}?>>Trier par catégorie</option>
        <option value="<?= 'c.titre DESC'; ?>" <?php if ($varTri == 'c.titre DESC'){echo 'selected';}?>>Trier par catégorie décroissante</option>
      </select>
    </div>
  </form>

  <table class="table">
    <caption>Gestion des annonces</caption>
    <tr>
      <th>ID</th>
      <th>Titre</th>
      <th>Decription Courte</th>
      <th>Description Longue</th>
      <th>Prix</th>
      <th>Photo</th>
      <th>Région</th>
      <th>Adresse</th>
      <th>CP</th>
      <th>Ville</th>
      <th>Membre</th>
      <th>Catégorie</th>
      <th>Date d'enregistrement</th>
      <th>Publication</th>
      <th class="text-right">Actions</th>
    </tr>
    <?php
    foreach ($annonces as $annonce) :
      $src = (!empty($annonce['photo'])) ? PHOTO_WEB . $annonce['photo'] : PHOTO_DEFAULT;
    ?>
      <tr>
        <td><?= $annonce['id']; ?></td>
        <td><?= $annonce['titre']; ?></td>
        <td><?= substr($annonce['description_courte'],0,50); ?>...</td>
        <td><?= substr($annonce['description_longue'],0,50); ?>...</td>
        <td><?= $annonce['prix']; ?> €</td>
        <td><a href="<?= $src; ?>" data-toggle="lightbox">
          <img src="<?= $src; ?>" style="width:100px" alt="annonce_<?= $annonce['id']; ?>">
        </a></td>
        <td><?= $annonce['nom_region']; ?></td>
        <td><?= $annonce['adresse']; ?></td>
        <td><?= $annonce['code_postal']; ?></td>
        <td><?= $annonce['ville']; ?></td>
        <td><?= $annonce['pseudo_membre']; ?></td>
        <td><?= $annonce['titre_categorie']; ?></td>
        <td><?= date('d-m-Y H:i', strtotime($annonce['date_enregistrement'])); ?></td>
        <td><?= $annonce['publication']; ?></td>
        <td class="text-right">
          <a href="../annonce.php?id=<?= $annonce['id']; ?>" class="btn btn-default" title="Voir"><i class="fa fa-search"></i></a>
          <a href="../annonce-edit.php?id=<?= $annonce['id']; ?>" class="btn btn-primary" title="Modifier"><i class="fa fa-pencil-square-o"></i></a>
          <a href="../annonce-delete.php?id=<?= $annonce['id']; ?>" class="btn btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');"><i class="fa fa-trash"></i></a>
        </td>
      </tr>
    <?php
    endforeach;
    ?>
  </table>

</div>

<?php
include __DIR__ . '/../layout/bottom.php';
?>
