<?php

namespace UpsProVendor\Ups\Entity;

use DOMDocument;
use DOMElement;
use UpsProVendor\Ups\NodeInterface;
class UPSFiled implements \UpsProVendor\Ups\NodeInterface
{
    /**
     * @var POA
     */
    private $poa;
    /**
     * @param null|object $attributes
     */
    public function __construct($attributes = null)
    {
        if (null !== $attributes) {
            if (isset($attributes->POA)) {
                $this->setPOA(new \UpsProVendor\Ups\Entity\POA($attributes->POA));
            }
        }
    }
    /**
     * @param null|DOMDocument $document
     *
     * @return DOMElement
     */
    public function toNode(\DOMDocument $document = null)
    {
        if (null === $document) {
            $document = new \DOMDocument();
        }
        $node = $document->createElement('UPSFiled');
        $poa = $this->getPOA();
        if (isset($poa)) {
            $node->appendChild($poa->toNode($document));
        }
        return $node;
    }
    /**
     * @return POA
     */
    public function getPOA()
    {
        return $this->poa;
    }
    /**
     * @return string
     */
    public function setPOA(\UpsProVendor\Ups\Entity\POA $poa)
    {
        $this->poa = $poa;
        return $this;
    }
}
