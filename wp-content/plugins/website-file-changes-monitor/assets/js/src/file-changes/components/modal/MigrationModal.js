/**
 * Scan Modal.
 */
import React, { Component } from 'react';
import Modal from 'react-modal';
import FileEvents from '../helper/FileEvents';

export default class MigrationModal extends Component {

	/**
	 * Constructor.
	 */
	constructor() {
		super();

		this.state = {
			modalIsOpen: true,
			scanning: false,
			backgroundUpdate: false,
			step: 'welcome',
		};

		this.openModal          = this.openModal.bind( this );
		this.closeModal         = this.closeModal.bind( this );
		this.startMigrationScan = this.startMigrationScan.bind( this );

	}

	/**
	 * Open modal.
	 */
	openModal() {
		this.setState({ modalIsOpen: true });
	}

	/**
	 * Close modal.
	 */
	closeModal() {
		this.setState({ modalIsOpen: false });
	}

	upgradeMessage() {

		return (
			{ __html:
				wfcmFileChanges.migrationModal.modalLine1 + '<br><br>' + wfcmFileChanges.migrationModal.modalLine2
			}
		);
	}

	completedMessageRender() {

		return (
			{ __html:
				wfcmFileChanges.migrationModal.oldClearedLine1
			}
		);
	}

	mainButtonsRender() {
		return (
			<p>
				{
					<input type="button" className="button-primary" value={ ! this.state.migrating ? wfcmFileChanges.migrationModal.upgradeButton : wfcmFileChanges.migrationModal.migrating } onClick={this.startMigrationScan} disabled={this.state.migrating} />
				}
			</p>
		);
	}

	completedButtonsRender() {
		return (
			<p>
				{
						<input type="button" className="button-primary" value={wfcmFileChanges.scanModal.ok} onClick={this.closeModal} />
				}
			</p>
		);
	}

	/**
	 * Start the scan.
	 */
	async startMigrationScan( element ) {
		this.setState( () => ({
			migrating: true,
			backgroundUpdate: true
		}) );
		const targetElement = element.target;

		// this is a destructive endpoint - would POST be better option?
		const requestUrl  = `${wfcmFileChanges.scanModal.adminAjax}?action=wfcm_sha256_upgrade_flush&security=${wfcmFileChanges.security}`;
		let requestParams = { method: 'GET' };
		let response      = await fetch( requestUrl, requestParams );

		if ( response ) {
			// this is a destructive endpoint - would POST be better option?
			let scan_start_response = await FileEvents.startManualScan();
			if( scan_start_response ) {
				this.setState( () => ({
					scanned: true,
					step: 'completed'
				}) );
			}
		} else {
			targetElement.value = wfcmFileChanges.scanModal.scanFailed;
		}
	}

	/**
	 * Render the modal.
	 */
	render() {
		return (
			<React.Fragment>
				<Modal isOpen={this.state.modalIsOpen} onRequestClose={this.closeModal} style={modalStyles} contentLabel={wfcmFileChanges.migrationModal.upgradeButton}>
					<div className="wfcm-modal-header">
						<span>
							<img src={wfcmFileChanges.scanModal.logoSrc} alt="WFCM" className="logo" />
							<h2>{ wfcmFileChanges.migrationModal.upgradeButton }</h2>
						</span>
					</div>
					<div className="wfcm-modal-body">
						<p dangerouslySetInnerHTML=
						{
							this.state.step === 'welcome' ?
								this.upgradeMessage() :
								this.completedMessageRender()
						}
						/>
						{
							this.state.step === 'welcome' ?
								this.mainButtonsRender() :
								this.completedButtonsRender()
							}
					</div>
				</Modal>
			</React.Fragment>
		);
	}
}

const modalStyles = {
	content: {
		top: '35%',
		left: '50%',
		right: 'auto',
		bottom: 'auto',
		marginRight: '-50%',
		transform: 'translate(-40%, -30%)',
		border: 'none',
		borderRadius: '0',
		padding: '0 16px 16px',
		width: '500px'
	}
};

Modal.defaultStyles.overlay.backgroundColor = 'rgba(0,0,0,0.5)';
Modal.setAppElement( '#wfcm-file-changes-view' );
