<?php
require_once __DIR__ . '/include/init.php';

$commentaire = $email_membre = $note = $avis = $moyenne_note = '';
$errors = [];

//récupération de l'annonce et des données membre dans la BDD
$req = $pdo->prepare('SELECT a.*, m.id id_membre, m.email email_membre, m.pseudo pseudo_membre, m.telephone telephone_membre, c.titre titre_categorie
        FROM membre m
        JOIN annonce a ON m.id = a.membre_id
        JOIN categorie c ON a.categorie_id = c.id
        WHERE a.id = ?');
$req->execute(array((int)$_GET['id']));
$annonce = $req->fetch();

if ($annonce === false) {
  header('Location: 404.php');
}

extract($annonce);

// url image
$src = (!empty($annonce['photo'])) ? PHOTO_WEB . $annonce['photo'] : PHOTO_DEFAULT;


// Commentaires
// formulaire de dépôt de commentaire
if (isset($_POST['commentaire'])) {
  extract($_POST);
  sanitizePost();

  // test des valeurs du formulaires
  if(empty($_POST['commentaire'])) {
    $errors[] = 'Le commentaire est obligatoire';
  }

  // si le formulaire est bien rempli
  if(empty($errors)) {
    $stmt = $pdo->prepare('INSERT INTO commentaire (commentaire, date_enregistrement, membre_id, annonce_id, commentaire_id) VALUES (:commentaire, now(), :membre_id, :annonce_id, :commentaire_id)');
    $stmt->bindValue(':commentaire', $commentaire);
    $stmt->bindValue(':membre_id', $_SESSION['membre']['id']);
    $stmt->bindValue(':annonce_id', $_GET['id']);
    $stmt->bindValue(':commentaire_id', $commentaire_id);
    $stmt->execute();
    // afficher le message de confirmation
    $successCommentaire = true;
  }
}

// affichage des commentaires laissés sur l'annonce
$reqCommentaire = $pdo->prepare('SELECT c.*, m.pseudo pseudo_annonce, a.id id_annonce
      FROM commentaire c
      LEFT JOIN membre m ON c.membre_id = m.id
      LEFT JOIN annonce a ON c.membre_id = a.id
      WHERE c.annonce_id = ?
      ORDER BY c.id
      ');
$reqCommentaire->execute(array((int)$_GET['id']));
$commentaires = $reqCommentaire->fetchAll();
$nbCommentaires = count($commentaires);

// affichage des réponses laissées sur les commentaires
if (isUserConnected()){
  $reqCptNote = $pdo->prepare('SELECT COUNT(*)
                FROM note
                WHERE membre_id1 = ' . $_SESSION['membre']['id']
                . ' AND membre_id2 = (SELECT membre_id FROM annonce WHERE id = ?)
                ');
  $reqCptNote->execute(array((int)$_GET['id']));
  $nbNoteMembre = $reqCptNote->fetchColumn();
}

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
    if ($nbNoteMembre == 0) {  // création
      $stmt = $pdo->prepare('INSERT INTO note (note, avis, date_enregistrement, membre_id1, membre_id2) VALUES (:note, :avis, now(), :membre_id1, :membre_id2)');
      $stmt->bindValue(':membre_id1', $_SESSION['membre']['id']);;
      $stmt->bindValue(':membre_id2', $id_membre);
    } else {  // modification
      $stmt = $pdo->prepare('UPDATE note SET note = :note, avis = :avis
                       WHERE membre_id1 = ' . $_SESSION['membre']['id']
                      . ' AND membre_id2 = (SELECT membre_id FROM annonce WHERE id = ' . $_GET['id'] . ')
                      ');
    }
    $stmt->bindValue(':note', $note);
    $stmt->bindValue(':avis', $avis);
    $stmt->execute();
    // afficher le message de confirmation
    $successNote = true;
  }
}

// récupération des notes et de l'auteur dans la BDD
$reqNote = $pdo->prepare('SELECT n.*, m.pseudo pseudo_annonce
      FROM note n
      LEFT JOIN membre m ON n.membre_id1 = m.id
      WHERE n.membre_id2 = ?
      ORDER BY n.id
      ');
$reqNote->execute(array((int)$_GET['id']));
$notes = $reqNote->fetchAll();
$nbNotes = count($notes);

// note moyenne de l'auteur
$noteMoyenne = $pdo->prepare('SELECT AVG(note) moyenne_note FROM note WHERE membre_id2 = ?');
$noteMoyenne->execute(array($id_membre));
$moyenne_note = $noteMoyenne->fetchColumn();

// autres annonces de la categorie
$reqAutres = $pdo->prepare('SELECT a.*, c.titre AS titre_categorie
                          FROM annonce a
                          JOIN categorie c ON a.categorie_id = c.id
                          WHERE categorie_id = (SELECT categorie_id
                                                FROM annonce a WHERE id = ' . $_GET['id'] . ')
                          AND a.id != ' . $_GET['id'] . ' LIMIT 4
                          ');
$reqAutres->execute(array((int)$_GET['id']));
$autres = $reqAutres->fetchAll();
$nbAutres = count($autres);

include __DIR__ . '/layout/top.php';
?>

<h1>
  Annonce <?php echo $titre_categorie; if($publication == 'inactive'){echo " - non publiée !"; }?>
</h1>

<?php
displayFlashMessage();
?>

<div class="row">

  <div class="col-md-6 col-xs-12">
    <h2><?= $titre; ?></h2>
  </div>

  <!--Affichage nnonce-->
  <div class="col-md-6 col-xs-12 text-right">
    <a href="#" data-toggle="modal" data-target="#contact_membre" class="btn btn-success">Contacter <?= $pseudo_membre; ?></a>
    <!-- Modal -->
    <div class="modal fade" id="contact_membre" tabindex="-1" role="dialog" aria-labelledby="contact_membre" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content  text-left">
          <div class="modal-header">
            <h4 class="modal-title">Contacter <?= $pseudo_membre; ?></h4>
          </div>
          <div class="modal-body">
            <p class="text-right"><strong>Par téléphone :</strong> <?= formatTel($telephone_membre); ?> <i class="fa fa-phone"></i></p>
            <hr>
            <?php include __DIR__ . '/layout/contact-membre.php'; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-remove"></i></button>
          </div>
        </div>
      </div>
    </div>
    <?php
    if (isUserConnected() && $_SESSION['membre']['id'] == $annonce['membre_id']):
    ?>
    <a href="annonce-edit.php?id=<?= $annonce['id']; ?>" class="btn btn-warning">Modifier Annonce</a>
    <?php
    endif;
    ?>
  </div>

</div>

<div class="row">

  <div class="col-md-6 col-xs-12 text-center">
    <img src="<?= $src; ?>" style="max-height:300px" alt="<?= $titre; ?>_<?= $id; ?>">
  </div>

  <div class="col-md-6 col-xs-12">
    <strong>Description :</strong>
    <p><?= nl2br($description_courte); ?></p>
    <p><?= nl2br($description_longue); ?></p>
  </div>

</div><hr>

<div class="row">

  <div class="col-md-3 col-xs-6">
    <p><i class="fa fa-calendar"></i> <?= date('d/m/Y', strtotime($annonce['date_enregistrement'])); ?></p>
  </div>

  <div class="col-md-3 col-xs-6">
    <p><i class="fa fa-user"></i> <?= $pseudo_membre; ?>
    <?php
    if ($moyenne_note > 0): // afficher s'il y a au moins une note
      echo ' - Note : ' . number_format($moyenne_note, 1, ',', ' ') . ' / 5';
    endif;
    ?>
  </div>

  <div class="col-md-3 col-xs-6">
    <p><i class="fa fa-euro"></i> <?= number_format($annonce['prix'], 2, ',', ' '); ?></p>
  </div>

  <div class="col-md-3 col-xs-6">
    <p><i class="fa fa-map-marker"></i> <?= $adresse; ?>, <?= $code_postal; ?>, <?= $ville; ?></p>
  </div>
  <hr>
  <iframe src="https://www.google.com/maps/embed/v1/place?key=AIzaSyC41mxqRrY2Qn-pIfFBOzIVdi72tUZzc1o&q=​<?= $adresse?>+<?= $code_postal?>+<?= $ville?>" allowfullscreen></iframe>
  <hr>
  <!--Notes et commentaires-->
  <div class="col-md-6 col-xs-12 dropup" id="comment">
    <p class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">Déposer un commentaire ou une note <span class="caret"></span></p>


    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
      <?php if (isUserConnected()) : ?>
      <li><a href="#" data-toggle="modal" data-target="#note">Déposer une note / un avis</a></li>
      <li role="separator" class="divider"></li>
      <li><a href="#" data-toggle="modal" data-target="#commentaire">Deposer un commentaire</a></li>
      <?php else : ?>
      <li><a href="#">Connectez-vous dans l'espace Membre pour déposer un commentaire ou une note</a></li>
      <?php endif; ?>
    </ul>
    <!-- Modal -->
    <div class="modal fade" id="note" tabindex="-1" role="dialog" aria-labelledby="note" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="note">Votre note</h4>
          </div>
          <div class="modal-body">
            <form method="post">
              <div class="form-group">
                <select name="note" class="form-control" style="width: 10em">
                  <option value="">Note</option>
                  <option value="1">★</option>
                  <option value="2">★★</option>
                  <option value="3">★★★</option>
                  <option value="4">★★★★</option>
                  <option value="5">★★★★★</option>
                </select>
              </div>
              <div class="form-group">
                <label>Avis :</label>
                <textarea name="avis" class="form-control" rows="4" cols="80"><?= $avis; ?></textarea>
              </div>
              <button type="submit" class="btn btn-primary">Valider</button>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-remove"></i></button>
          </div>
        </div>
      </div>
    </div><!-- fin modal note -->
    <div class="modal fade" id="commentaire" tabindex="-1" role="dialog" aria-labelledby="commentaire" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h4 class="modal-title" id="commentaire">Votre commentaire</h4>
          </div>
          <div class="modal-body">
            <form method="post">
              <div class="form-group">
                <textarea name="commentaire" class="form-control" rows="8" cols="80"><?= $commentaire; ?></textarea>
              </div>
              <button type="submit"  class="btn btn-primary">Envoyer</button>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-remove"></i></button>
          </div>
        </div>
      </div>
    </div><!-- fin modal commentaire -->
  </div><!-- fin .dropup -->

  <?php
  if (!empty($_SERVER['HTTP_REFERER'])) {
  ?>
    <div class="col-md-6 col-xs-12 text-right">
      <a href="<?= $_SERVER['HTTP_REFERER']; ?>" class="btn btn-default">Retour</a>
    </div>
  <?php
  }
  ?>
</div>
  <?php
  if (!empty($errors)) :
    ?>
    <br>
    <div class="alert alert-danger">
      <strong>Le formulaire contient des erreurs</strong><br>
      <?= implode('<br>', $errors); ?>
    </div>
    <?php
  endif;

  if (isset($successCommentaire)):
    ?>
    <br>
    <div class="alert alert-success">
      <strong>Votre commentaire a bien été enregistré</strong><br>
    </div>
    <?php
  endif;

  if (isset($successNote)):
    ?>
    <br>
    <div class="alert alert-success">
      <strong>Votre note a bien été enregistrée</strong><br>
    </div>
    <?php
  endif;
  ?>
  <?php
  if ($nbCommentaires > 0): // afficher s'il y a une ou plusieurs annonces
  ?>
  <hr>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        <h3>Commentaire<?php if($nbCommentaires > 1){echo "s";} ?></h3>
        <?php
        foreach ($commentaires as $commentaire) :
        ?>
          <div class="div" id="commentaire<?= $commentaire['id'] ?>">
            <p>Par <strong><?= $commentaire['pseudo_annonce'] ?></strong>, le <strong><?= date('d/m/Y H:i', strtotime($commentaire['date_enregistrement'])); ?></strong></p>
            <p><?= nl2br($commentaire['commentaire']); ?></p>
          </div>
        <?php
        endforeach;
        ?>
      </div>
    </div>
  </div>
  <?php
  endif;

  if ($nbAutres > 0): // afficher s'il y a une ou plusieurs annonces
    $src = (!empty($annonce['photo'])) ? PHOTO_WEB . $annonce['photo'] : PHOTO_DEFAULT;
  ?>
  <div class="row autres_annonces">
    <hr>
    <h3>Autre<?php if($nbAutres > 1){echo "s";} ?> annonce<?php if($nbAutres > 1){echo "s";} ?></h3>
    <?php
    foreach ($autres as $autre) :
      $src = (!empty($autre['photo'])) ? PHOTO_WEB . $autre['photo'] : PHOTO_DEFAULT;
      ?>
      <div class="col-md-3 div autres">
        <a href="annonce.php?id=<?= $autre['id']; ?>">
          <img src="<?= $src; ?>" alt="annonce_<?= $autre['id']; ?>">
          <p class="text-center"><strong><?= $autre['titre']; ?></strong></p>
        </a>
      </div>
      <?php
    endforeach;
  endif;
  ?>
</div>

<?php
include __DIR__ . '/layout/bottom.php';
?>
