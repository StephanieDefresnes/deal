<?php
require_once __DIR__ . '/../include/init.php';
adminSecurity();

// Top 5 des membres les mieux notés
$reqNotes = $pdo->prepare('SELECT ROUND(AVG(note), 1) note_moyenne, COUNT(n.note) total_note, m.id id_membre, m.nom nom_membre, m.prenom prenom_membre
        FROM note n
        JOIN membre m ON m.id = n.membre_id2
        GROUP BY m.id
        ORDER BY note_moyenne DESC
        LIMIT 5
        ');
$reqNotes->execute();
$notes = $reqNotes->fetchAll();

// Top 5 des membres les plus actifs
$reqActifs = $pdo->prepare('SELECT m.nom nom_membre, m.prenom prenom_membre, a.membre_id membre_annonce, COUNT(a.id) total_annonce
        FROM membre m
        JOIN annonce a ON a.membre_id = m.id
        GROUP BY a.membre_id
        ORDER BY total_annonce DESC
        LIMIT 5
        ');
$reqActifs->execute();
$actifs= $reqActifs->fetchAll();

// Top 5 des annonces les plus anciennes
$reqAnnonces = $pdo->prepare('SELECT * FROM annonce ORDER BY date_enregistrement LIMIT 5');
$reqAnnonces->execute();
$annonces= $reqAnnonces->fetchAll();

// Top 5 des catégories contenant le plus d'annonces
$reqCategories = $pdo->prepare('SELECT c.*, COUNT(a.id) nombre_annonce
        FROM categorie c
        JOIN annonce a ON a.categorie_id = c.id
        GROUP BY c.titre
        ORDER BY nombre_annonce DESC
        LIMIT 5
        ');
$reqCategories->execute();
$categories = $reqCategories->fetchAll();

include __DIR__ . '/../layout/top.php';
?>

<h1>Statistiques</h1>

<div class="container">

        <h2>Top 5 des membres les mieux notés</h2>
        <ul>
                <?php
                foreach ($notes as $note) :
                ?>
                <li><?= $note['prenom_membre'] . ' ' . $note['nom_membre'] . ' : ' . $note['note_moyenne'] . ' / 5 basé sur ' . $note['total_note'] . ' avis'?> </li>
                <?php
                endforeach;
                ?>
        </ul>

        <h2>Top 5 des membres les plus actifs</h2>
        <ul>
                <?php
                foreach ($actifs as $actif) :
                ?>
                <li><?= $actif['prenom_membre'] . ' ' . $actif['nom_membre'] . ' avec ' . $actif['total_annonce'] ?> annonce<?php if($actif['total_annonce'] > 1) {echo "s";}?> </li>
                <?php
                endforeach;
                ?>
        </ul>

        <h2>Top 5 des annonces les plus anciennes</h2>
        <ul>
                <?php
                foreach ($annonces as $annonce) :
                ?>
                <li><?= $annonce['titre'] . ' - le ' . date('d-m-Y H:i', strtotime($annonce['date_enregistrement'])); ?></li>
                <?php
                endforeach;
                ?>
        </ul>

        <h2>Top 5 des catégories contenant le plus d'annonces</h2>
        <ul>
                <?php
                foreach ($categories as $categorie) :
                ?>
                <li><?= $categorie['titre']; ?> - <?= $categorie['nombre_annonce']; ?> annonce<?php if($categorie['nombre_annonce'] > 1) {echo "s";}?></li>
                <?php
                endforeach;
                ?>
        </ul>

</div>

<?php
include __DIR__ . '/../layout/bottom.php';
?>
