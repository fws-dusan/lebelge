/**
 * File Events Helper Functions.
 */

/**
 * Get request object for REST request.
 *
 * @param {string} method REST method: GET, POST, PATCH, DELETE.
 * @param {string} url REST url.
 */
function getRestRequestObject( method, url, body = false ) {

	// Request object params.
	let requestParams = { // eslint-disable-line no-undef
		method: method,
		headers: {
			'X-WP-Nonce': wfcmFileChanges.security // eslint-disable-line no-undef
		}
	};

	// If there is a body then add it to the request object.
	if ( body ) {
		requestParams.body = body;
	}

	// Return the request object.
	return new Request( url, requestParams );
}

/**
 * Get events via REST request.
 *
 * @param {string} eventType Event type: added, modified, deleted.
 * @param {integer} paged Page number.
 * @param {integer} perPage Number of events per page.
 */
async function getEvents( eventType, paged, perPage ) {
	const requestUrl = `${wfcmFileChanges.fileEvents.get}/${eventType}?paged=${paged}&per-page=${perPage}`;
	const request = getRestRequestObject( 'GET', requestUrl ); // Get REST request object.

	// Send the request.
	let response = await fetch( request );
	let events = await response.json();
	return events;
}

/**
 * Mark event as read.
 *
 * @param {integer} id Event id.
 */
async function markEventAsRead( id ) {
	const requestUrl = `${wfcmFileChanges.fileEvents.delete}/${id}`;
	const request = getRestRequestObject( 'DELETE', requestUrl ); // Get REST request object.

	// Send the request.
	let response = await fetch( request );
	response = await response.json();
	return response;
}

/**
 * Mark event as read.
 *
 * @since 1.5
 */
async function startManualMarkAllRead( type ) {

	const sanitizedType = type.replace('-files', '');
	const requestUrl = `${wfcmFileChanges.fileEvents.mark_all_read}/${sanitizedType}`;
	const request    = getRestRequestObject( 'DELETE', requestUrl ); // Get REST request object.

	// Send the request.
	let response = await fetch( request );
	response     = await response.json();
	return response;
}

/**
 * Exclude event from scanning.
 *
 * @param {integer} id Event id.
 * @param {string} excludeType Type of exclusion.
 */
async function excludeEvent( id, excludeType ) {
	const requestUrl = `${wfcmFileChanges.fileEvents.delete}/${id}`;
	const requestBody = JSON.stringify({
		exclude: true,
		excludeType: excludeType
	});
	const request = getRestRequestObject( 'DELETE', requestUrl, requestBody );

	// Send the request.
	let response = await fetch( request );
	response = await response.json();
	return response;
}

/**
 * Allow file or folder to be present in site root or WP core folders.
 *
 * @param {integer} id Event id.
 * @param {string} targetType Type of target - dir or file.
 */
async function allowEventInCore( id, targetType ) {
	const requestUrl = `${wfcmFileChanges.fileEvents.allowInCore}/${id}`;
	const requestBody = JSON.stringify({
		targetType: targetType
	});
	const request = getRestRequestObject( 'PATCH', requestUrl, requestBody );

	// Send the request.
	let response = await fetch( request );
	response = await response.json();
	return response;
}



/**
 * Start the manual scan.
 */
async function startManualScan() {
	const requestUrl = `${wfcmFileChanges.monitor.start}`;
	const request = getRestRequestObject( 'GET', requestUrl );

	// Send the request.
	let response = await fetch( request );
	response = await response.json();
	return response;
}

/**
 * Delete events within specified folder
 *
 * @param {string} dirPath
 * @returns {Promise<void>}
 */
async function deleteEventsInFolder( dirPath ) {
	const requestUrl = `${wfcmFileChanges.fileEvents.delete_in_folder}`;
	const requestBody = JSON.stringify({
		path: dirPath
	});
	const request = getRestRequestObject( 'DELETE', requestUrl, requestBody );

	// Send the request.
	let response = await fetch( request );
	response = await response.json();
	return response;
}

export default {
	getRestRequestObject,
	getEvents,
	markEventAsRead,
	excludeEvent,
	startManualScan,
	startManualMarkAllRead,
	allowEventInCore,
	deleteEventsInFolder
};
