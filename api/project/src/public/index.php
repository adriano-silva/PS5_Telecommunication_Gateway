<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App;

// Permet de récupérer (dans le cadre du projet) la valeur d'une des fonctionnalités du capteur de consommation
$app->get('/sensors/{moduleURL}/{fctURL}', function(Request $request, Response $response)
{
  $moduleURL = $request->getAttribute('moduleURL');
  $fctURL = $request->getAttribute('fctURL');
  $dom = new DomDocument;
  $dom->load("ressources.xml");
  
  $isModuleOK=FALSE;
  $isfctOK=FALSE;

  $listeModules = $dom->getElementsByTagName('GroupRange');
  $listeFct = $dom->getElementsByTagName('GroupAddress');
  
  //Test si le module entré dans l'URL est dans les ressources
  foreach($listeModules as $module)
  {
    if ($module->getAttribute("Name") === $moduleURL){
    //Le module entré dans l'URL apparaît dans la liste des ressources
    $isModuleOK=TRUE;
    }         
  }

  //Test si la fonctionnalité entrée dans l'URL est dans les ressources
  foreach($listeFct as $fct)
  {
    if ($fct->getAttribute("Name") === $fctURL){
    //La fonctionnalité entrée dans l'URL apparaît dans la liste des ressources
    $isfctOK=TRUE;
    $address = $fct->getAttribute("Address"); 
    }       
  }
  
  // Appel du script et assignation de la variable de retour si tout est OK
  if($isModuleOK == TRUE && $isfctOK == TRUE){   
   $result = exec("python3 /home/pi/Desktop/write_read_KNX.py -l $address -c read");
   $response->getBody()->write("Le résultat est : $result [W] \n");
   $response->getBody()->write("Module --> $moduleURL , Fonctionnalité --> $fctURL, Adresse de la fonctionnalité --> $address"); 
  } else {
   $response->getBody()->write("L'URL ne correspond pas aux ressources disponibles");
  }
   
  return $response;
});

// Permet (dans le cadre du projet) d'allumer/éteindre les lampes connectés au switch
$app->post('/actuators/{moduleURL}/{fctURL}/{function}', function(Request $request, Response $response)
{
  $moduleURL = $request->getAttribute('moduleURL');
  $fctURL = $request->getAttribute('fctURL');
  $OnOff = $request->getAttribute('OnOff');
  $dom = new DomDocument;
  $dom->load("ressources.xml");
  
  $isModuleOK=FALSE;
  $isfctOK=FALSE;
  $isValueOK=FALSE;

  $listeModules = $dom->getElementsByTagName('GroupRange');
  $listeFct = $dom->getElementsByTagName('GroupAddress');
  $value = 0;
  
  //Test si le module entré dans l'URL est dans les ressources
  foreach($listeModules as $module)
  {
    if ($module->getAttribute("Name") === $moduleURL){
    //Le module entré dans l'URL apparaît dans la liste des ressources
    $isModuleOK=TRUE;
    }         
  }

  //Test si la fonctionnalité entrée dans l'URL est dans les ressources
  foreach($listeFct as $fct)
  {
    if ($fct->getAttribute("Name") === $fctURL){
    //La fonctionnalité entrée dans l'URL apparaît dans la liste des ressources
    $isfctOK=TRUE;
    $address = $fct->getAttribute("Address"); 
    }       
  }

  // Test pour savoir si il faut allumer ou éteindre (1 ou 0)
  if($OnOff === "On"){
    $value = 1;
    $isValueOK = TRUE;
  } else if($OnOff === "Off"){
    $isValueOK = TRUE;
  }

  // Appel du script et assignation de la variable de retour si tout est OK
  if($isModuleOK == TRUE && $isfctOK == TRUE && $isValueOK == TRUE){
   $response->getBody()->write("Module --> $moduleURL , Fonctionnalité --> $fctURL, Adresse de la fonctionnalité --> $address, Valeur --> $value");   
   exec("python3 /home/pi/Desktop/write_read_KNX.py -l $address -c write -v $value");
  } else {
   $response->getBody()->write("L'URL ne correspond pas aux ressources disponibles");
  }
   
  return $response;
});


