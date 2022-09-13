<?php

use Objectiv\Plugins\Checkout\Action\AccountExistsAction;
use Objectiv\Plugins\Checkout\Action\ApplyCouponAction;
use Objectiv\Plugins\Checkout\Action\CompleteOrderAction;
use Objectiv\Plugins\Checkout\Action\LogInAction;
use Objectiv\Plugins\Checkout\Action\RemoveCouponAction;
use Objectiv\Plugins\Checkout\Action\UpdateCheckoutAction;
use Objectiv\Plugins\Checkout\Action\UpdatePaymentMethodAction;
use Objectiv\Plugins\Checkout\CartImageSizeAdder;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\AfterPayKrokedil;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\AmazonPayV1;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\Braintree;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\BraintreeForWooCommerce;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\In3;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\InpsydePayPalPlus;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\KlarnaCheckout;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\KlarnaPayment;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\PayPalCheckout;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\PayPalForWooCommerce;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\PayPalPlusCw;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\PostFinance;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\Square;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\Stripe;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\StripeWooCommerce;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\ToCheckout;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\Vipps;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\WooCommercePensoPay;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\WooSquarePro;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\ActiveCampaign;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\ApplyOnline;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\BeaverThemer;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\CartFlows;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\CheckoutAddressAutoComplete;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\CheckoutManager;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\Chronopost;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\CO2OK;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\CountryBasedPayments;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\CraftyClicks;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\CSSHero;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\DiviUltimateFooter;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\DiviUltimateHeader;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\ElementorPro;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\EnhancedEcommerceGoogleAnalytics;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\EUVATNumber;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\ExtraCheckoutFieldsBrazil;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\FacebookForWooCommerce;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\Fattureincloud;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\GermanMarket;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\GoogleAnalyticsPro;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\IconicWooCommerceDeliverySlots;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\IndeedAffiliatePro;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\JupiterXCore;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\Klaviyo;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\MailerLite;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\MartfuryAddons;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\MixPanel;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\MondialRelay;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\MyCredPartialPayments;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\MyShipper;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\NextGenGallery;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\NIFPortugal;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\NLPostcodeChecker;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\OneClickUpsells;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\OrderDeliveryDate;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\OrderDeliveryDateLite;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\OxygenBuilder;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\PixelCaffeine;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\PortugalVaspKios;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\PostNL;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\PWGiftCardsPro;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\SalientWPBakery;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\SavedAddressesForWooCommerce;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\SendCloud;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\ShipMondo;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\SkyVergeCheckoutAddons;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\SkyVergeSocialLogin;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\StrollikCore;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\SUMOPaymentPlans;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\SUMOSubscriptions;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\Tickera;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\TranslatePress;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\UltimateRewardsPoints;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\UpsellOrderBumpOffer;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WCFieldFactory;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WCPont;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\Webshipper;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\Weglot;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceAddressValidation;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceAdvancedMessages;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceCarrierAgents;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceCheckoutFieldEditor;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceCore;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceExtendedCouponFeaturesPro;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceGermanized;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceGermanMarket;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceGiftCards;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceOrderDelivery;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommercePointsandRewards;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommercePriceBasedOnCountry;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceServices;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceSmartCoupons;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceSubscriptionGifting;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommerceSubscriptions;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooFunnelsOrderBumps;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WPCProductBundles;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WPWebWooCommerceSocialLogin;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\YITHCompositeProducts;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\YITHDeliveryDate;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\ThemeHighCheckoutFieldEditor;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Astra;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Atelier;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Atik;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Avada;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Barberry;
use Objectiv\Plugins\Checkout\Compatibility\Themes\BeaverBuilder;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Blaszok;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Divi;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Electro;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Flatsome;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Flevr;
use Objectiv\Plugins\Checkout\Compatibility\Themes\FuelThemes;
use Objectiv\Plugins\Checkout\Compatibility\Themes\GeneratePress;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Genesis;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Jupiter;
use Objectiv\Plugins\Checkout\Compatibility\Themes\JupiterX;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Konte;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Listable;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Neve;
use Objectiv\Plugins\Checkout\Compatibility\Themes\OceanWP;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Optimizer;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Porto;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Pro;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Savoy;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Shoptimizer;
use Objectiv\Plugins\Checkout\Compatibility\Themes\SpaSalonPro;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Stockie;
use Objectiv\Plugins\Checkout\Compatibility\Themes\The7;
use Objectiv\Plugins\Checkout\Compatibility\Themes\TheBox;
use Objectiv\Plugins\Checkout\Compatibility\Themes\TMOrganik;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Tokoo;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Uncode;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Verso;
use Objectiv\Plugins\Checkout\Compatibility\Themes\Zidane;
use Objectiv\Plugins\Checkout\Admin\DataUpgrader;
use Objectiv\Plugins\Checkout\Admin\Notices\CompatibilityNotice;
use Objectiv\Plugins\Checkout\Admin\Notices\InvalidLicenseKeyNotice;
use Objectiv\Plugins\Checkout\Admin\Notices\TemplateDisabledNotice;
use Objectiv\Plugins\Checkout\Admin\Notices\WelcomeNotice;
use Objectiv\Plugins\Checkout\Admin\Pages\Addons;
use Objectiv\Plugins\Checkout\Admin\Pages\Advanced;
use Objectiv\Plugins\Checkout\Admin\Pages\Appearance;
use Objectiv\Plugins\Checkout\Admin\Pages\CartSummary;
use Objectiv\Plugins\Checkout\Admin\Pages\Checkout;
use Objectiv\Plugins\Checkout\Admin\Pages\General;
use Objectiv\Plugins\Checkout\Customizer;
use Objectiv\Plugins\Checkout\Managers\StyleManager;
use Objectiv\Plugins\Checkout\Model\Template;
use Objectiv\Plugins\Checkout\Features\CartEditing;
use Objectiv\Plugins\Checkout\Managers\PlanManager;
use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use Objectiv\Plugins\Checkout\Managers\UpdatesManager;
use Objectiv\Plugins\Checkout\Features\OrderBumps;
use Objectiv\Plugins\Checkout\Stats\StatCollection;
use Objectiv\Plugins\Checkout\Admin\Pages\OrderPay;
use Objectiv\Plugins\Checkout\Admin\Pages\PageController;
use Objectiv\Plugins\Checkout\Admin\Pages\Support;
use Objectiv\Plugins\Checkout\Admin\Pages\ThankYou;
use Objectiv\Plugins\Checkout\Admin\ShippingPhoneController;
use Objectiv\Plugins\Checkout\Admin\WooCommerceAdminScreenAugmenter;
use Objectiv\Plugins\Checkout\TrustBadgeImageSizeAdder;
use Objectiv\Plugins\Checkout\Features\TrustBadges;
use Objectiv\Plugins\Checkout\Features\PhpSnippets;
use Objectiv\Plugins\Checkout\Features\AddressAutocomplete;
use Objectiv\Plugins\Checkout\Features\FetchifyAddressAutocomplete;
use Objectiv\Plugins\Checkout\Features\OrderReviewStep;
use Objectiv\Plugins\Checkout\Features\OnePageCheckout;
use Objectiv\Plugins\Checkout\FormAugmenter;
use Objectiv\Plugins\Checkout\PluginActivationDeactivationHandler;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\OnePageCheckout as WooCommerceOnePageCheckout;
use Objectiv\Plugins\Checkout\Admin\AdminPluginsPageManager;
use Objectiv\Plugins\Checkout\Features\SmartyStreets;
use Objectiv\Plugins\Checkout\Compatibility\CompatibilityAbstract;
use Objectiv\Plugins\Checkout\Action\LostPasswordAction;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\AmazonPay;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\AmazonPayLegacy;
use Objectiv\Plugins\Checkout\Admin\Pages\OrderBumps as PagesOrderBumps;
use Objectiv\Plugins\Checkout\Model\Bump;
use Objectiv\Plugins\Checkout\PhpErrorOutputSuppressor;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooCommercePakettikauppa;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\WooFinvoicer;
use Objectiv\Plugins\Checkout\Compatibility\Plugins\ConvertKitforWooCommerce;
use Objectiv\Plugins\Checkout\Compatibility\Gateways\WooCommercePayments;


