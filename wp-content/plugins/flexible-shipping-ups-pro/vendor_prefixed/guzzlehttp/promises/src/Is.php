<?php

namespace UpsProVendor\GuzzleHttp\Promise;

final class Is
{
    /**
     * Returns true if a promise is pending.
     *
     * @return bool
     */
    public static function pending(\UpsProVendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \UpsProVendor\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled or rejected.
     *
     * @return bool
     */
    public static function settled(\UpsProVendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() !== \UpsProVendor\GuzzleHttp\Promise\PromiseInterface::PENDING;
    }
    /**
     * Returns true if a promise is fulfilled.
     *
     * @return bool
     */
    public static function fulfilled(\UpsProVendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \UpsProVendor\GuzzleHttp\Promise\PromiseInterface::FULFILLED;
    }
    /**
     * Returns true if a promise is rejected.
     *
     * @return bool
     */
    public static function rejected(\UpsProVendor\GuzzleHttp\Promise\PromiseInterface $promise)
    {
        return $promise->getState() === \UpsProVendor\GuzzleHttp\Promise\PromiseInterface::REJECTED;
    }
}