// Listing de tous les sensors présents 
$app->get('/sensors', function(Request $request, Response $response)
{
  $dom = new DomDocument;
  $dom->load("ressources.xml"); 

  $items = $dom->getElementsByTagName('GroupRange');

  // Récupère l'index du noeud ayant comme Name "sensors" 
  for ($i = 0; $i < $items->length; $i++) 
  {
	if ($items->item($i)->getAttribute('Name') === "sensors")
	{
		$nodeIndex = $i;
	}
  }

  // Récupère le noeud ayant comme Name "sensors" 
  $sensors = $dom->getElementsByTagName('GroupRange')->item($nodeIndex);


  $listeModule = $sensors->getElementsByTagName('GroupRange');
  $liste = "la liste de tous les sensors est : \n";

  // Parcourt et récupère tous les sensors disponibles
  foreach($listeModule as $module)
  {
     $liste .= "- " . $module->getAttribute("Name") . "\n";
  }
  
  $response->getBody()->write($liste);

  return $response;
});

// Listing de toutes les fonctionnalités disponible pour le module sensor entré en URL
$app->get('/sensors/{moduleURL}', function(Request $request, Response $response)
{
  $moduleURL = $request->getAttribute('moduleURL');
  $dom = new DomDocument;
  $dom->load("ressources.xml"); 
  $isAvailable = false;
  
  $items = $dom->getElementsByTagName('GroupRange');

  // Récupère l'index du noeud ayant comme Name "moduleURL" 
  for ($i = 0; $i < $items->length; $i++) 
  {
	if ($items->item($i)->getAttribute('Name') === $moduleURL)
	{
		$nodeIndex = $i;
		$isAvailable = true;
	}
  }

  // Récupère le noeud ayant comme Name "moduleURL" 
  $sensors = $dom->getElementsByTagName('GroupRange')->item($nodeIndex);


  $listeFct = $sensors->getElementsByTagName('GroupAddress');
  $liste = "La liste de toutes les fonctionnalité pour le module sensor $moduleURL est : \n";

  // Parcourt et récupère toutes les fonctionnalités du noeud ayant comme Name "moduleURL"
  foreach($listeFct as $fct)
  {
     $liste .= "- " . $fct->getAttribute("Name") . "\n";
  }
  
  // Teste si le noeud ayant comme Name "moduleURL" est disponible
  if($isAvailable == true)
  {
	$response->getBody()->write($liste);
  } else 
  {
	$response->getBody()->write("Le module $moduleURL n'est pas disponible");
  }

  return $response;
});



// Listing de tous les actuators présents 
$app->get('/actuators', function(Request $request, Response $response)
{
  $dom = new DomDocument;
  $dom->load("ressources.xml"); 

  $items = $dom->getElementsByTagName('GroupRange');
  
  // Récupère l'index du noeud ayant comme Name "actuators" 
  for ($i = 0; $i < $items->length; $i++) 
  {
	if ($items->item($i)->getAttribute('Name') === "actuators")
	{
		$nodeIndex = $i;
	}
  }

  // Récupère le noeud ayant comme Name "sensors" 
  $actuators = $dom->getElementsByTagName('GroupRange')->item($nodeIndex);
  $listeModule = $actuators->getElementsByTagName('GroupRange');
  $liste = "la liste de tous les actuateurs est : \n";

  // Parcourt et récupère tous les actuators disponibles
  foreach($listeModule as $module)
  {
     $liste .= "- " . $module->getAttribute("Name") . "\n";
  }
  
  $response->getBody()->write($liste);

  return $response;
});

// Listing de toutes les fonctionnalités disponible pour le module actuator entré en URL
$app->get('/actuators/{moduleURL}', function(Request $request, Response $response)
{
  $moduleURL = $request->getAttribute('moduleURL');
  $dom = new DomDocument;
  $dom->load("ressources.xml"); 
  $isAvailable = false;
  

  $items = $dom->getElementsByTagName('GroupRange');

  // Récupère l'index du noeud ayant comme Name "moduleURL" 
  for ($i = 0; $i < $items->length; $i++) 
  {
	if ($items->item($i)->getAttribute('Name') === $moduleURL)
	{
		$nodeIndex = $i;
		$isAvailable = true;
	}
  }

  // Parcourt et récupère toutes les fonctionnalités du noeud ayant comme Name "moduleURL"
  $sensors = $dom->getElementsByTagName('GroupRange')->item($nodeIndex);
  $listeFct = $sensors->getElementsByTagName('GroupAddress');
  $liste = "La liste de toutes les fonctionnalité pour le module actuato $moduleURL est : \n";

  // Parcourt et récupère toutes les fonctionnalités du noeud ayant comme Name "moduleURL"
  foreach($listeFct as $fct)
  {
     $liste .= "- " . $fct->getAttribute("Name") . "\n";
  }
  
  // Teste si le noeud ayant comme Name "moduleURL" est disponible
  if($isAvailable == true)
  {
	$response->getBody()->write($liste);
  } else 
  {
	$response->getBody()->write("Le module $moduleURL n'est pas disponible");
  }

  return $response;


});


$app->run();
