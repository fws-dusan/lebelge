<?php
namespace Wnsm;

use InvalidArgumentException;


class Settings
{
    const MSG_CART = 'MSG_CART';
    const MSG_CHECKOUT = 'MSG_CHECKOUT';
    const MSG_CHECKOUT_NOTICE = 'MSG_CHECKOUT_NOTICE';
    const MSG_ALL = [self::MSG_CART, self::MSG_CHECKOUT, self::MSG_CHECKOUT_NOTICE];

    const MSG_TYPE_NOOP = 'noop';
    const MSG_TYPE_TEXT = 'text';
    const MSG_TYPE_HTML = 'html';

    const SETTINGS_SECTION_ID = 'wnsm_shipping_unavailable_messages';


    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param string $msgId  self::MSG_XXX
     * @return string|null
     */
    public function getMsgHtml($msgId)
    {
        $msg = self::readAndNormalize($msgId);
        if (!isset($msg)) {
            return null;
        }

        list($type, $text) = $msg;

        $text = $this->app->translate($text);

        if ($type !== self::MSG_TYPE_HTML) {
            $text = nl2br(esc_html($text));
        }

        return $text;
    }

    public function getFields()
    {
        $select = function($msgId, $title, $descTip, $screenshot) {
            return  [
                'id' => self::getWpOptionNames($msgId)[0],
                'type' => 'select',
                'title' => $this->app->translate($title),
                'desc_tip' =>
                    $this->app->translate($descTip).'<br>'.
                    $this->app->translate('Click to see a screenshot.'),
                'options' => [
                    self::MSG_TYPE_NOOP => $this->app->translate('Do not change'),
                    self::MSG_TYPE_TEXT => $this->app->translate('Replace with plain text'),
                    self::MSG_TYPE_HTML => $this->app->translate('Replace with HTML'),
                ],
                'class' => 'wnsm-replace-select',
                'custom_attributes' => [
                    'data-screenshot' => $this->app->getAssetUrl("screenshots/{$screenshot}"),
                ],
                'autoload' => false,
            ];
        };

        $textarea = static function($msgId) {
            return [
                'id' => self::getWpOptionNames($msgId)[1],
                'type' => 'textarea',
                'class' => 'wnsm-replace-textarea',
                'custom_attributes' => [
                    'rows' => 4,
                ],
                'autoload' => false,
            ];
        };

        return [

            [
                'id' => $sectionId = self::SETTINGS_SECTION_ID,
                'type' => 'title',
                'title' => $this->app->translate('Shipping unavailable messages'),
                'desc' => $this->app->translate('Messages shown to the customer when no shipping options available.'),
            ],

            $select(
                self::MSG_CART,
                'On the cart page',
                'The message appearing in the Shipping section of the Cart page.',
                'cart.png'
            ),
            $textarea(self::MSG_CART),

            $select(
                self::MSG_CHECKOUT,
                'On the checkout page',
                'The message appearing in the Shipping section of the Checkout page.',
                'checkout.png'
            ),
            $textarea(self::MSG_CHECKOUT),

            $select(
                self::MSG_CHECKOUT_NOTICE,
                'Checkout notice',
                'The notice appearing on the Checkout page after the customer attempts to place an order without a shipping option selected.',
                'checkout-notice.png'
            ),
            $textarea(self::MSG_CHECKOUT_NOTICE),

            [
                'id' => $sectionId,
                'type' => 'sectionend',
            ]
        ];
    }

    public static function normalize()
    {
        foreach (self::MSG_ALL as $msgId) {
            self::readAndNormalize($msgId);
        }
    }


    /** @var App */
    private $app;

    /**
     * @return array($type, $text)|null
     */
    private static function readAndNormalize($msgId)
    {
        list($typeOption, $textOption) = self::getWpOptionNames($msgId);

        $text = get_option($textOption, null);
        $type = get_option($typeOption, null);

        // Keep the invariants:
        // type: null|'text'|'html'
        // text: null|trimmed non-empty string
        // text,type: null,null | trimmed non-empty string,null/'text'/'html'
        // (null value also means the corresponding wp option is removed)
        {
            if (isset($type) && !in_array($type, [self::MSG_TYPE_TEXT, self::MSG_TYPE_HTML], true)) {
                $type = null;
                delete_option($typeOption);
            }

            $trimmedText = trim($text);
            if ($trimmedText === '') {
                if (isset($text)) {
                    $text = null;
                    delete_option($textOption);
                }
                if (isset($type)) {
                    $type = null;
                    delete_option($typeOption);
                }
            }
            else if ($trimmedText !== $text) {
                $text = $trimmedText;
                update_option($textOption, $trimmedText);
            }
        }

        if (!isset($type)) {
            return null;
        }

        return [$type, $text];
    }

    private static function getWpOptionNames($msgId)
    {
        switch ($msgId) {
            case self::MSG_CART:
                return ['wnsm_msg_cart_type', 'wnsm_msg_cart_text'];
            case self::MSG_CHECKOUT:
                return ['wnsm_msg_checkout_type', 'wnsm_msg_checkout_text'];
            case self::MSG_CHECKOUT_NOTICE:
                return ['wnsm_msg_checkout_notice_type', 'wnsm_msg_checkout_notice_text'];
            default:
                throw new InvalidArgumentException("Unknown message id '{$msgId}'");
        }
    }
}