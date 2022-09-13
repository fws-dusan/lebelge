/**
 * Events Table Bulk Actions.
 */
import React from 'react';
import { EventsContext } from '../context/EventsContext';
import BulkActions from './BulkActions';
import Pagination from './Pagination';
import ShowItems from './ShowItems';
import InstantScan from './InstantScan';
import MarkAllRead from './MarkAllRead';

const Navigation = ( props ) => {
	const position = props.position;

	return (
		<React.Fragment>
		{
			'top' === position ?
			<EventsContext.Consumer>
				{ ({totalItems, maxPages, paged, goToPage, handleBulkAction, startInstantScan, startMarkAllRead}) => (
					<div className="tablenav top">
						<BulkActions handleBulkAction={handleBulkAction} />
						<MarkAllRead startMarkAllRead={startMarkAllRead} />
						<InstantScan startInstantScan={startInstantScan} />
						<Pagination totalItems={totalItems} maxPages={maxPages} paged={paged} goToPage={goToPage} />
					</div>
				) }
			</EventsContext.Consumer> :
			<EventsContext.Consumer>
				{ ({handleShowItems}) => (
					<div className="tablenav botton">
						<ShowItems handleShowItems={handleShowItems} eventsType={props.eventsType} />
					</div>
				) }
			</EventsContext.Consumer>
		}
		</React.Fragment>
	);
};

export default Navigation;
