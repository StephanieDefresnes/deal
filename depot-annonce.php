<?php
require_once __DIR__ . '/include/init.php';
// membreSecurity();
$titre = $descr_courte = $descr_longue = $prix = $civilite = $ville = $adresse = $codepostal = '';


include __DIR__ . '/layout/top.php';
?>

<h1>Dépôt d'annonce</h1>

<div class="container-fluid">

  <form method="post">
    <div class="col-md-6 col-xs-12">

      <div class="form-group">
        <label>Titre</label>
        <input type="text" name="titre" value="<?= $titre; ?>" class="form-control" placeholder="Titre de l'annonce">
      </div>

      <div class="form-group">
        <label>Description courte</label>
        <textarea type="text" name="descr_courte" value="<?= $descr_courte; ?>" class="form-control" placeholder="Description courte de votre annonce"></textarea>
      </div>

      <div class="form-group">
        <label>Description longue</label>
        <textarea type="text" name="descr_longue" value="<?= $descr_longue; ?>" class="form-control" placeholder="Description longue de votre annonce"></textarea>
      </div>

      <div class="form-group">
        <label>Prix</label>
        <input type="text" name="titre" value="<?= $prix; ?>" class="form-control" placeholder="Prix figurant dans l'annonce">
      </div>

      <div class="form-group">
        <label>Catégorie</label>
        <select name="categorie" class="form-control">
          <option value="" <?php if ($civilite =='categorie'){echo 'selected';} ?>>Toutes les catégories</option>
        </select>
      </div>

    </div>

    <div class="col-md-6 col-xs-12">

      <div class="form-group">
        <label>Photo</label>
        <input type="file" name="nom" style="height:6em"/>
      </div>

      <div class="form-group">
        <label>Adresse</label>
        <textarea type="text" name="adresse" value="<?= $adresse; ?>" class="form-control" placeholder="Adresse"></textarea>
      </div>

      <div class="form-group">
        <label>Code Postal</label>
        <input type="text" name="pays" value="<?= $codepostal; ?>" class="form-control" placeholder="Code Postal">
      </div>

      <div class="form-group">
        <label>Ville</label>
        <input type="text" name="pays" value="<?= $ville; ?>" class="form-control" placeholder="Ville">
      </div>

      <button type="submit" class="btn btn-primary">Enregistrer</button>

    </div>

  </form>

</div>

<?php
include __DIR__ . '/layout/bottom.php';
?>
