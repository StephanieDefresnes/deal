<?php
/*
	********************************************************************************************
	CONFIGURATION
	********************************************************************************************
*/
// destinataire est votre adresse mail. Pour envoyer à plusieurs à la fois, séparez-les par une virgule
$destinataire = 'stcozzi@gmail.com';

// copie ? (envoie une copie au visiteur)
$copie = 'oui';

// Messages de confirmation du mail
$message_envoye = "Votre message nous est bien parvenu ! Une copie vous a été envoyée";
$message_non_envoye = "L'envoi du mail a échoué, veuillez réessayer SVP.";

// Message d'erreur du formulaire
$message_formulaire_invalide = "Vérifiez que tous les champs soient bien remplis et que l'email soit sans erreur.";

/*
	********************************************************************************************
	FIN DE LA CONFIGURATION
	********************************************************************************************
*/



// formulaire envoyé, on récupère tous les champs.
$contactNom     = (isset($_POST['nom']))     ? Rec($_POST['nom'])     : '';
$contactEmail   = (isset($_POST['email']))   ? Rec($_POST['email'])   : '';
$objet   = (isset($_POST['objet']))   ? Rec($_POST['objet'])   : '';
$contactMessage = (isset($_POST['message'])) ? Rec($_POST['message']) : '';

// On va vérifier les variables et l'email ...
$contactEmail = (IsEmail($contactEmail)) ? $contactEmail : ''; // soit l'email est vide si erroné, soit il vaut l'email entré
$err_formulaire = false; // sert pour remplir le formulaire en cas d'erreur si besoin

if (isset($_POST['envoi']))
{
	if (($contactNom != '') && ($contactEmail != '') && ($objet != '') && ($contactMessage != ''))
	{
		// les 4 variables sont remplies, on génère puis envoie le mail
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'From:'.$nom.' <'.$contactEmail.'>' . "\r\n" .
				'Reply-To:'.$contactEmail. "\r\n" .
				'Content-Type: text/plain; charset="utf-8"; DelSp="Yes"; format=flowed '."\r\n" .
				'Content-Disposition: inline'. "\r\n" .
				'Content-Transfer-Encoding: 7bit'." \r\n" .
				'X-Mailer:PHP/'.phpversion();

		// envoyer une copie au visiteur ?
		if ($copie == 'oui')
		{
			$cible = $destinataire.';'.$contactEmail;
		}
		else
		{
			$cible = $destinataire;
		};

		// Remplacement de certains caractères spéciaux
		$contactMessage = str_replace("&#039;","'",$contactMessage);
		$contactMessage = str_replace("&#8217;","'",$contactMessage);
		$contactMessage = str_replace("&quot;",'"',$contactMessage);
		$contactMessage = str_replace('<br>','',$contactMessage);
		$contactMessage = str_replace('<br />','',$contactMessage);
		$contactMessage = str_replace("&lt;","<",$contactMessage);
		$contactMessage = str_replace("&gt;",">",$contactMessage);
		$contactMessage = str_replace("&amp;","&",$contactMessage);

		// Envoi du mail
		$num_emails = 0;
		$tmp = explode(';', $cible);
		foreach($tmp as $email_destinataire)
		{
			if (mail($email_destinataire, $objet, $contactMessage, $headers))
				$num_emails++;
		}

		if ((($copie == 'oui') && ($num_emails == 2)) || (($copie == 'non') && ($num_emails == 1)))
		{
			echo '<script>alert("'.$message_envoye.'");</script>';
		}
		else
		{
			echo '<script>alert("'.$message_non_envoye.'");</script>';
		};
	}
	else
	{
		// une des 3 variables (ou plus) est vide ...
		echo '<script>alert("'.$message_formulaire_invalide.'");</script>';
		$err_formulaire = true;
	};
}; // fin du if (!isset($_POST['envoi']))

if (($err_formulaire) || (!isset($_POST['envoi'])))
{
	// afficher le formulaire
	echo '
	<form id="contact-site" method="post">
		<fieldset>
			<div class="form-group">
				<label for="nom">Nom :</label>
				<input type="text" id="nom" name="nom" value="'.stripslashes($contactNom).'" tabindex="1"  class="form-control">
			</div>
			<div class="form-group">
				<label for="email">Email :</label>
				<input type="text" id="email" name="email" value="'.stripslashes($contactEmail).'" tabindex="2"  class="form-control">
			</div>
		</fieldset>

		<fieldset>
			<div class="form-group">
				<label for="objet">Objet :</label>
				<input type="text" id="objet" name="objet" value="'.stripslashes($objet).'" tabindex="3" class="form-control">
			</div>
			<div class="form-group">
				<label for="message">Message :</label>
				<textarea id="message" name="message" tabindex="4" cols="30" rows="8" class="form-control">'.stripslashes($contactMessage).'</textarea>
			</div>
		</fieldset>

		<div class="text-center">
			<button type="submit" name="envoi" class="btn btn-primary">Envoyer</button>
		</div>
	</form>';
};
?>
