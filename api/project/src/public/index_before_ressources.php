<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

$app = new \Slim\App;
$app->get('/sensors/powerMeasurement/{fct}', function(Request $request, Response $response)
{
    $fct = $request->getAttribute('fct');
    
    switch ($fct) {
        case "consumption":
            $result = exec('python3 /home/pi/Desktop/write_read_KNX.py -l 1/1/0 -c read'); 
            $response->getBody()->write("La consommation totale actuelle est de $result [W]");
            break;
        default:
            $response->getBody()->write("Fonctionnalité non existante");
    }
    return $response;
});


$app->post('/actuators/lightning/{room}Light{fct}', function(Request $request, Response $response)
{
    $room = $request->getAttribute('room');
    $fct  = $request->getAttribute('fct');
    
    switch ($room) {
        case "room":
            if ($fct === "On") {
		exec('python3 /home/pi/Desktop/write_read_KNX.py -l 1/0/0 -c write -v 1');
                $response->getBody()->write("Appel du script pour la roomLight et On");
            }            
            else if ($fct === "Off") {
		exec('python3 /home/pi/Desktop/write_read_KNX.py -l 1/0/0 -c write -v 0');
                $response->getBody()->write("Appel du script pour la roomLight et Off");
            } else {
                $response->getBody()->write("Entrez comme fonction On ou Off svp (par exemple roomLightOn)");
            }
            break;
        
        case "kitchen":
            if ($fct === "On") {
		exec('python3 /home/pi/Desktop/write_read_KNX.py -l 1/0/1 -c write -v 1');
                $response->getBody()->write("Appel du script pour la kitchenLight et On");
            }            
            else if ($fct === "Off") {
		exec('python3 /home/pi/Desktop/write_read_KNX.py -l 1/0/1 -c write -v 0');
                $response->getBody()->write("Appel du script pour la kitchenLight et Off");
            } else {
                $response->getBody()->write("Entrez comme paramètre On ou Off svp (par exemple roomLightOn)");
            }
            break;
        
        default:
            $response->getBody()->write("Cette lampe n'existe pas");
    }
    return $response;
});


$app->run();
