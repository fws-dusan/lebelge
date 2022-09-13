<?php

namespace UpsProVendor\Ups\Entity;

class FailureNotification
{
    public $FailedEmailAddress;
    public $FailureNotificationCode;
    /**
     * @param \stdClass|null $response
     */
    public function __construct(\stdClass $response = null)
    {
        $this->FailureNotificationCode = new \UpsProVendor\Ups\Entity\FailureNotificationCode();
        if (null !== $response) {
            if (isset($response->FailedEmailAddress)) {
                $this->FailedEmailAddress = $response->FailedEmailAddress;
            }
            if (isset($response->FailureNotificationCode)) {
                $this->FailureNotificationCode = new \UpsProVendor\Ups\Entity\FailureNotificationCode($response->FailureNotificationCode);
            }
        }
    }
}
