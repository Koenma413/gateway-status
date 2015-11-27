<?php
require 'cli-bootstrap.php';

use Phalcon\DI;
use Phalcon\DI\Injectable;
class UpdateGateways extends Injectable
{
    public function run()
    {
        $newGateways = Gateway::getNewGatewayEuisFromApi();
	    foreach ($newGateways as $newGateway) {
	        $newGateway->save();
	    }

	    $lastEntry = Gateway::getLastEntry();
	    Gateway::updateGatewayStatusFromApi($lastEntry->last_seen);
	}
}
try {
    $task = new UpdateGateways();
    $task->run();
} catch (Exception $e) {
    echo $e->getMessage(), PHP_EOL;
    echo $e->getTraceAsString();
}