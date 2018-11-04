<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();
$req = $pdo->prepare('SELECT n.*, m1.id id_membre_1, m1.email email_membre_1, m2.id id_membre_2, m2.email email_membre_2
        FROM note n
        JOIN membre m1 ON m1.id = n.membre_id1
        JOIN membre m2 ON m2.id = n.membre_id2
        ');
$req->execute();
$notes = $req->fetchAll();

include __DIR__ . '/../layout/top.php';
?>

<h1>Notes</h1>
<?php
displayFlashMessage();
?>
<div class="container-fluid">

  <table class="table">
    <caption>Gestion des commentaires</caption>
    <tr>
      <th>ID</th>
      <th>Membre</th>
      <th>Membre noté</th>
      <th>Note</th>
      <th>Avis</th>
      <th>Date d'enregistrement</th>
      <th class="text-right">Actions</th>
    </tr>
    <?php
    foreach ($notes as $note) :
    ?>
      <tr>
        <td><?= $note['id']; ?></td>
        <td><?= $note['id_membre_1']; ?> - <?= $note['email_membre_1']; ?></td>
        <td><?= $note['id_membre_2']; ?> - <?= $note['email_membre_2']; ?></td>
        <td><?= $note['note']; ?> / 5</td>
        <td><?= $note['avis']; ?></td>
        <td><?= date('d-m-Y H:i', strtotime($note['date_enregistrement'])); ?></td>
        <td class="text-right">
          <a href="note-edit.php?id=<?= $note['id']; ?>" class="btn btn-primary" title="Modifier"><i class="fa fa-pencil-square-o"></i></a>
          <a href="note-delete.php?id=<?= $note['id']; ?>" class="btn btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette note ?');"><i class="fa fa-trash"></i></a>
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
