/**
 * Deleted Files Table
 */
import React, { Component } from 'react';
import EventsTable from '../events-table';
import Navigation from '../navigation';
import { EventsProvider } from '../context/EventsContext';

export default class DeletedFilesTable extends Component {
	render() {
		return (
			<section>
				<EventsProvider eventsType="deleted">
					<Navigation position="top" />
					<EventsTable />
					<Navigation position="bottom" eventsType="deleted" />
				</EventsProvider>
			</section>
		);
	}
}
