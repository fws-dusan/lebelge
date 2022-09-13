/**
 * Settings JS.
 */
window.addEventListener( 'load', function() {

	const $ = document.querySelector.bind( document );
	const keepLog = document.querySelectorAll( 'input[name="wfcm-settings[keep-log]"]' );
	const frequencySelect = $( 'select[name="wfcm-settings[scan-frequency]"]' );
	const scanWrapper = document.getElementById( 'scan-time-row' );
	const scanDay = $( 'select[name="wfcm-settings[scan-day]"]' ).parentNode;
	const scanDate = $( 'select[name="wfcm-settings[scan-date]"]' ).parentNode;
	const addToListButtons = document.querySelectorAll( '.wfcm-files-container .add' );
	const removeFromListButtons = document.querySelectorAll( '.wfcm-files-container .remove' );
	const manualScanStart = $( '#wfcm-scan-start' );
	const manualScanStop = $( '#wfcm-scan-stop' );
	const manualScanResponse = $( '#wfcm-scan-response' );
	const sendTestEmailButton = $( '#wfcm-send-test-email');
	const testEmailResponse = $( '#wfcm-test-email-response');

	// Frequency handler.
	frequencySelect.addEventListener( 'change', function() {
		showScanFields( this.value );
	});

	// Manage appearance on load.
	showScanFields( frequencySelect.value );

	/**
	 * Show Scan Time fields according to selected frequency.
	 *
	 * @param {string} frequency - Scan frequency.
	 */
	function showScanFields( frequency ) {
		scanDay.classList.add( 'hidden' );
		scanDate.classList.add( 'hidden' );

		if ( 'weekly' === frequency ) {
			scanDay.classList.remove( 'hidden' );
		}

		if ( 'hourly' === frequency ) {
			scanWrapper.classList.add( 'faded-row' );
			jQuery( '#scan-time-row' ).find( 'select' ).each( function() {
				jQuery( this ).prop( 'disabled',  true );
			});
		} else {
			scanWrapper.classList.remove( 'faded-row' );
			jQuery( '#scan-time-row' ).find( 'select' ).each( function() {
				jQuery( this ).prop( 'disabled',  false );
			});
		}
	}

	// Add Exclude Item.
	[ ...addToListButtons ].forEach( excludeAddButton => {
		excludeAddButton.addEventListener( 'click', addToList );
	});

	// Remove Exclude Item(s).
	[ ...removeFromListButtons ].forEach( excludeRemoveButton => {
		excludeRemoveButton.addEventListener( 'click', removeFromList );
	});

	/**
	 * Add item to a list.
	 *
	 * @param {Event} e Event object.
	 */
	function addToList( e ) {
		let pattern = '';
		const objectType = e.target.dataset.objectType;
		const listType = e.target.dataset.listType;

		if ( 'dirs' === objectType ) {
			pattern = /^\s*[a-z-._\d,\s/]+\s*$/i;
		} else if ( 'files' === objectType ) {
			pattern = /^\s*[a-z-._\d,\s]+\s*$/i;
		} else if ( 'exts' === objectType ) {
			pattern = /^\s*[a-z-._\d,\s]+\s*$/i;
		}

		const newItemNameInput = e.target.parentNode.querySelector( '.name' );
		const newItemName = newItemNameInput.value;

		if ( null === newItemName.match( pattern ) ) {
			if ('dirs' === objectType) {
				alert(wfcmSettingsData.dirInvalid); // eslint-disable-line no-undef
			} else if ('files' === objectType) {
				alert(wfcmSettingsData.fileInvalid); // eslint-disable-line no-undef
			} else if ('exts' === objectType) {
				alert(wfcmSettingsData.extensionInvalid); // eslint-disable-line no-undef
			}
		} else {

			const popupMessage = e.target.dataset.triggerPopup;
			if (!popupMessage) {
				_addItemToList(newItemNameInput, newItemName, listType, objectType);
			} else {
				jQuery.confirm({
					title: e.target.dataset.triggerPopupTitle,
					content: popupMessage,
					theme: 'react-confirm-alert',
					animateFromElement: true,
					draggable: false,
					buttons: {
						ok: {
							text: "OK",
							btnClass: 'btn-primary',
							keys: ['enter'],
							action: function () {
								_addItemToList(newItemNameInput, newItemName, listType, objectType);
							}
						}
					}
				});
			}
		}
	}

	function _addItemToList(newItemNameInput, newItemName, listType, objectType ) {
		const targetList = $( `#wfcm-${listType}-${objectType}-list` );
		const newItem = document.createElement( 'span' );
		const newItemId = `${listType}-${objectType}-${newItemName}`;
		const newItemInput = document.createElement( 'input' );
		const newItemLabel = document.createElement( 'label' );

		newItemInput.type = 'checkbox';
		newItemInput.checked = true;
		newItemInput.name = `wfcm-settings[scan-${listType}-${objectType}][]`;
		newItemInput.id = newItemId;
		newItemInput.value = newItemName;

		newItemLabel.setAttribute( 'for', newItemId );
		newItemLabel.innerHTML = newItemName;

		newItem.appendChild( newItemInput );
		newItem.appendChild( newItemLabel );
		targetList.appendChild( newItem );
		newItemNameInput.value = '';
	}
	/**
	 * Remove item from a list.
	 *
	 * @param {Event} e Event object.
	 */
	function removeFromList( e ) {
		const objectType = e.target.dataset.objectType;
		const listType = e.target.dataset.listType;
		const targetListId = `wfcm-${listType}-${objectType}-list`;
		const allItems = [ ...e.target.parentNode.querySelectorAll( '#' + targetListId + ' input[type=checkbox]' ) ];

		let valuesToRemove = [];
		for ( let index = 0; index < allItems.length; index++ ) {
			if ( ! allItems[index].checked ) {
				valuesToRemove.push( allItems[index].value );
			}
		}

		if ( valuesToRemove.length ) {
			for ( let index = 0; index < valuesToRemove.length; index++ ) {
				let itemToRemove = $( 'input[value="' + valuesToRemove[index] + '"]' );
				if ( itemToRemove ) {
					itemToRemove.parentNode.remove();
				}
			}
		}
	}

	// Update settings state when keep log options change.
	[ ...keepLog ].forEach( toggle => {
		toggle.addEventListener( 'change', function() {
			toggleSettings( toggle.checked );
		});
	});

	/**
	 * Toggle Plugin Settings State.
	 *
	 * @param {string} settingValue - Keep log setting value.
	 */
	function toggleSettings( settingValue ) {
		const settingFields = [ ...document.querySelectorAll( '.wfcm-table fieldset' ) ];

		settingFields.forEach( setting => {
			if ( ! settingValue ) {
				setting.disabled = true;
			} else {
				setting.disabled = false;
			}
		});
	}

	/**
	 * Send request to start manual scan.
	 */
	manualScanStart.addEventListener( 'click', function( e ) {
		e.target.value = wfcmSettingsData.scanButtons.scanning; // eslint-disable-line no-undef
		e.target.disabled = true;
		manualScanStop.disabled = false;
		manualScanResponse.classList.add( 'hidden' ); // Hide the notice.
		manualScanResponse.classList.remove( 'notice', 'notice-error' ); // Remove notice html classes.

		// Rest request object.
		const request = new Request( wfcmSettingsData.monitor.start, { // eslint-disable-line no-undef
			method: 'GET',
			headers: {
				'X-WP-Nonce': wfcmSettingsData.restRequestNonce // eslint-disable-line no-undef
			}
		});

		// Send the request.
		fetch( request )
			.then( response => response.json() )
			.then( data => {
				if ( data ) {
					e.target.value = wfcmSettingsData.scanButtons.scanNow; // eslint-disable-line no-undef
				} else {
					e.target.value = wfcmSettingsData.scanButtons.scanFailed; // eslint-disable-line no-undef
					manualScanResponse.classList.add( 'notice', 'notice-error' );
					manualScanResponse.classList.remove( 'hidden' );
				}

				e.target.disabled = false;
				manualScanStop.disabled = true;
			})
			.catch( error => {
				e.target.value = wfcmSettingsData.scanButtons.scanFailed; // eslint-disable-line no-undef
				e.target.disabled = false;
				manualScanStop.disabled = true;
				console.log( error ); // eslint-disable-line no-console
				manualScanResponse.classList.add( 'notice', 'notice-error' );
				manualScanResponse.classList.remove( 'hidden' );
			});
	});

	/**
	 * Send request to stop manual scan.
	 */
	manualScanStop.addEventListener( 'click', function( e ) {
		e.target.value = wfcmSettingsData.scanButtons.stopping; // eslint-disable-line no-undef
		e.target.disabled = true;

		// Rest request object.
		const request = new Request( wfcmSettingsData.monitor.stop, { // eslint-disable-line no-undef
			method: 'GET',
			headers: {
				'X-WP-Nonce': wfcmSettingsData.restRequestNonce // eslint-disable-line no-undef
			}
		});

		// Send the request.
		fetch( request )
			.then( response => response.json() )
			.then( data => {
				if ( data ) {
					e.target.value = wfcmSettingsData.scanButtons.scanStop; // eslint-disable-line no-undef
					manualScanStart.disabled = false;
				}
			})
			.catch( error => {
				console.log( error ); // eslint-disable-line no-console
			});
	});

	/**
	 * Send request to send a test email.
	 */
	sendTestEmailButton.addEventListener( 'click', function( e ) {
		e.target.value = wfcmSettingsData.emailSending; // eslint-disable-line no-undef
		e.target.disabled = true;
		testEmailResponse.classList.remove( 'notice', 'notice-error' );
		testEmailResponse.classList.add( 'hidden' );

		// Rest request object.
		const request = new Request( `${wfcmSettingsData.adminAjax}?action=wfcm_send_test_email&security=${wfcmSettingsData.restRequestNonce}`, { // eslint-disable-line no-undef
			method: 'GET',
			headers: {
				'X-WP-Nonce': wfcmSettingsData.restRequestNonce // eslint-disable-line no-undef
			}
		});

		// Send the request.
		fetch( request )
			.then( response => response.json() )
			.then( data => {
				if ( data && data.success === true ) {
					e.target.value = wfcmSettingsData.emailSent; // eslint-disable-line no-undef
				} else {
					e.target.value = wfcmSettingsData.sendEmail; // eslint-disable-line no-undef
					testEmailResponse.classList.add( 'notice', 'notice-error' );
					testEmailResponse.classList.remove( 'hidden' );
				}

				e.target.disabled = false;
			})
			.catch( error => {
				e.target.value = wfcmSettingsData.sendEmail; // eslint-disable-line no-undef
				e.target.disabled = false;
				console.log( error ); // eslint-disable-line no-console
				testEmailResponse.classList.add( 'notice', 'notice-error' );
				testEmailResponse.classList.remove( 'hidden' );
			});
	});

	const noticeEmailField = document.getElementById('notice-email-address');

	noticeEmailField.addEventListener('click', function() {
		document.getElementById( 'email-notice-custom' ).checked = true;
	}, false);
});
