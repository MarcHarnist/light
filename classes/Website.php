<?php
/****                               - CLASSE Website  -
*                                     Marc L. Harnist
*                                        20/07/2018
*
* MAJ: 31/07/18, 23/08/18, 30/08/18 (membersPermissions plus court)
*      30/08/18 Website coupé en deux: nouvelle classe Alpha. Alpha
*      contient uniquement les données particulières du site web.
*      2020-07-09 Alpha erased. No class with a properly name (Eric from IdemSante)
* IMPORTANT
*     Toutes les constantes et variables du site renseignées ici.
*     Aucune autre classe ne doit contenir de donnée particulière
*     afin de limiter la modification de ce seul fichier pour l'installation du
*     CMS Light dans un site web.
*     Ce fichier remplace "config.php"
*
*  METHODES
*    __construct()...........................line 36
*    message()...............................line 44
*    message_title().........................line 54
*    membersPermissions()....................line 64
*    clientPermissions().....................line 76
*    redirection()...........................line 75
*    session()...............................line 83 
*    sessionClient()........................line 105
*/
class Website {
    // DB TABLES (inutile de créer notebook_members)
    // const MAX_SIZE = 10000000; // Taille max en octets du fichier
    // const WIDTH_MAX = 3000;    // Largeur max de l'image en pixels
    // const HEIGHT_MAX = 2000;   // Hauteur max de l'image en pixels
	// const LEVEL = 100; // Niveau par défaut des utilisateurs

    public $website_url;
    public $page_url;
    public $file_url;
    public $img_url;

/** __construct()...........................line  36
*	Description : démarre automatiquement la methode defineDatabaseIds
*	Paramètres: aucun
*	Valeurs retournées : aucune. Modifie les attributs de l'objet
*	Version : 1.0 Créée le: 29/07/2018 MAJ: 30/08/2018
*/  public function __construct(){ 
		$this->website_url = WEBSITE_URL;
		$this->page_url = PAGE_URL;
		$this->img_url = PUBLIC_PATH."/img/";

	}
/** message()...............................line  44
*	Description : permet d'afficher un message en css dans un contrôleur
*	Paramètres: $titre, $message, $background-color.
*	Valeurs retournées : message avec css
*	Version : 1.0 Créée le: 29/07/2018 MAJ: 30/08/2018
*/  public static function message($titre = "", $message = "", $background_color = "white"){
		if($titre or $message != "")//If message exists. <div> allows <p>
			return self::message_title($titre, $background_color) . "\n<div style=\"background-color:" . $background_color . ";padding:10px;margin:0;\">" . $message . "</div>\n";
		else //No message registered
			return self::message_title("Messages", "pink") . "<p style=\"background-color:" . $background_color . ";padding:10px;margin:0;\">Pas de message renseigné.</p>";
	}
/** message_title().........................line  54
*	Description : ajoute du css au titre du message dans le contrôleur
*   Utilisé dans la méthode classes/Website.php/message()
*	Paramètres: $titre, $background-color.
*	Valeurs retournées : message avec css
*	Version : 1.0 Créée le: 29/07/2018 MAJ: 30/08/2018
*/  public static function message_title($titre, $background_color){
        return "<h3 style=\"background-color:" . $background_color . ";padding:10px;margin:0px;\">" . $titre . "</h3>";
	}
/** membersPermissions()....................line 64
*	Description : redirige à l'accueil si le visiteur n'a pas les droits (niveau)
*	Paramètres: $int (niveau) et objet $member
*	Valeurs retournées : message si droits refusés: exit bloque le code
*	Version : 1.0 Créée le: 29/07/2018 MAJ: 30/08/2018
*/  public function membersPermissions($int = 99, $member){
      if(!$_SESSION['member'])
	  {
		header('Location: ' . $this->page_url . 'connexion');//Redirection à l'accueil du site
		exit(__FILE__);
	  }
	  elseif($member->level() > $int)//Si le visiteur n'a pas le niveau (droits)
	  {
		header('Location: ' . $this->page_url . 'acces-limite');//Redirection à l'accueil du site
		exit(__FILE__);
	  }
    }
/** clientPermissions()......................line 76
*	Description : redirige à l'accueil si le visiteur n'a pas les droits (niveau)
*	Paramètres: $int (niveau) et objet $client
*	Valeurs retournées : message si droits refusés: exit bloque le code
*	Version : 1.0 Créée le: 29/07/2018 MAJ: 28/05/2020
*   $int = 99, : valeur par défaut.
*/  public function clientsPermissions($int = 99, $client){
		if(!$_SESSION['client'])
			header('Location: ' . $this->page_url . 'accueil');//Redirect to homepage if client is not connected
		elseif($client !== Null && $client->level() > $int)//If client has not enought rights to be here
			header('Location: ' . $this->page_url . 'acces-limite');//Redirect to "limited-access" page
    }
	
/** redirection()...........................line 75
*	Description : crée un lien vers une page depuis root
*	Paramètres: $page: nom de la page. Ex: accueil
*	Valeurs retournées : lien depuis root
*   Used in "__file-edition"                               
*/  public function redirection($page){
        return $this->page_url . $page;
  }
/** session()...............................line 83 
*	Description : Restaure l'objet Member mémorisé dans "inc/footer.php"
*	Paramètres: aucun
*	Valeurs retournées : objet $member
*   Used in index to replace include_once(models/user.php)
*/  public static function session(){
		// Une session "member" existe-t-elle ?
		if(isset($_SESSION['member'])) {
			$manager = new MembersManager();//on crée le manager des membres avec données db
			$member = $manager->get($_SESSION['member']);// Création de l'objet "member"

			// Si le membre n'existe pas dans la base de donnée on déconnecte tout
			if($member === NULL){
			   // The member do not exists in database but still in the navigator memory. We empty it.
			   unset($member);//destruction de $member
			   unset($_SESSION['member']); // same action to session memory
			   // session_destroy(); // close de session
			}
		return $member;
      	}
	}//Close 	public static function session()
	
/** sessionClient()...............................line 105
*	Description : Restaure l'objet Client mis en mémoire dans une session 
*   le cas échéant pour économiser une requête sql
*	Paramètres: Client mémorisé dans "inc/footer.php"
*	Valeurs retournées : objet $client ou Null
*   Utilisé dans root/index.php ligne 24
*/  public static function sessionClient($client = Null){
	
		// Une session "client" existe-t-elle ?
		if(isset($_SESSION['client'])) {
			$manager = new ClientsManager();//on crée le manager des clients avec les données de la base de données
			$client = $manager->get($_SESSION['client']);// Création de l'objet "client"

			// Si le client n'existe pas dans la base de donnée on déconnecte tout
			if($client === NULL){
			   // Le client n'existe pas dans la base de donnée mais dans la mémoire du navigateur. Nous la vidons.
			   // The client do not exists in database but still in the navigator memory. We empty it.
			   unset($_SESSION['client']); // destruction de la mémoire de la session //same action to session memory
			}
		return $client;
      	}
	}//Close 	public static function session()
	
	
	public function __toString()
	{
		return self::NAME;
	}
	
}//Close the class