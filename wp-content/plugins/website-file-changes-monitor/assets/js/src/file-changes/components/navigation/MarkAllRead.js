/**
 * Instant Scan.
 */
import React, { Component } from 'react';
import MarkAllReadModal from '../modal/MarkAllReadModal';

export default class MarkAllRead extends Component {

	constructor( props ) {
		super( props );

		this.state = {
			visibleModal: false,
			running: false,
			MarkReadFailed: false,
			markAllReadBtnValue: wfcmFileChanges.markAllRead.markNow,
		};

		this.triggerMarking = this.triggerMarking.bind( this );
	}

	triggerMarking() {
		if ( this.state.visibleModal ) {
			this.setState( { visibleModal: false } );
		} else {
			this.setState( { visibleModal: true } );
		}
	}

	/**
	 * Component render.
	 */
	render() {
		return (
			<div className="alignleft actions">
				<input id="mark-all-read-button" type="submit" className="button-primary" value={this.state.markAllReadBtnValue} onClick={this.triggerMarking.bind( this )} disabled={this.state.running} />
				{
				this.state.visibleModal ?
					<MarkAllReadModal {...this.props} /> :
					null
				}
			</div>

		);
	}
}
