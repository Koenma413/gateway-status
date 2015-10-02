<?php
require 'cli-bootstrap.php';

use Phalcon\DI;
use Phalcon\DI\Injectable;

class GatewayStatusUpdate extends Injectable
{
    public function run()
    {
        $config = DI::getDefault()->getConfig();
        $queue = new Phalcon\Queue\Beanstalk(
            array(
                'host' => $config->monitor->beanstalkHost,
                'port' => $config->monitor->beanstalkPort
            )
        );
        while (($job = $queue->peekReady()) !== false) {
            $message = $job->getBody();

            if (isset($message['GatewayStatusUpdate'])) {
                $gatewayId = $message['GatewayStatusUpdate'];
                $gateway = Gateway::findFirstByID($gatewayId);
                if ($gateway) {
                    $slackMessage = 'Gateway ' . $gateway->eui;
                    if (!empty($gateway->remarks)) {
                        $slackMessage .= ' (' . $gateway->remarks .  ')';
                    }
                    $slackMessage .= ' is now ' . $gateway->status . '!';
                    $slackMessage .= "\n" . 'it was last seen on ' . $gateway->last_seen;
                    $slackMessage .= "\n" . 'more details at <http://ttnstatus.org/gateways/'.$gateway->eui.'>';
                    $this->sendSlackMessage($slackMessage);
                }
            }
           
            $job->delete();
        }
    }

    private function sendSlackMessage($message)
    {
        $config = DI::getDefault()->getConfig();
        $client = new GuzzleHttp\Client();

        $slackEndpoint = $config->monitor->slackHook;
        $payload = json_encode(
                    array(
                        // "channel" => 'gatewaymonitor', 
                        // "username" => 'bot', 
                        "text" => $message
                    )
                );

        $response = $client->post($slackEndpoint, ['body' => $payload]);
        $responseText = $response->getBody($asString = true);
        return $responseText;
    }
}
try {
    $task = new GatewayStatusUpdate();
    $task->run();
} catch (Exception $e) {
    echo $e->getMessage(), PHP_EOL;
    echo $e->getTraceAsString();
}