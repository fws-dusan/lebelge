<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FSConditionalMethodsVendor\Monolog\Handler;

use FSConditionalMethodsVendor\Monolog\Logger;
use FSConditionalMethodsVendor\Monolog\Formatter\NormalizerFormatter;
/**
 * Logs to a MongoDB database.
 *
 * usage example:
 *
 *   $log = new Logger('application');
 *   $mongodb = new MongoDBHandler(new \Mongo("mongodb://localhost:27017"), "logs", "prod");
 *   $log->pushHandler($mongodb);
 *
 * @author Thomas Tourlourat <thomas@tourlourat.com>
 */
class MongoDBHandler extends \FSConditionalMethodsVendor\Monolog\Handler\AbstractProcessingHandler
{
    protected $mongoCollection;
    public function __construct($mongo, $database, $collection, $level = \FSConditionalMethodsVendor\Monolog\Logger::DEBUG, $bubble = \true)
    {
        if (!($mongo instanceof \MongoClient || $mongo instanceof \Mongo || $mongo instanceof \FSConditionalMethodsVendor\MongoDB\Client)) {
            throw new \InvalidArgumentException('MongoClient, Mongo or MongoDB\\Client instance required');
        }
        $this->mongoCollection = $mongo->selectCollection($database, $collection);
        parent::__construct($level, $bubble);
    }
    protected function write(array $record)
    {
        if ($this->mongoCollection instanceof \FSConditionalMethodsVendor\MongoDB\Collection) {
            $this->mongoCollection->insertOne($record["formatted"]);
        } else {
            $this->mongoCollection->save($record["formatted"]);
        }
    }
    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new \FSConditionalMethodsVendor\Monolog\Formatter\NormalizerFormatter();
    }
}
