/**
 * Modified Files Table
 */
import React, { Component } from 'react';
import EventsTable from '../events-table';
import Navigation from '../navigation';
import { EventsProvider } from '../context/EventsContext';

export default class ModifiedFilesTable extends Component {
	render() {
		return (
			<section>
				<EventsProvider eventsType="modified">
					<Navigation position="top" />
					<EventsTable />
					<Navigation position="bottom" eventsType="modified" />
				</EventsProvider>
			</section>
		);
	}
}
