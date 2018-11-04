<?php
// nettoie une valeur
//****************************
function sanitizeValue(&$value)
{
  $value = trim(strip_tags($value));
  // trim() supprime les espaces en début et fin de chaîne
  // strip_tags() supprime les balises HTML
}
function sanitizeArray(array &$array)
{
  // applique la fonction sanitizeValue() à toutes les valeurs du tableau
  array_walk($array, 'sanitizeValue');
}
// nettoie le tableau $_POST
function sanitizePost()
{
  sanitizeArray($_POST);
}

// Session
//***************************
function isUserConnected()
{
  return isset($_SESSION['membre']);
}

// récupération des nom et prénom si connecté
function getUserFullName()
{
  if(isUserConnected()) {
    return $_SESSION['membre']['prenom'] . ' ' .$_SESSION['membre']['nom']; //  /!\ pb changement info session dans top

  }
  return '';
}

// accès si admin ou user connecté (pages front)
function isUserMembre()
{
  return isUserConnected() && $_SESSION['membre']['role'] == 'user' ||  $_SESSION['membre']['role'] == 'admin';
}

// accès si admin connecté (pages back)
function membreSecurity()
{
  if (!isUserMembre()){
    // si on n'est pas connecté
    if (!isUserConnected()){
      // redirection vers la page de connexion
      header('Location: ' . RACINE_WEB . 'connexion.php');
    // si on est connecté mais avec le role utilisateur
    } else {
      // on interdit l'accès à la page
      header('HTTP/1.1 403 Forbidden');
      echo "Vous n'avez pas le droit d'accéder à cette page !";
    }
    die;
  }
}
// Session Admin
//***************************
function isUserAdmin()
{
  return isUserConnected() && $_SESSION['membre']['role'] == 'admin';
}

//--
function adminSecurity()
{
  if (!isUserAdmin()){
    // si on n'est pas connecté
    if (!isUserConnected()){
      // redirection vers la page de connexion
      header('Location: ' . RACINE_WEB . 'connexion.php');
    // si on est connecté mais avec le role utilisateur
    } else {
      // on interdit l'accès à la page
      header('HTTP/1.1 403 Forbidden');
      echo "Vous n'avez pas le droit d'accéder à cette page !";
    }
    die;
  }
}

// enregistre un message dans la session
//********************************************
function setFlashMessage($message, $type = 'success')
{
  $_SESSION['flashMessage'] = [
    'message' => $message,
    'type' => $type
  ];
}
// affiche le message en registré en session
// puis le supprime
function displayFlashMessage()
{
  if (isset($_SESSION['flashMessage'])){
    $message = $_SESSION['flashMessage']['message'];
    $type = ($_SESSION['flashMessage']['type'] == 'error')
      ? 'danger'  // pour la classe alert-danger du bootstrap
      : $_SESSION['flashMessage']['type']
    ;

    echo '<div class="alert alert-' . $type . '">'
        . "<strong>$message</strong>"
        . '</div>'
    ;

    unset($_SESSION['flashMessage']);
  }
}

// modifie le format telephone
//********************************
function formatTel($telephone) {
  $i=0;
  $j=0;
  $formate = "";
  while ($i<strlen($telephone)) { // tant qu'il y a des caractères
    if ($j < 2) {
      if (preg_match('/^[0-9]$/', $telephone[$i])) { // si on a bien un chiffre on le garde
        $formate .= $telephone[$i];
        $j++;
      }
      $i++;
    }
    else { //si on a mis 2 chiffres à la suite on met un espace
      $formate .= " ";
      $j=0;
    }
  }
  return $formate;
}


// formulaire de contact
//********************************

// cette fonction sert à nettoyer et enregistrer un texte

function Rec($text)
{
	$text = htmlspecialchars(trim($text), ENT_QUOTES);
	if (1 === get_magic_quotes_gpc())
	{
		$text = stripslashes($text);
	}

	$text = nl2br($text);
	return $text;
}

// Cette fonction sert à vérifier la syntaxe d'un email

function IsEmail($email)
{
	$value = preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $email);
	return (($value === 0) || ($value === false)) ? false : true;
}