// Setup our Singletons here
$settings_manager = SettingsManager::instance();
$settings_manager->init();

FormAugmenter::instance();

$stats_collection = StatCollection::instance();
$stats_collection->init();

UpdatesManager::instance()->init();

// TODO: This should eventually be removed...right? Probably want to grandfather people who have had this active and only disable for new installs
( new PhpErrorOutputSuppressor() )->init();

/**
 * Plan Availability
 */
$has_plus_plan            = PlanManager::has_required_plan( PlanManager::PLUS );
$has_pro_plan             = PlanManager::has_required_plan( PlanManager::PRO );
$plus_required_plans_list = PlanManager::get_english_list_of_required_plans_html( PlanManager::PLUS );
$pro_required_plans_list  = PlanManager::get_english_list_of_required_plans_html( PlanManager::PRO );

/**
 * Admin Settings Pages
 */
// Handles Parent Menu and General Menu
$general_admin_page    = new General();
$appearance_admin_page = new Appearance( $settings_manager );

// These priorities start at 70 because General sets up the main menu on $priority - 5
// 65 is our target priority for our admin parent menu
$pages = array(
	$general_admin_page->set_priority( 70 ),
	( new Checkout( $general_admin_page->get_url() ) )->set_priority( 75 ),
	( new OrderPay() )->set_priority( 80 ),
	( new ThankYou() )->set_priority( 85 ),
	( new CartSummary() )->set_priority( 90 ),
	( new PagesOrderBumps( Bump::get_post_type(), $pro_required_plans_list, $has_pro_plan ) )->set_priority( 95 ),
	$appearance_admin_page->set_priority( 100 ),
	( new Advanced() )->set_priority( 105 ),
	( new Addons() )->set_priority( 110 ),
	( new Support() )->set_priority( 115 ),
);

