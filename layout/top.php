<!DOCTYPE html>
<html lang="zxx">
  <head>
    <meta charset="utf-8">
    <title>DEAL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ekko-lightbox/5.3.0/ekko-lightbox.css">
    <link href="<?=RACINE_WEB; ?>kartik-v-bootstrap-star-rating/css/star-rating.css" media="all" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="<?=RACINE_WEB; ?>css/style.css">
  </head>
  <body>

    <?php
    if(isUserAdmin()):
    ?>
    <nav class="navbar navbar-inverse">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-admin-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Administration</a>
        </div>
        <div class="collapse navbar-collapse" id="bs-admin-navbar-collapse-1">
        <ul class="nav navbar-nav navbar-right">
          <li>
            <a href="<?=RACINE_WEB; ?>admin/annonces.php">Gestion annonces</a>
          </li>
          <li>
            <a href="<?=RACINE_WEB; ?>admin/categories.php">Gestion catégories</a>
          </li>
          <li>
            <a href="<?=RACINE_WEB; ?>admin/membres.php">Gestion membres</a>
          </li>
          <li>
            <a href="<?=RACINE_WEB; ?>admin/commentaires.php">Gestion commentaires</a>
          </li>
          <li>
            <a href="<?=RACINE_WEB; ?>admin/notes.php">Gestion notes</a>
          </li>
          <li>
            <a href="<?=RACINE_WEB; ?>admin/statistiques.php">Statistiques</a>
          </li>
        </ul>
      </div>
      </div>
    </nav>
    <?php
    endif;
    ?>

    <nav class="navbar navbar-default">
      <div class="container-fluid">
        <!-- Brand and toggle get grouped for better mobile display -->
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-user-navbar-collapse-1" aria-expanded="false">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?=RACINE_WEB; ?>">DEAL</a>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse" id="bs-user-navbar-collapse-1">
          <ul class="nav navbar-nav">
            <li><a href="#" data-toggle="modal" data-target="#contact">Contact</a></li>
            <li><a href="#" data-toggle="modal" data-target="#qui">Qui Nous Sommes</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li class="dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><i class="fa fa-user"></i>
                <?php
                if (isUserConnected()) :
                ?>
                <?= getUserFullName() ?>
                <?php
                else :
                ?>
                Espace Membre
                <?php
                endif;
                ?>
                <span class="caret"></span></a>
              <ul class="dropdown-menu">
                <?php
                if (isUserConnected()) :
                ?>
                <li>
                  <a href="<?= RACINE_WEB; ?>profil.php?id=<?= $_SESSION['membre']['id']; ?>"><i class="fa fa-user"></i> Profil</a>
                </li>
                <li>
                  <a href="<?= RACINE_WEB; ?>annonce-edit.php"><i class="fa fa-plus-circle"></i> Dépôt d'annonce</a>
                </li>
                <li role="separator" class="divider"></li>
                <li>
                  <a href="<?= RACINE_WEB; ?>deconnexion.php"><i class="fa fa-sign-out"></i> Déconnexion</a>
                </li>
                <?php
                else :
                ?>
                <li>
                  <a href="<?= RACINE_WEB; ?>connexion.php">Connexion</a>
                </li>
                <li>
                  <a href="<?= RACINE_WEB; ?>inscription.php">Inscription</a>
                </li>
                <?php
                endif;
                ?>
              </ul>
            </li>
          </ul>
        </div><!-- /.navbar-collapse -->
      </div><!-- /.container-fluid -->
    </nav>

    <!-- Modal -->
    <div class="modal fade" id="contact" tabindex="-1" role="dialog" aria-labelledby="contact" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Contacter DEAL</h4>
          </div>
          <div class="modal-body">
            <?php include __DIR__ . '/contact-site.php'; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-remove"></i></button>
          </div>
        </div>
      </div>
    </div>
    <div class="modal fade" id="qui" tabindex="-1" role="dialog" aria-labelledby="qui" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title">Qui nous sommes</h4>
          </div>
          <div class="modal-body">
            <p>DEAL est une société permettant aux internautes de déposer des annonces en lignes.</p>
            <p><strong>Raison social :</strong><br> DEAL</p>
            <p><strong>Adresse :</strong><br>50 rue de Paradis, 75010 Paris, France</p>
            <p><strong>Mission :</strong><br>La société basée sur un système d'annoonces en lignes, est spécialsée dans la mise en relation entre les internautes.</p>
            <p><strong>Périmètre géographique de l'activité :</strong><br>National (France entière)</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-remove"></i></button>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid">
