<?php
require_once __DIR__ . '/include/init.php';
include __DIR__ . '/layout/top.php';

// $annonce = $region = $categorie = $membre = '';
$varTri = 'a.date_enregistrement DESC';
$prixMin = $prixMax = '';
extract($_POST);

// selecteur central d'affichage annonces
$req = 'SELECT a.*, m.pseudo pseudo_membre, m.id id_membre, c.titre titre_categorie, r.nom nom_region, ROUND(AVG(note), 1) moyenne_note
        FROM annonce a
        JOIN membre m ON m.id = a.membre_id
        JOIN categorie c ON c.id = a.categorie_id
        JOIN region r ON r.id = a.region_id
        LEFT JOIN note n ON n.membre_id2 = m.id
        WHERE a.publication = \'active\'';
          if (!empty($_GET['categorie'])) {
            $req .= ' AND c.id = ' . $_GET['categorie'];
          }
          if (!empty($_GET['region'])) {
            $req .= ' AND r.id = ' . $_GET['region'];
          }
          if (!empty($_GET['membre'])) {
            $req .= ' AND m.id = ' . $_GET['membre'];
          }
          if (!empty($_GET['prixMin'])) {
            $req .= ' AND a.prix >= ' . $_GET['prixMin'];
          }
          if (!empty($_GET['prixMax'])) {
            $req .= ' AND a.prix <= ' . $_GET['prixMax'];
          }
          $req .= ' GROUP BY a.id
                    ORDER BY ' . $varTri
          ;
$reqFiltre = $pdo->prepare($req);
$reqFiltre->execute();
$annonces = $reqFiltre->fetchAll();

// récupération de la liste des catégories
$reqCat = 'SELECT * FROM categorie';
$stmtCat = $pdo->query($reqCat);
$categories = $stmtCat->fetchAll();

// récupération de la liste des régions
$reqRegion = 'SELECT * FROM region';
$stmtReg = $pdo->query($reqRegion);
$regions = $stmtReg->fetchAll();

// récupération de la liste des membres
$reqMembre = 'SELECT * FROM membre';
$stmtMemb = $pdo->query($reqMembre);
$membres = $stmtMemb->fetchAll();

?>

<h1>Accueil</h1>
<?php
displayFlashMessage();
?>
<div class="row">

  <div class="col-md-3 col-xs-12">

    <form>

      <div class="form-group">
        <label>Catégorie</label>
        <select name="categorie" class="form-control">
          <option value="">Tous les catégories</option>
          <?php
          foreach ($categories as $categorie) :
          ?>
          <option value="<?= $categorie['id']; ?>" <?php if (isset($_GET['categorie']) && $_GET['categorie'] == $categorie['id']){echo 'selected';}?>><?= $categorie['titre']; ?></option>
          <?php
          endforeach;
          ?>
        </select>
      </div>

      <div class="form-group">
        <label>Région</label>
        <select name="region" class="form-control">
          <option value="">Tous les régions</option>
          <?php
          foreach ($regions as $region) :
          ?>
          <option value="<?= $region['id']; ?>" <?php if (isset($_GET['region']) && $_GET['region'] == $region['id']){echo 'selected';}?>><?= $region['nom']; ?></option>
          <?php
          endforeach;
          ?>
        </select>
      </div>

      <div class="form-group">
        <label>Membre</label>
        <select name="membre" class="form-control">
          <option value="">Tous les membres</option>
          <?php
          foreach ($membres as $membre) :
          ?>
          <option value="<?= $membre['id']; ?>" <?php if (isset($_GET['membre']) && $_GET['membre'] == $membre['id']){echo 'selected';}?>><?= $membre['pseudo']; ?></option>
          <?php
          endforeach;
          ?>
        </select>
      </div>

      <div class="form-group">
        <label>Prix</label>
        <div class="row">
          <div class="col-md-6">
            <input type="text" name="prixMin" value="<?= $prixMin; ?>" class="form-control" placeholder="Prix Minimum">
          </div>
          <div class="col-md-6">
            <input type="text" name="prixMax"  value="<?= $prixMax; ?>" class="form-control" placeholder="Prix Maximum">
          </div>
        </div>
      </div>

      <div class="text-right">
        <button type="submit" class="btn btn-primary">Filtrer</button>
      </div>

    </form>

  </div>

  <div class="col-md-7 col-md-offset-1 col-xs-12">
    <form method="post">
      <div class="form-group col-md-6">
        <select name="varTri" class="form-control" onchange="this.form.submit()">
          <option value="<?= 'a.prix'; ?>" <?php if ($varTri == 'a.prix'){echo 'selected';}?>>Trier par prix (du moins cher au plus cher)</option>
          <option value="<?= 'a.prix DESC'; ?>" <?php if ($varTri == 'a.prix DESC'){echo 'selected';}?>>Trier par prix (du plus cher au moins cher)</option>
          <option value="<?= 'a.date_enregistrement'; ?>" <?php if ($varTri == 'a.date_enregistrement'){echo 'selected';}?>>Trier par date (de la plus ancienne à la plus récente)</option>
          <option value="<?= 'a.date_enregistrement DESC'; ?>" <?php if ($varTri == 'a.date_enregistrement DESC'){echo 'selected';}?>>Trier par date (de la plus récente à la plus ancienne)</option>
          <option value="<?= 'moyenne_note DESC'; ?>" <?php if ($varTri == 'moyenne_note DESC'){echo 'selected';}?>>Les meilleurs vendeurs en premier</option>
        </select>
      </div>
    </form>

    <div class="col-md-12"><hr>

      <?php
      if (empty($annonces)){
      ?>
      <div class="alert alert-warning">
        Aucune annonce ne correspond à votre recherche
      </div>
      <?php
      }
      ?>

      <div class="row">

        <?php
        foreach ($annonces as $annonce) :
          $src = (!empty($annonce['photo'])) ? PHOTO_WEB . $annonce['photo'] : PHOTO_DEFAULT;
        ?>

        <div class="col-md-4 text-center">
          <a href="annonce.php?id=<?= $annonce['id']; ?>">
            <img src="<?= $src; ?>" style="height:100px" alt="annonce_<?= $annonce['id']; ?>">
          </a>
        </div>

        <div class="col-md-7 col-md-offset-1">

          <h4><a href="annonce.php?id=<?= $annonce['id']; ?>"><?= $annonce['titre']; ?></a></h4>
          <p><?= nl2br($annonce['description_courte']); ?></p>

          <div class="row">
            <div class="col-md-6">
              <p>
                <i class="fa fa-user"></i> <?= $annonce['pseudo_membre']; ?>
                <?php
                if ($annonce['moyenne_note'] > 0): // afficher s'il y a au moins une note
                  echo ' - Note : ' . $annonce['moyenne_note'] . ' / 5';
                endif;
                ?>
              </p>
            </div>
            <div class="col-md-6 text-right">
              <p><?= number_format($annonce['prix'], 2, ',', ' '); ?> <strong>€</strong></p>
            </div>
          </div>

        </div><br class="clear"><hr>

        <?php
        endforeach;
        ?>

      </div>

    </div>

  </div>

</div>

<?php
include __DIR__ . '/layout/bottom.php';
?>