$page_controller = new PageController( ...$pages );
$page_controller->init();

// Note: The active template has to be setup early because admin pages use it to store active theme specific settings
// The fact that a "get" function is causing outside changes in the ether is an indication this should be refactored.
$active_template = cfw_get_active_template();

/**
 * Premium Features Instantiation
 */
$order_bumps_feature = new OrderBumps( $has_pro_plan, $pro_required_plans_list, $settings_manager );
$order_bumps_feature->init();
add_action( 'cfw_angelleye_paypal_ec_is_express_checkout', array( $order_bumps_feature, 'unhook_order_bumps_output' ) );

$order_review_step = new OrderReviewStep(
	$settings_manager->get_setting( 'enable_order_review_step' ) === 'yes',
	$has_plus_plan,
	$plus_required_plans_list,
	$settings_manager
);
$order_review_step->init();
add_action( 'cfw_angelleye_paypal_ec_is_express_checkout', array( $order_review_step, 'unhook' ) );

$one_page_checkout = new OnePageCheckout(
	$settings_manager->get_setting( 'enable_one_page_checkout' ) === 'yes',
	$has_plus_plan,
	$plus_required_plans_list,
	$settings_manager
);
$one_page_checkout->init();

$address_autocomplete = new AddressAutocomplete(
	$settings_manager->get_setting( 'enable_address_autocomplete' ) === 'yes',
	$has_plus_plan,
	$plus_required_plans_list,
	$settings_manager,
	$general_admin_page->get_url()
);
$address_autocomplete->init();

$fetchify_address_autocomplete = new FetchifyAddressAutocomplete(
	$settings_manager->get_setting( 'enable_fetchify_address_autocomplete' ) === 'yes',
	$has_pro_plan,
	$pro_required_plans_list,
	$settings_manager
);
$fetchify_address_autocomplete->init();

$trust_badges = new TrustBadges(
	$settings_manager->get_setting( 'enable_trust_badges' ) === 'yes',
	$has_plus_plan,
	$plus_required_plans_list,
	$settings_manager,
	$settings_manager->get_field_name( 'trust_badges' )
);
$trust_badges->init();

$smartystreets_address_validation = new SmartyStreets(
	$settings_manager->get_setting( 'enable_smartystreets_integration' ) === 'yes',
	$has_pro_plan,
	$pro_required_plans_list,
	$settings_manager
);
$smartystreets_address_validation->init();

$php_snippets = new PhpSnippets(
	! is_admin(),
	$has_plus_plan,
	$plus_required_plans_list,
	$settings_manager,
	$settings_manager->get_field_name( 'php_snippets' )
);
$php_snippets->init();

