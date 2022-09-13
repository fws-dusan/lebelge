/**
 * Created Events Provider
 */
import React, { Component } from 'react';
import FileEvents from '../helper/FileEvents';

// Create events context.
export const EventsContext = React.createContext();

// Events Provider Component.
export class EventsProvider extends Component {

	/**
	 * Constructor.
	 *
	 * @param {array} props Component props.
	 */
	constructor( props ) {
		super( props );

		this.state = {
			events: [],
			selectAll: false,
			totalItems: 0,
			maxPages: 1,
			paged: 1
		};
	}

	/**
	 * Select all events.
	 */
	selectAllEvents() {
		this.setState({ selectAll: ! this.state.selectAll }); // Change the state of select all.

		// Change states of every event.
		this.setState({
			events: this.state.events.map( event => {
				event.checked = ! this.state.selectAll;
				return event;
			})
		});
	}

	/**
	 * Select an event.
	 *
	 * @param {string|int} id Event id.
	 */
	selectEvent( id ) {
		let allSelected = true;

		this.setState({
			events: this.state.events.map( event => {
				event.checked = event.id === id ? ! event.checked : event.checked;
				allSelected = event.checked && allSelected ? allSelected : false;
				return event;
			})
		});

		if ( allSelected ) {
			this.setState({ selectAll: true });
		} else {
			this.setState({ selectAll: false });
		}
	}

	/**
	 * Query events from WP.
	 */
	async getFileEvents( paged = false, perPage = false ) {
		if ( false === paged ) {
			paged = this.state.paged;
		}

		const response = await FileEvents.getEvents( this.props.eventsType, paged, perPage );

		if ( 0 === response.events.length && 0 !== ( paged - 1 ) ) {
			this.getFileEvents( paged - 1 );
			return;
		}

		this.setState({
			events: response.events,
			totalItems: response.total,
			maxPages: response.max_num_pages,
			paged: paged
		});
	}

	/**
	 * Mark event as read.
	 *
	 * @param {object} event Event object.
	 */
	async markEventAsRead( event ) {
		let response = null;
		if ( 'wp.org' === event.origin && 'added' === event.type ) {
			response = await FileEvents.allowEventInCore( event.id, 'file' );
		} else {
			response = await FileEvents.markEventAsRead( event.id );
		}

		if ( response.success ) {
			this.getFileEvents();
		}
	}

	/**
	 * Add event to exclude list.
	 *
	 * @param {int} eventId Event id.
	 * @param {string} excludeType Type of exclusion.
	 */
	async excludeEvent( eventId, excludeType ) {
		const response = await FileEvents.excludeEvent( eventId, excludeType );

		if ( response.success ) {
			this.getFileEvents();
		}

		return response;

	}

	/**
	 * Add event to the list files allowed in site root and WP core.
	 *
	 * @param {int} eventId Event id.
	 * @param {string} targetType Type of target - dir or file.
	 */
	async allowEventInCore( eventId, targetType ) {
		const response = await FileEvents.allowEventInCore( eventId, targetType );

		if ( response.success ) {
			await this.getFileEvents();
		}

		return response;
	}

	/**
	 * Handles the bulk actions on events.
	 *
	 * @param {string} action Name of bulk action.
	 */
	async handleBulkAction( action ) {
		let events = [ ...this.state.events ];

		for ( const [ index, event ] of events.entries() ) {
			if ( event.checked ) {
				let response;

				if ( 'mark-as-read' === action ) {
					response = await FileEvents.markEventAsRead( event.id );
				} else if ( 'exclude' === action ) {
					response = await FileEvents.excludeEvent( event.id );
				}

				if ( response && response.success ) {
					this.getFileEvents();
				}
			}
		}

		this.setState({ selectAll: false });
	}

	/**
	 * Events pagination handler.
	 *
	 * @param {int} pageNum Page number.
	 */
	goToPage( pageNum ) {
		this.getFileEvents( pageNum );
	}

	/**
	 * Handles the number of items to display per page.
	 *
	 * @param {integer} items Number of items to display per page.
	 */
	handleShowItems( items ) {
		this.getFileEvents( 1, items );
	}

	/**
	 * Start instant scan.
	 */
	async startInstantScan() {
		let response = await FileEvents.startManualScan();

		if ( response ) {
			this.getFileEvents();
			return response;
		}

		return false;
	}

	/**
	 * Start marking all as read.
	 */
	async startMarkAllRead( type ) {
		// get the events and count them.
		let response = await FileEvents.startManualMarkAllRead( type );

		if ( response ) {
			this.getFileEvents();
			return response;
		}

	}

	/**
	 * Add event to exclude list.
	 *
	 * @param {string} dirPath Path to directory.
	 */
	async deleteEventsInFolder( dirPath) {
		const response = await FileEvents.deleteEventsInFolder( dirPath );
		return response;

	}
	/**
	 * Component render.
	 */
	render() {
		return (
			<EventsContext.Provider
				value={{
					...this.state,
					getFileEvents: this.getFileEvents.bind( this ),
					selectEvent: this.selectEvent.bind( this ),
					selectAllEvents: this.selectAllEvents.bind( this ),
					markEventAsRead: this.markEventAsRead.bind( this ),
					excludeEvent: this.excludeEvent.bind( this ),
					handleBulkAction: this.handleBulkAction.bind( this ),
					goToPage: this.goToPage.bind( this ),
					handleShowItems: this.handleShowItems.bind( this ),
					startInstantScan: this.startInstantScan.bind( this ),
					startMarkAllRead: this.startMarkAllRead.bind( this ),
					allowEventInCore: this.allowEventInCore.bind( this ),
					deleteEventsInFolder: this.deleteEventsInFolder.bind( this )
				}}
			>
				{this.props.children}
			</EventsContext.Provider>
		);
	}
}
