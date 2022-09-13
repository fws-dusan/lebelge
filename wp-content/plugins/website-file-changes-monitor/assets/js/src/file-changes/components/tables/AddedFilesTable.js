/**
 * Created Files Table
 */
import React, { Component } from 'react';
import EventsTable from '../events-table';
import Navigation from '../navigation';
import { EventsProvider } from '../context/EventsContext';

export default class AddedFilesTable extends Component {
	render() {
		return (
			<section>
				<EventsProvider eventsType="added">
					<Navigation position="top" />
					<EventsTable />
					<Navigation position="bottom" eventsType="added" />
				</EventsProvider>
			</section>
		);
	}
}