$cart_editing = new CartEditing(
	$settings_manager->get_setting( 'enable_cart_editing' ) === 'yes',
	$has_plus_plan,
	$plus_required_plans_list,
	$settings_manager
);
$cart_editing->init();

/**
 * Setup Compatibility Modules
 */
$compatibility_modules = array(
	// Plugins
	WooCommerceCore::instance(),
	MixPanel::instance(),
	SkyVergeCheckoutAddons::instance(),
	Tickera::instance(),
	PixelCaffeine::instance(),
	OneClickUpsells::instance(),
	GoogleAnalyticsPro::instance(),
	WooCommerceOnePageCheckout::instance(),
	WooCommerceSubscriptions::instance(),
	WooCommerceSubscriptionGifting::instance(),
	WooCommerceGermanized::instance()->setup( $order_review_step->is_active() && ! $one_page_checkout->is_active() ),
	CraftyClicks::instance(),
	CheckoutManager::instance(),
	CheckoutAddressAutoComplete::instance(),
	NLPostcodeChecker::instance(),
	PostNL::instance(),
	ActiveCampaign::instance(),
	UltimateRewardsPoints::instance(),
	WooCommerceSmartCoupons::instance(),
	EUVATNumber::instance(),
	SkyVergeSocialLogin::instance(),
	WooCommercePriceBasedOnCountry::instance(),
	FacebookForWooCommerce::instance(),
	Webshipper::instance(),
	OrderDeliveryDate::instance(),
	OrderDeliveryDateLite::instance(),
	WooFunnelsOrderBumps::instance(),
	MartfuryAddons::instance(),
	WCFieldFactory::instance(),
	MondialRelay::instance(),
	SUMOPaymentPlans::instance(),
	WooCommerceAddressValidation::instance(),
	ElementorPro::instance(),
	SendCloud::instance(),
	CO2OK::instance(),
	DiviUltimateHeader::instance(),
	DiviUltimateFooter::instance(),
	ExtraCheckoutFieldsBrazil::instance(),
	MyCredPartialPayments::instance(),
	CountryBasedPayments::instance(),
	GermanMarket::instance(),
	StrollikCore::instance(),
	WooCommerceCheckoutFieldEditor::instance(),
	IndeedAffiliatePro::instance(),
	ShipMondo::instance(),
	Chronopost::instance(),
	JupiterXCore::instance(),
	OxygenBuilder::instance(),
	Fattureincloud::instance(),
	CSSHero::instance(),
	NIFPortugal::instance(),
	WooCommerceOrderDelivery::instance(),
	PortugalVaspKios::instance(),
	WPCProductBundles::instance(),
	YITHDeliveryDate::instance(),
	CartFlows::instance(),
	PWGiftCardsPro::instance(),
	NextGenGallery::instance(),
	Weglot::instance(),
	WooCommerceGiftCards::instance(), // WooCommerce Gift Cards (official)
	BeaverThemer::instance(),
	WooCommerceCarrierAgents::instance(),
	WooCommerceServices::instance(),
	SalientWPBakery::instance(),
	WPWebWooCommerceSocialLogin::instance(),
	WCPont::instance(),
	MailerLite::instance(),
	ApplyOnline::instance(),
	YITHCompositeProducts::instance(),
	WooCommerceExtendedCouponFeaturesPro::instance(),
	WooCommerceSubscriptionGifting::instance(),
	WooCommerceGermanMarket::instance(),
	IconicWooCommerceDeliverySlots::instance(),
	MyShipper::instance(),
	EnhancedEcommerceGoogleAnalytics::instance(),
	WooCommercePointsandRewards::instance(),
	SavedAddressesForWooCommerce::instance(),
	TranslatePress::instance(),
	SUMOSubscriptions::instance(),
	UpsellOrderBumpOffer::instance(),
	WooCommerceAdvancedMessages::instance(),
	Klaviyo::instance(),
	ThemeHighCheckoutFieldEditor::instance(),
	WooCommercePakettikauppa::instance(),
	WooFinvoicer::instance(),

	// Gateways
	PayPalCheckout::instance(),
	Stripe::instance(),
	PayPalForWooCommerce::instance(),
	Braintree::instance(),
	BraintreeForWooCommerce::instance(),
	AmazonPay::instance(),
	AmazonPayLegacy::instance(),
	AmazonPayV1::instance(),
	KlarnaCheckout::instance(),
	KlarnaPayment::instance(),
	AfterPayKrokedil::instance(),
	ToCheckout::instance(),
	In3::instance(),
	InpsydePayPalPlus::instance(),
	WooSquarePro::instance(),
	PayPalPlusCw::instance(),
	PostFinance::instance(),
	Square::instance(),
	StripeWooCommerce::instance(),
	WooCommercePensoPay::instance(),
	Vipps::instance(),
	ConvertKitforWooCommerce::instance(),
	WooCommercePayments::instance(),

	// Themes
	Avada::instance(),
	Porto::instance(),
	GeneratePress::instance(),
	TMOrganik::instance(),
	BeaverBuilder::instance(),
	Astra::instance(),
	Savoy::instance(),
	OceanWP::instance(),
	Atelier::instance(),
	Jupiter::instance(),
	The7::instance(),
	Zidane::instance(),
	Atik::instance(),
	Optimizer::instance(),
	Verso::instance(),
	Listable::instance(),
	Flevr::instance(),
	Divi::instance(),
	Electro::instance(),
	JupiterX::instance(),
	Blaszok::instance(),
	Konte::instance(),
	Genesis::instance(),
	TheBox::instance(),
	Barberry::instance(),
	Stockie::instance(),
	Tokoo::instance(),
	FuelThemes::instance(),
	SpaSalonPro::instance(),
	Shoptimizer::instance(),
	Flatsome::instance(),
	Pro::instance(),
	Uncode::instance(),
	Neve::instance(),
);

