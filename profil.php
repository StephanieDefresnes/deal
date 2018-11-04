<?php
require_once __DIR__ . '/include/init.php';

membreSecurity();

$telephone = $nb_annonce = '';
$telephone = preg_replace('/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/','\1 \2 \3 \4 \5', $telephone);

// récupération des données du membre
$req = 'SELECT * FROM membre WHERE id = ' .(int)$_GET['id'];
$stmt = $pdo->query($req);
$membre = $stmt->fetch();

if ($membre === false) {
  header('Location: 404.php');
}
extract($membre);

// récupération des annonces du membre
$reqAnnonce = $pdo->prepare('SELECT a.*, m.id id_membre, c.titre titre_categorie, r.nom nom_region
      FROM annonce a
      LEFT JOIN membre m ON m.id = a.membre_id
      LEFT JOIN categorie c ON c.id = a.categorie_id
      LEFT JOIN region r ON r.id = a.region_id
      WHERE m.id = ?
      ');
$reqAnnonce->execute(array((int)$_GET['id']));
$annonces = $reqAnnonce->fetchAll();
$nbAnnonces = count($annonces);

// récupération des commentaires laissés au membre
// $reqCommentaire = $pdo->prepare('SELECT c.*, m.pseudo pseudo_annonce, a.id id_annonce, a.titre titre_annonce
//       FROM commentaire c
//         INNER JOIN membre m ON m.id = c.membre_id
//         INNER JOIN annonce a ON a.id = c.annonce_id
//         WHERE a.membre_id = ?
//         AND c.membre_id != ?
//         AND c.reponse_id IN (SELECT id FROM commentaire WHERE reponse_id IS NULL)
//         ORDER BY c.id');
$reqCommentaire = $pdo->prepare('SELECT c.*, m.pseudo pseudo_annonce, a.id id_annonce, a.titre titre_annonce
      FROM commentaire c
        INNER JOIN membre m ON m.id = c.membre_id
        INNER JOIN annonce a ON a.id = c.annonce_id
        WHERE a.membre_id = ?
        AND c.membre_id != ?
        ORDER BY c.id DESC');
$reqCommentaire->execute(array((int)$_GET['id'], (int)$_GET['id']));
$commentaires = $reqCommentaire->fetchAll();
$nbCommentaires = count($commentaires);

// récupération des notes attribuées par le membre
$reqNote = $pdo->prepare('SELECT n.*, m1.id id_membre_1, m1.email email_membre_1, m2.id id_membre_2, m2.email email_membre_2
        FROM note n
        JOIN membre m1 ON m1.id = n.membre_id1
        JOIN membre m2 ON m2.id = n.membre_id2
        WHERE n.membre_id1 = ?'
        );
$reqNote->execute(array((int)$_GET['id']));
$notes = $reqNote->fetchAll();
$nbNotes = count($notes);

// note moyenne attribuée au membre
$reqMoyenne = 'SELECT ROUND(AVG(note), 1) note_moyenne FROM note WHERE membre_id2 = ' . (int)$_GET['id'];
$stmtMoy = $pdo->query($reqMoyenne);
$moyenne = $stmtMoy->fetch();

// avis attribués au membre
$reqAvis = $pdo->prepare('SELECT * FROM note WHERE membre_id2 = ' . (int)$_GET['id']);
$reqAvis->execute();
$avisMembres = $reqAvis->fetchAll();
$nbAvis = count($avisMembres);

include __DIR__ . '/layout/top.php';
?>

<h1>Profil</h1>
<?php
displayFlashMessage();
?>
<div class="container-fluid">

  <table class="table">
    <caption>Informations</caption>
    <tr>
      <th>Pseudo</th>
      <th>Civilité</th>
      <th>Nom</th>
      <th>Prénom</th>
      <th>Email</th>
      <th>Téléphone</th>
      <th>Date d'enregitrement</th>
      <th class="text-right">Actions</th>
    </tr>

      <tr>
        <td><?= $pseudo; ?></td>
        <td><?= $civilite ;?></td>
        <td><?= $nom; ?></td>
        <td><?= $prenom; ?></td>
        <td><?= $email; ?></td>
        <td><?= formatTel($telephone) ;?></td>
        <td><?= date('d-m-Y H:i', strtotime($date_enregistrement)); ?></td>
        <td class="text-right">
          <a href="profil-edit.php?id=<?= $id; ?>" class="btn btn-primary" title="Modifier"><i class="fa fa-pencil-square-o"></i></a>
        </td>
      </tr>

  </table>

  <?php
  if ($nbAnnonces > 0): // afficher s'il y a une ou plusieurs annonces'
  ?>
  <table class="table">
      <caption>Annonce<?php if ($nbAnnonces > 1){echo "s";}?></caption>
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
        <td><?= $annonce['titre_categorie']; ?></td>
        <td><?= date('d-m-Y H:i', strtotime($annonce['date_enregistrement'])); ?></td>
        <td><?= $annonce['publication']; ?></td>
        <td class="text-right">
          <a href="annonce.php?id=<?= $annonce['id']; ?>" class="btn btn-default" title="Voir"><i class="fa fa-search"></i></a>
          <a href="annonce-edit.php?id=<?= $annonce['id']; ?>" class="btn btn-primary" title="Modifier"><i class="fa fa-pencil-square-o"></i></a>
          <a href="annonce-delete.php?id=<?= $annonce['id']; ?>" class="btn btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette annonce ?');"><i class="fa fa-trash"></i></a>
        </td>
      </tr>
    <?php
    endforeach;
    ?>
  </table>
  <?php
  endif;

  if ($nbCommentaires > 0): // afficher s'il y a une ou plusieurs annonces'
  ?>
  <table class="table">
    <caption>Commentaire<?php if ($nbCommentaires > 1){echo "s";}?> sur vo<?php if ($nbAnnonces > 1){echo "s";}?><?php if ($nbAnnonces <= 1){echo "tre";}?> annonce<?php if ($nbAnnonces > 1){echo "s";}?></caption>
    <tr>
      <th>Annonce</th>
      <th>Commentaire</th>
      <th>Date d'enregistrement</th>
      <th class="text-right">Actions</th>
    </tr>
    <?php
    foreach ($commentaires as $commentaire) :
    ?>
      <tr>
        <td><?= $commentaire['titre_annonce']; ?></td>
        <td><?= $commentaire['commentaire']; ?></td>
        <td><?= date('d-m-Y H:i', strtotime($commentaire['date_enregistrement'])); ?></td>
        <td class="text-right">
          <a href="annonce.php?id=<?= $commentaire['id_annonce']; ?>#commentaire<?= $commentaire['id']; ?>" class="btn btn-default" title="Voir"><i class="fa fa-search"></i></a>

        </td>
      </tr>
    <?php
    endforeach;
    ?>
  </table>
  <?php
  endif;

  if ($nbAvis > 0): // afficher s'il y a un ou plusieurs avis
  ?>

  <table class="table">
    <caption>Note moyenne : <strong><?= $moyenne['note_moyenne'] ?>  / 5</strong></caption>
    <tr>
      <th>Avis</th>
      <th>Date d'enregistrement</th>
    </tr>
    <?php
    foreach ($avisMembres as $avisMembre) :
    ?>
      <tr>
        <td><?= $avisMembre['avis']; ?></td>
        <td><?= date('d-m-Y H:i', strtotime($avisMembre['date_enregistrement'])); ?></td>
      </tr>
    <?php
    endforeach;
    ?>
  </table>
  <?php
  endif;
  ?>
</div>

<?php
include __DIR__ . '/layout/bottom.php';
?>
