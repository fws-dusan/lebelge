<?php

namespace Objectiv\Plugins\Checkout\Action;

use Objectiv\Plugins\Checkout\Managers\SettingsManager;
use SmartyStreets\PhpSdk\ClientBuilder;
use SmartyStreets\PhpSdk\International_Street\Client as InternationalStreetApiClient;
use SmartyStreets\PhpSdk\International_Street\Lookup;
use SmartyStreets\PhpSdk\StaticCredentials;
use SmartyStreets\PhpSdk\US_Street\Client as USStreetApiClient;

/**
 * Class LogInAction
 *
 * @link checkoutwc.com
 * @since 1.0.0
 * @package Objectiv\Plugins\Checkout\Action
 * @author Brandon Tassone <brandontassone@gmail.com>
 */
class SmartyStreetsAddressValidationAction extends CFWAction {
	protected $smartystreets_auth_id;
	protected $smartystreets_auth_token;

	/**
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $smartystreets_auth_id, $smartystreets_auth_token ) {
		parent::__construct( 'cfw_smartystreets_address_validation', false, 'wc_ajax_' );

		$this->smartystreets_auth_id    = $smartystreets_auth_id;
		$this->smartystreets_auth_token = $smartystreets_auth_token;
	}

	/**
	 * Logs in the user based on the information passed. If information is incorrect it returns an error message
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function action() {
		$auth_id        = $this->smartystreets_auth_id;
		$auth_token     = $this->smartystreets_auth_token;
		$credentials    = new StaticCredentials( $auth_id, $auth_token );
		$client_builder = new ClientBuilder( $credentials );
		$replace_start  = 'checkoutwc_0';
		$replace_end    = 'checkoutwc_1';

		$client = 'US' === $_POST['address']['country'] ? $client_builder->buildUsStreetApiClient() : $client_builder->buildInternationalStreetApiClient();

		try {
			if ( ! is_array( $_POST['address'] ) ) {
				throw new \Exception( 'POST address is not a valid array of address info.' );
			}

			$address            = $this->getAddressSuggestion( $_POST['address'], $client, $_POST['address']['country'] );
			$original           = $_POST['address'];
			$changed_components = array_keys( array_diff_assoc( $address, $original ) );

			if ( empty( $changed_components ) ) {
				$this->out(
					array(
						'result'  => false,
						'message' => 'Suggested address matched input address',
					)
				);
			}

			$poisoned_address = $address;

			foreach ( $changed_components as $changed_component ) {
				$poisoned_address[ $changed_component ] = "{$replace_start}{$address[$changed_component]}{$replace_end}";
			}

			$output_address = WC()->countries->get_formatted_address( $poisoned_address );
			$output_address = str_replace( $replace_start, '<span style="color:red">', $output_address );
			$output_address = str_replace( $replace_end, '</span>', $output_address );

			$this->out(
				array(
					'result'     => true,
					'address'    => stripslashes( $output_address ),
					'original'   => stripslashes( WC()->countries->get_formatted_address( $original ) ),
					'components' => $address,
				)
			);
		} catch ( \Exception $ex ) {
			$this->out(
				array(
					'result'  => false,
					'message' => $ex->getMessage(),
				)
			);
		}
	}

	/**
	 * @param array $address
	 * @param USStreetApiClient|InternationalStreetApiClient $client
	 * @param string $country
	 * @return array
	 * @throws \SmartyStreets\PhpSdk\Exceptions\SmartyException
	 */
	function getAddressSuggestion( array $address, $client, string $country = 'US' ): array {
		if ( 'US' === $country ) {
			return $this->getDomesticAddressSuggestion( $address, $client );
		} else {
			return $this->getInternationalAddressSuggestion( $address, $client );
		}
	}

	/**
	 * @param array $address
	 * @param USStreetApiClient $client
	 * @return array
	 * @throws \SmartyStreets\PhpSdk\Exceptions\SmartyException
	 */
	function getDomesticAddressSuggestion( array $address, USStreetApiClient $client ): array {
		$lookup = new \SmartyStreets\PhpSdk\US_Street\Lookup();

		$lookup->setStreet( $address['address_1'] );
		$lookup->setStreet2( $address['address_2'] );
		$lookup->setCity( $address['city'] );
		$lookup->setState( $address['state'] );
		$lookup->setZipcode( $address['postcode'] );
		$lookup->setMaxCandidates( 1 );
		$lookup->setMatchStrategy( 'invalid' );

		$client->sendLookup( $lookup ); // The candidates are also stored in the lookup's 'result' field.

		/** @var \SmartyStreets\PhpSdk\US_Street\Candidate $first_candidate */
		$first_candidate = $lookup->getResult()[0];

		$suggested_address   = $first_candidate->getDeliveryLine1();
		$suggested_address_2 = $first_candidate->getDeliveryLine2();
		$suggested_postcode  = $first_candidate->getComponents()->getZipcode();
		$suggested_state     = $first_candidate->getComponents()->getStateAbbreviation();
		$suggested_city      = $first_candidate->getComponents()->getCityName();

		return array(
			'address_1' => $suggested_address,
			'address_2' => $suggested_address_2,
			'city'      => $suggested_city,
			'state'     => $suggested_state,
			'postcode'  => $suggested_postcode,
			'country'   => 'US',
			'company'   => $address['company'],
		);
	}

	/**
	 * @param array $address
	 * @param InternationalStreetApiClient $client
	 * @return array
	 * @throws \SmartyStreets\PhpSdk\Exceptions\SmartyException
	 */
	function getInternationalAddressSuggestion( array $address, InternationalStreetApiClient $client ): array {
		$lookup = new Lookup();

		$lookup->setInputId( '0' );
		$lookup->setAddress1( $address['address_1'] );
		$lookup->setAddress2( $address['address_2'] );
		$lookup->setLocality( $address['city'] );
		$lookup->setAdministrativeArea( $address['state'] );
		$lookup->setCountry( $address['country'] );
		$lookup->setPostalCode( $address['postcode'] );

		$client->sendLookup( $lookup ); // The candidates are also stored in the lookup's 'result' field.

		/** @var \SmartyStreets\PhpSdk\International_Street\Candidate $first_candidate */
		$first_candidate = $lookup->getResult()[0];

		$suggested_address   = $first_candidate->getAddress1();
		$suggested_address_2 = $first_candidate->getAddress2();
		$suggested_country   = substr( $first_candidate->getComponents()->getCountryIso3(), 0, -1 );
		$suggested_zip       = ! empty( $first_candidate->getComponents()->getPostalCodeExtra() ) ? $first_candidate->getComponents()->getPostalCodeShort() . ' - ' . $first_candidate->getComponents()->getPostalCodeExtra() : $first_candidate->getComponents()->getPostalCodeShort();
		$suggested_state     = $first_candidate->getComponents()->getAdministrativeArea();
		$suggested_city      = $first_candidate->getComponents()->getLocality();

		return array(
			'address_1' => $suggested_address,
			'address_2' => $suggested_address_2,
			'company'   => $address['company'],
			'city'      => $suggested_city,
			'country'   => $suggested_country,
			'state'     => $suggested_state,
			'postcode'  => $suggested_zip,
		);
	}
}