/**
 * WP Admin Notices
 */
( new TemplateDisabledNotice() )->init();
( new InvalidLicenseKeyNotice() )->init();
( new CompatibilityNotice() )->init();
( new WelcomeNotice() )->init();

/**
 * Misc Admin Stuff That Defies Cogent Categorization For The Moment
 */
( new AdminPluginsPageManager( $general_admin_page->get_url() ) )->init();
( new ShippingPhoneController() )->init();
( new WooCommerceAdminScreenAugmenter() )->init();
( new CartImageSizeAdder() )->add_cart_image_size();
( new TrustBadgeImageSizeAdder() )->add_trust_badge_image_size();
CartFlows::instance()->admin_init();

add_action( 'init', function() {
	( new DataUpgrader() )->init();
} );

/**
 * Customizer Handler
 */
( new Customizer( $appearance_admin_page->get_theme_color_settings() ) )->init();

/**
 * Custom Styles
 */
add_action(
	'cfw_custom_styles',
	function() {
		StyleManager::output_styles();
	}
);

add_action(
	'after_setup_theme',
	function() {
		// Menu location for template footer
		register_nav_menu( 'cfw-footer-menu', cfw__( 'CheckoutWC: Footer Menu', 'checkout-wc' ) );
	}
);

add_action(
	'plugins_loaded',
	function() use ( $compatibility_modules ) {
		/**
		 * Compatibility Pre-init
		 *
		 * Turns out running this on init causes problems, and plugins_loaded is also too late
		 * Obviously this is something we need to cleanup in the future, but doing this
		 * here eliminates some edge case bugs.
		 */
		/** @var CompatibilityAbstract $module */
		foreach ( $compatibility_modules as $module ) {
			$module->pre_init();
		}
	},
	-1000
);

// Plugin Activation & Deactivation
$activation_deactivation_handler = new PluginActivationDeactivationHandler();

$activation_deactivation_handler->add_activation_handler( $stats_collection );
$activation_deactivation_handler->add_activation_handler( UpdatesManager::instance() );
$activation_deactivation_handler->add_activation_handler( $active_template );
$activation_deactivation_handler->add_activation_handler( $address_autocomplete );
$activation_deactivation_handler->add_activation_handler( $fetchify_address_autocomplete );
$activation_deactivation_handler->add_activation_handler( $cart_editing );
$activation_deactivation_handler->add_activation_handler( $one_page_checkout );
$activation_deactivation_handler->add_activation_handler( $order_review_step );
$activation_deactivation_handler->add_activation_handler( $php_snippets );
$activation_deactivation_handler->add_activation_handler( $trust_badges );
$activation_deactivation_handler->add_activation_handler( $smartystreets_address_validation );

