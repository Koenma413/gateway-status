<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

/**
 * Add your routes here
 */
$app->get('/', function () use ($app) {
    $gateways = Gateway::find();
    $lastEntry = Gateway::getLastEntry();

    // this isn't 100% correct: if all gateways are down, the last update datetime won't be the same as the actual update time. Otherwise the update datetime could be up to gateway-update-interval off
    echo $app['view']->render('index', array('gateways' => $gateways, 'lastUpdate' => $lastEntry->last_seen));
});

$app->get('/gateways', function () use ($app) {
    $response = new Phalcon\Http\Response();
    $response->setContentType('application/json');

    $gateways = Gateway::find();

    $gatewaysArray = array();
    foreach ($gateways as $gateway) {
        $gatewayArray = $gateway->toArray();
        unset($gatewayArray['ID']);
        $gatewaysArray[] = $gatewayArray;
    }

    // Pass the content of a file
    $response->setContent(json_encode($gatewaysArray));

    return $response;
});

$app->get('/gateways/{eui}', function ($eui) use ($app) {
    $response = new Phalcon\Http\Response();
    $response->setContentType('application/json');

    $gateway = Gateway::findFirstByEui($eui);
    if (empty($gateway)) {
        $response->setContent('Gateway not found');
        $response->setStatusCode(404, "Not Found");
        return $response;
    }

    $gatewayArray = $gateway->toArray();
    $gatewayArray['entries_in_last_24h'] = '<to be implemented>';
    unset($gatewayArray['ID']);

    // Pass the content of a file
    $response->setContent(json_encode($gatewayArray));

    return $response;
});

/**
 * Not found handler
 */
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    echo $app['view']->render('404');
});
