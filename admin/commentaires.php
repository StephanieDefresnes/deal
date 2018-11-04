<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();
$req = ('SELECT c.*, m.id AS id_membre, m.email AS email_membre, a.id AS id_annonce, a.titre AS titre_annonce
        FROM commentaire c
        JOIN membre m ON m.id = c.membre_id
        JOIN annonce a ON a.id = c.annonce_id
        ');
$stmt = $pdo->query($req);
$commentaires = $stmt->fetchAll();

include __DIR__ . '/../layout/top.php';
?>

<h1>Commentaires</h1>
<?php
displayFlashMessage();
?>
<div class="container-fluid">

  <table class="table">
    <caption>Gestion des commentaires</caption>
    <tr>
      <th>ID</th>
      <th>Membre</th>
      <th>Annonce</th>
      <th>Commentaire</th>
      <th>Date d'enregistrement</th>
      <th class="text-right">Actions</th>
    </tr>
    <?php
    foreach ($commentaires as $commentaire) :
    ?>
      <tr>
        <td><?= $commentaire['id']; ?></td>
        <td><?= $commentaire['id_membre']; ?> - <?= $commentaire['email_membre']; ?></td>
        <td><?= $commentaire['id_annonce']; ?> - <?= $commentaire['titre_annonce']; ?></td>
        <td><?= $commentaire['commentaire']; ?></td>
        <td><?= date('d-m-Y H:i', strtotime($commentaire['date_enregistrement'])); ?></td>
        <td class="text-right">
          <a href="../annonce.php?id=<?= $commentaire['id_annonce']; ?>#commentaire<?= $commentaire['id']; ?>" class="btn btn-default" title="Voir"><i class="fa fa-search"></i></a>
          <a href="commentaire-edit.php?id=<?= $commentaire['id']; ?>" class="btn btn-primary" title="Modifier"><i class="fa fa-pencil-square-o"></i></a>
          <a href="commentaire-delete.php?id=<?= $commentaire['id']; ?>" class="btn btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce commentaire ?');"><i class="fa fa-trash"></i></a>
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