$activation_deactivation_handler->add_deactivation_handler( $stats_collection );
$activation_deactivation_handler->add_deactivation_handler( UpdatesManager::instance() );

add_action(
	'cfw_do_plugin_activation',
	function() use ( $settings_manager ) {
		$settings_manager->add_setting( 'enable', 'no' );
		$settings_manager->add_setting( 'login_style', 'enhanced' );
		$settings_manager->add_setting( 'registration_style', 'enhanced' );
		$settings_manager->add_setting( 'label_style', 'floating' );
		$settings_manager->add_setting( 'cart_item_link', 'disabled' );
		$settings_manager->add_setting( 'cart_item_data_display', 'short' );
		$settings_manager->add_setting( 'skip_shipping_step', 'no' );
		$settings_manager->add_setting( 'enable_order_notes', 'no' );
		$settings_manager->add_setting( 'active_template', 'default' );
		$settings_manager->add_setting( 'allow_checkout_field_editor_address_modification', 'no' );
		$settings_manager->add_setting( 'enable_elementor_pro_support', 'no' );
		$settings_manager->add_setting( 'enable_beaver_themer_support', 'no' );
		$settings_manager->add_setting( 'template_loader', 'redirect' );
		$settings_manager->add_setting( 'override_view_order_template', 'yes' );
		$settings_manager->add_setting( 'show_logos_mobile', 'no' );
		$settings_manager->add_setting( 'show_mobile_coupon_field', 'no' );
		$settings_manager->add_setting( 'enable_order_pay', 'no' );
		$settings_manager->add_setting( 'enable_thank_you_page', 'no' );
		$settings_manager->add_setting( 'thank_you_order_statuses', 'no' );
		$settings_manager->add_setting( 'enable_map_embed', 'no' );
		$settings_manager->add_setting( 'override_view_order_template', 'no' );
		$settings_manager->add_setting( 'google_places_api_key', '' );
		$settings_manager->add_setting( 'user_matching', 'enabled' );

		foreach ( cfw_get_available_templates() as $template ) {
			$breadcrumb_completed_text_color   = '#7f7f7f';
			$breadcrumb_current_text_color     = '#333333';
			$breadcrumb_next_text_color        = '#7f7f7f';
			$breadcrumb_completed_accent_color = '#333333';
			$breadcrumb_current_accent_color   = '#333333';
			$breadcrumb_next_accent_color      = '#333333';

			if ( $template->get_slug() === 'glass' ) {
				$breadcrumb_current_text_color   = $settings_manager->get_setting( 'button_color', array( 'glass' ) );
				$breadcrumb_current_accent_color = $settings_manager->get_setting( 'button_color', array( 'glass' ) );
				$breadcrumb_next_text_color      = '#dfdcdb';
				$breadcrumb_next_accent_color    = '#dfdcdb';

			} elseif ( $template->get_slug() === 'futurist' ) {
				$breadcrumb_completed_text_color   = '#222222';
				$breadcrumb_current_text_color     = '#222222';
				$breadcrumb_next_text_color        = '#222222';
				$breadcrumb_completed_accent_color = '#222222';
				$breadcrumb_current_accent_color   = '#222222';
				$breadcrumb_next_accent_color      = '#222222';
			}

			$settings_manager->update_setting( 'breadcrumb_completed_text_color', $breadcrumb_completed_text_color, true, array( $template->get_slug() ) );
			$settings_manager->update_setting( 'breadcrumb_current_text_color', $breadcrumb_current_text_color, true, array( $template->get_slug() ) );
			$settings_manager->update_setting( 'breadcrumb_next_text_color', $breadcrumb_next_text_color, true, array( $template->get_slug() ) );
			$settings_manager->update_setting( 'breadcrumb_completed_accent_color', $breadcrumb_completed_accent_color, true, array( $template->get_slug() ) );
			$settings_manager->update_setting( 'breadcrumb_current_accent_color', $breadcrumb_current_accent_color, true, array( $template->get_slug() ) );
			$settings_manager->update_setting( 'breadcrumb_next_accent_color', $breadcrumb_next_accent_color, true, array( $template->get_slug() ) );
		}

		$custom_logo_id = get_theme_mod( 'custom_logo' );

		if ( $custom_logo_id ) {
			$settings_manager->add_setting( 'logo_attachment_id', $custom_logo_id );
		}
	}
);

