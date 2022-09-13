<?php

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace FlexibleShippingImportExportVendor\Monolog\Handler;

use FlexibleShippingImportExportVendor\Monolog\Logger;
use FlexibleShippingImportExportVendor\Monolog\Formatter\NormalizerFormatter;
use FlexibleShippingImportExportVendor\Doctrine\CouchDB\CouchDBClient;
/**
 * CouchDB handler for Doctrine CouchDB ODM
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class DoctrineCouchDBHandler extends \FlexibleShippingImportExportVendor\Monolog\Handler\AbstractProcessingHandler
{
    private $client;
    public function __construct(\FlexibleShippingImportExportVendor\Doctrine\CouchDB\CouchDBClient $client, $level = \FlexibleShippingImportExportVendor\Monolog\Logger::DEBUG, $bubble = \true)
    {
        $this->client = $client;
        parent::__construct($level, $bubble);
    }
    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $this->client->postDocument($record['formatted']);
    }
    protected function getDefaultFormatter()
    {
        return new \FlexibleShippingImportExportVendor\Monolog\Formatter\NormalizerFormatter();
    }
}
