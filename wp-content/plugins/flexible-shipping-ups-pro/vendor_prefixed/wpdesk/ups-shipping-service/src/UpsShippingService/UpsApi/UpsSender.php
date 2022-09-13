<?php

/**
 * UPS API: Send request.
 *
 * @package WPDesk\UpsShippingService\UpsApi
 */
namespace UpsProVendor\WPDesk\UpsShippingService\UpsApi;

use Psr\Log\LoggerInterface;
use UpsProVendor\Ups\Entity\RateRequest;
use UpsProVendor\Ups\Entity\RateResponse;
use UpsProVendor\Ups\Exception\InvalidResponseException;
use UpsProVendor\Ups\Rate;
use UpsProVendor\WPDesk\AbstractShipping\Exception\RateException;
/**
 * Send request to UPS API
 */
class UpsSender implements \UpsProVendor\WPDesk\UpsShippingService\UpsApi\Sender
{
    /**
     * Access key.
     *
     * @var string
     */
    private $access_key;
    /**
     * User id.
     *
     * @var string
     */
    private $user_id;
    /**
     * Password.
     *
     * @var string
     */
    private $password;
    /**
     * Is tax enabled.
     *
     * @var bool
     */
    private $is_tax_enabled;
    /**
     * Logger
     *
     * @var LoggerInterface
     */
    private $logger;
    /**
     * Is testing?
     *
     * @var bool
     */
    private $is_testing;
    /**
     * UpsSender constructor.
     *
     * @param string          $access_key .
     * @param string          $user_id .
     * @param string          $password .
     * @param LoggerInterface $logger Logger.
     * @param bool            $is_testing Is testing?.
     * @param bool            $is_tax_enabled Is tax enabled?.
     */
    public function __construct($access_key, $user_id, $password, \Psr\Log\LoggerInterface $logger, $is_testing = \false, $is_tax_enabled = \true)
    {
        $this->access_key = $access_key;
        $this->user_id = $user_id;
        $this->password = $password;
        $this->logger = $logger;
        $this->is_testing = $is_testing;
        $this->is_tax_enabled = $is_tax_enabled;
    }
    /**
     * .
     *
     * @return string
     */
    public function get_access_key()
    {
        return $this->access_key;
    }
    /**
     * .
     *
     * @return string
     */
    public function get_user_id()
    {
        return $this->user_id;
    }
    /**
     * .
     *
     * @return string
     */
    public function get_password()
    {
        return $this->password;
    }
    /**
     * .
     *
     * @return bool
     */
    public function is_tax_enabled()
    {
        return $this->is_tax_enabled;
    }
    /**
     * .
     *
     * @return LoggerInterface
     */
    public function get_logger()
    {
        return $this->logger;
    }
    /**
     * .
     *
     * @return bool
     */
    public function is_testing()
    {
        return $this->is_testing;
    }
    /**
     * Send request.
     *
     * @param RateRequest $request UPS request.
     *
     * @return RateResponse
     *
     * @throws \Exception .
     * @throws RateException .
     */
    public function send(\UpsProVendor\Ups\Entity\RateRequest $request)
    {
        $rate = new \UpsProVendor\Ups\Rate($this->access_key, $this->user_id, $this->password, $this->is_testing, $this->logger);
        try {
            $reply = $rate->shopRates($request);
        } catch (\UpsProVendor\Ups\Exception\InvalidResponseException $e) {
            throw new \UpsProVendor\WPDesk\AbstractShipping\Exception\RateException($e->getMessage(), ['exception' => $e->getCode()]);
            //phpcs:ignore
        }
        $rate_interpretation = new \UpsProVendor\WPDesk\UpsShippingService\UpsApi\UpsRateReplyInterpretation($reply, $this->is_tax_enabled);
        if ($rate_interpretation->has_reply_error()) {
            throw new \UpsProVendor\WPDesk\AbstractShipping\Exception\RateException($rate_interpretation->get_reply_message(), ['response' => $reply]);
            //phpcs:ignore
        }
        return $reply;
    }
}