register_activation_hook(
	CFW_MAIN_FILE,
	function() use ( $activation_deactivation_handler ) {
		$activation_deactivation_handler->activate();
		do_action( 'cfw_do_plugin_activation' );
	}
);

register_deactivation_hook( CFW_MAIN_FILE, array( $activation_deactivation_handler, 'deactivate' ) );

add_filter(
	'cfw_disable_woocommerce_gift_cards_compatibility',
	function() {
		return class_exists( '\\WC_GC_Coupon_Input' );
	}
);

/**
 * Permissioned Init
 *
 * Nothing south of this check should run if templates aren't enabled / plugin is licensed
 *
 * This has to run on init because we need to be able to use current_user_can()
 * See: https://wordpress.stackexchange.com/questions/198185/what-is-valid-timing-of-using-current-user-can-and-related-functions
 */
add_action(
	'init',
	function() use ( $active_template, $compatibility_modules, $settings_manager, $smartystreets_address_validation ) {
		if ( ! cfw_is_enabled() ) {
			return;
		}

		// Load Translations
		load_plugin_textdomain(
			'checkout-wc',
			false,
			dirname( plugin_basename( CFW_MAIN_FILE ) ) . '/languages'
		);

		// Enqueue Template Assets and Load Template functions.php file
		Template::init_active_template( $active_template );

		// Init Compatibility Modules
		/** @var CompatibilityAbstract $module */
		foreach ( $compatibility_modules as $module ) {
			$module->init();
		}

		/**
		 * Setup Ajax Action Listeners
		 */
		( new AccountExistsAction() )->load();
		( new LogInAction() )->load();
		( new CompleteOrderAction() )->load();
		( new ApplyCouponAction() )->load();
		( new RemoveCouponAction() )->load();
		( new UpdateCheckoutAction() )->load();
		( new UpdatePaymentMethodAction() )->load();
		( new LostPasswordAction() )->load();
		$smartystreets_address_validation->load_ajax_action();

		// Override some WooCommerce Options
		add_filter(
			'pre_option_woocommerce_registration_generate_password',
			function( $result ) use ( $settings_manager ) {
				if ( cfw_is_checkout() && apply_filters( 'cfw_registration_generate_password', $settings_manager->get_setting( 'registration_style' ) !== 'woocommerce' ) ) {
					return 'yes';
				}

				return $result;
			},
			10,
			1
		);

		add_filter(
			'pre_option_woocommerce_registration_generate_username',
			function( $result ) {
				if ( cfw_is_checkout() ) {
					$result = 'yes';
				}

				return $result;
			},
			10,
			1
		);

		if ( PlanManager::can_access_feature( 'enable_thank_you_page', PlanManager::PLUS ) && PlanManager::can_access_feature( 'override_view_order_template', PlanManager::PLUS ) ) {
			add_filter(
				'woocommerce_get_view_order_url',
				function( $url, WC_Order $order ) {
					return add_query_arg( 'view', 'true', $order->get_checkout_order_received_url() );
				},
				100,
				2
			);
		}

		/**
		 * User matching
		 */
		if ( PlanManager::can_access_feature( 'user_matching', PlanManager::PLUS ) ) {
			// Match new guest orders to accounts
			add_action( 'woocommerce_new_order', 'cfw_maybe_match_new_order_to_user_account', 10, 1 );

			// Match old guest orders to accounts on registration
			add_action( 'woocommerce_created_customer', 'cfw_maybe_link_orders_at_registration', 10, 1 );
		}

		if ( $settings_manager->get_setting( 'enable_order_notes' ) === 'yes' ) {
			add_filter( 'woocommerce_enable_order_notes_field', '__return_true' );
		}

		/**
		 * Load Frontend Handlers
		 */
		add_action( 'wp', array( FormAugmenter::instance(), 'init' ), 1 );
		add_action( 'wp', 'cfw_frontend', 1 );
	},
	1
);
