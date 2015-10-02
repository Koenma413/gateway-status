<?php

use Phalcon\DI;
class StatusUpdate extends \Phalcon\Mvc\Model
{

    /**
     *
     * @var integer
     */
    public $ID;

    /**
     *
     * @var integer
     */
    public $gateway_id;

    /**
     *
     * @var string
     */
    public $since_time;

     /**
     *
     * @var string
     */
    public $update_time;

    /**
     *
     * @var string
     */
    public $interval;

    /**
     *
     * @var integer
     */
    public $entries_seen;

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        $this->belongsTo('gateway_id', 'Gateway', 'ID', array('alias' => 'Gateway'));
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'status_update';
    }

    /**
     * Allows to query a set of records that match the specified conditions
     *
     * @param mixed $parameters
     * @return StatusUpdate[]
     */
    public static function find($parameters = null)
    {
        return parent::find($parameters);
    }

    /**
     * Allows to query the first record that match the specified conditions
     *
     * @param mixed $parameters
     * @return StatusUpdate
     */
    public static function findFirst($parameters = null)
    {
        return parent::findFirst($parameters);
    }
}
