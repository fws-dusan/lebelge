/**
 * Instant Scan.
 */
import React, { Component } from 'react';
import ScanErrorModal from '../modal/ScanErrorModal';
import ScanModal from '../modal/ScanModal';
import MigrationModal from '../modal/MigrationModal';

export default class InstantScan extends Component {

	constructor( props ) {
		super( props );

		this.state = {
			scanning: wfcmFileChanges.instantScan.scanningSetState,
			scanFailed: false,
			scanBtnValue: wfcmFileChanges.instantScan.scanNow,
			lastScanTimestamp: wfcmFileChanges.instantScan.lastScanTime
		};
	}

	/**
	 * Start manual scan.
	 */
	async startScan() {
		this.setState({
			scanning: true,
			scanBtnValue: wfcmFileChanges.instantScan.scanning
		});

		jQuery( '#last-scan-timestamp' ).text( wfcmFileChanges.instantScan.scanningInProgress );

		const response = await this.props.startInstantScan();

		if ( response ) {
			this.setState({
				scanning: true,
				scanBtnValue: wfcmFileChanges.instantScan.scanning,
				lastScanTimestamp: response
			});
		} else {
			this.setState({
				scanFailed: true,
				scanBtnValue: wfcmFileChanges.instantScan.scanFailed
			});
		}
	}

	/**
	 * Component render.
	 */
	render() {
		return (
			<div className="alignleft actions">
				<input type="submit" className="button button-red" value={this.state.scanBtnValue} onClick={this.startScan.bind( this )} disabled={this.state.scanning} />
				{
					this.state.lastScanTimestamp ?
						<span id="last-scan-timestamp">{wfcmFileChanges.instantScan.lastScan}: {this.state.lastScanTimestamp}</span> :
						false
				}
				{
					this.state.scanFailed ?
						<ScanErrorModal /> :
						null
				}
				{
					! wfcmFileChanges.scanModal.dismiss ?
						<ScanModal /> :
						null
				}
				{
					! wfcmFileChanges.migrationModal.migrated && wfcmFileChanges.scanModal.dismiss ?
						<MigrationModal /> :
						null
				}
			</div>
		);
	}
}
