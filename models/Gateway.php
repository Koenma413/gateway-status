<?php

use Phalcon\DI;
class Gateway extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $ID;

    /**
     *
     * @var string
     */
    public $eui;

    /**
     *
     * @var string
     */
    public $location;

    /**
     *
     * @var string
     */
    public $status;

    /**
     *
     * @var string
     */
    public $last_seen;

    /**
     *
     * @var string
     */
    public $remarks;

    /**
     *
     * @var string
     */
    public $created_at;

    /**
     *
     * @var string
     */
    public $updated_at;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->hasMany('ID', 'StatusUpdate', 'gateway_id', array('alias' => 'StatusUpdate'));
    }


    public static function getNewGatewayEuisFromInflux()
    {
        $database = DI::getDefault()->getInfluxdb();
        $result = $database->query("SHOW TAG VALUES FROM gateway_status WITH KEY=eui");

        $existingGateways = Gateway::find();
        $existingEuis = array();
        foreach ($existingGateways as $existingGateway) {
            $existingEuis[$existingGateway->eui] = $existingGateway;
        }

        $now = self::getCurrentLocalDateTime();

        $newGateways = array();
        foreach ($result->getPoints() as $eui) {
            if (!(isset($existingEuis[$eui['eui']]))) {
                echo 'New eui found: ' . $eui['eui'];
                $newGateway = new Gateway();
                $newGateway->eui = $eui['eui'];
                $newGateway->created_at = self::prepareForDb($now);
                $newGateway->updated_at = self::prepareForDb($now);
                $newGateways[] = $newGateway;
            }
        }

        return $newGateways;
    }

    public static function updateGatewayStatusFromInflux($since = null, $interval = '1h')
    {
        $config = DI::getDefault()->getConfig();
        $database = DI::getDefault()->getInfluxdb();

        if (is_null($since)) {
            if (!preg_match('/\d{1,10}[smhd]{1}/', $interval)) {
                echo 'Malformed interval';
                return false;
            }
            $result = $database->query("SELECT * FROM gateway_status WHERE time > (NOW() - $interval) GROUP BY eui;");
        } else {
            $localTimezone = new DateTimeZone($config->monitor->localTimezone);
            $localSince = new DateTime($since, $localTimezone);

            $maxSince = self::getCurrentLocalDateTime();
            $maxSince->modify($config->monitor->maxSinceOffset);

            // make sure we're not querying too much in the past, which would result in many entries returned and either influxdb quitting or php crashing
            if ($localSince < $maxSince) {
                $localSince = $maxSince;
            }

            $influxTimezone = new DateTimeZone($config->monitor->influxTimezone);
            $influxSince = clone $localSince;
            $influxSince = $influxSince->setTimezone($influxTimezone);

            $result = $database->query("SELECT * FROM gateway_status WHERE time > '" . $influxSince->format('Y-m-d H:i:s.u') . "' GROUP BY eui;");
        }

        $now = self::getCurrentLocalDateTime();
        $gatewaysProcessed = array();

        foreach ($result->getSeries() as $serie) {
            $eui = $serie['tags']['eui'];
            $numberOfValues = count($serie['values']);
            $lastValue = $serie['values'][$numberOfValues-1];
            $valueIndex = array_flip($serie['columns']);

            $gateway = Gateway::findFirstByEui($eui);
            $gateway->last_seen = self::prepareForDb(self::convertToLocalTimezone($lastValue[$valueIndex['time']]));
            $gateway->location = $lastValue[$valueIndex['latitude']] . ',' . $lastValue[$valueIndex['longitude']];
            $gateway->updated_at = self::prepareForDb($now);

            $statusUpdate = new StatusUpdate();
            $statusUpdate->update_time = self::prepareForDb($now);

            if (is_null($since)) {
                $statusUpdate->interval = $interval;
            } else {
                $statusUpdate->since_time = self::prepareForDb($localSince);
            }
            
            $statusUpdate->entries_seen = $numberOfValues;

            $gateway->StatusUpdate = $statusUpdate;

            if (!$gateway->save()) {
                foreach ($gateway->getMessages() as $message) {
                    echo $message;
                }
            } else {
                $gatewaysProcessed[] = $eui;
            }
        }

        // make sure that gateways that did not send a recent message to influx get updated
        $allGateways = Gateway::find();

        foreach ($allGateways as $gateway) {
            if (!in_array($gateway->eui, $gatewaysProcessed)) {
                $gateway->updated_at = self::prepareForDb($now);
                $gateway->save();
            }
        }

    }

    public static function getNewGatewayEuisFromApi()
    {
        $config = $config = DI::getDefault()->getConfig();
        $apiEndpoint = $config->monitor->apiEndPoint;
        $client = new GuzzleHttp\Client();

        $responseText = $client->get($apiEndpoint)->getBody($asString = true);
        $response = json_decode($responseText);

        $existingGateways = Gateway::find();
        $existingEuis = array();
        foreach ($existingGateways as $existingGateway) {
            $existingEuis[$existingGateway->eui] = $existingGateway;
        }

        $now = self::getCurrentLocalDateTime();
        $newGateways = array();

        foreach ($response as $gatewayApi) {
            if (!(isset($existingEuis[$gatewayApi->eui]))) {
                echo 'New eui found: ' . $gatewayApi->eui;
                $newGateway = new Gateway();
                $newGateway->eui = $gatewayApi->eui;
                $newGateway->created_at = self::prepareForDb($now);
                $newGateway->updated_at = self::prepareForDb($now);
                $newGateways[] = $newGateway;
            }
        }

        return $newGateways;
    }


    public static function updateGatewayStatusFromApi()
    {
        $config = $config = DI::getDefault()->getConfig();
        $apiEndpoint = $config->monitor->apiEndPoint;
        $client = new GuzzleHttp\Client();

        $responseText = $client->get($apiEndpoint)->getBody($asString = true);
        $response = json_decode($responseText);

        $now = self::getCurrentLocalDateTime();
        $gatewaysProcessed = array();

        foreach ($response as $gatewayApi) {
            $gateway = Gateway::findFirstByEui($gatewayApi->eui);
            $gateway->last_seen = self::prepareForDb(self::convertToLocalTimezone($gatewayApi->last_seen));
            $gateway->location = $gatewayApi->latitude . ',' . $gatewayApi->longitude;
            $gateway->updated_at = self::prepareForDb($now);
            if (!$gateway->save()) {
                foreach ($gateway->getMessages() as $message) {
                    echo $message;
                }
            } else {
                $gatewaysProcessed[] = $gatewayApi->eui;
            }
        }

        // make sure that gateways that did not send a recent message to influx get updated
        $allGateways = Gateway::find();

        foreach ($allGateways as $gateway) {
            if (!in_array($gateway->eui, $gatewaysProcessed)) {
                $gateway->updated_at = self::prepareForDb($now);
                $gateway->save();
            }
        }

    }


    public static function getLastEntry()
    {
        $lastEntry = Gateway::findFirst(
            array(
                "order" => "last_seen DESC",
            )
        );

        return $lastEntry;
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return Gateway[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return Gateway
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }

    public function beforeSave()
    {
        $config = DI::getDefault()->getConfig();
        $downThresholdDateTime = self::getCurrentLocalDateTime();
        $downThresholdDateTime->modify($config->monitor->downStatusOffset);
        $deadThresholdDateTime = self::getCurrentLocalDateTime();
        $deadThresholdDateTime->modify($config->monitor->deadStatusOffset);

        // determine if gateway is up or down, by comparing last seen entry to user defined offset from current time
        $lastSeenDateTime = self::getDateTimeObject($this->last_seen, new DateTimeZone($config->monitor->localTimezone));
        if ($lastSeenDateTime > $downThresholdDateTime) {
            $newStatus = 'up';
        } elseif ($lastSeenDateTime > $deadThresholdDateTime) {
            $newStatus = 'down';
        } else {
            $newStatus = 'dead';
        }

        if ($newStatus != $this->status) {
            // if status has changed, do something
            $this->queueNotification();
        }

        $this->status = $newStatus;
    }

    private function queueNotification()
    {
        $config = DI::getDefault()->getConfig();
        $queue = new Phalcon\Queue\Beanstalk(
            array(
                'host' => $config->monitor->beanstalkHost,
                'port' => $config->monitor->beanstalkPort
            )
        );

        $queue->put(
            array('GatewayStatusUpdate' => $this->ID)
        );
    }


    protected static function convertNanoToMicroseconds($datetime)
    {
        // PHP's DateTime doesn't seem to handle very high precision timestamps, so round down
        if (preg_match('/.+(\.\d+)[^0-9]+$/', $datetime, $subseconds)) {
             // round, then remove '0' from string
            $microseconds = substr(round($subseconds[1], 4), 1);
            $datetime = preg_replace('/(.+)\.\d+([^0-9]+$)/', '${1}'.$microseconds.'${2}', $datetime);
        }
       
        return $datetime;
    }


    public static function getCurrentLocalDateTime()
    {
        $config = DI::getDefault()->getConfig();
        $currentDateTime = new DateTime("now", new DateTimeZone($config->monitor->localTimezone));

        return $currentDateTime;
    }

    public static function getDateTimeObject($datetime, $timezone)
    {
        if (!is_object($datetime)) {
            $datetime = self::convertNanoToMicroseconds($datetime);
            // if left empty, the current date is returned, so set to date far in the past
            if (empty($datetime)) {
                $datetime = '1970-01-01';
            }
            $datetime = new DateTime($datetime, $timezone);
        }

        return $datetime;
    }

    public static function convertToLocalTimezone($datetime)
    {
        $config = DI::getDefault()->getConfig();
        $localTimezone = new DateTimeZone($config->monitor->localTimezone);
        $influxTimezone = new DateTimeZone($config->monitor->influxTimezone);

        $datetime = self::getDateTimeObject($datetime, $influxTimezone);

        $datetime->setTimezone($localTimezone);
        return $datetime;
    }

    protected static function prepareForDb($value)
    {
        if ($value instanceof DateTime) {
            $value = $value->format('Y-m-d H:i:s.u');
        }

        return $value;
    }


    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'gateway';
    }
}
