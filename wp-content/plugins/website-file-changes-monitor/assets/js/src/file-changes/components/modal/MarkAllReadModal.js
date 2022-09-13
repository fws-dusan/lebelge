/**
 * Scan Modal.
 */
import React, { Component } from 'react';
import Modal from 'react-modal';

export default class MarkAllReadModal extends Component {

	/**
	 * Constructor.
	 */
	constructor( props ) {
		super( props );

		const fileChanges = document.getElementById( 'wfcm-file-changes-view' );
		let viewString    = 'not set';
		switch ( fileChanges.dataset.view ) {
			case 'added-files':
				viewString = 'added';
				break;
			case 'modified-files':
				viewString = 'modified';
				break;
			case 'deleted-files':
				viewString = 'removed';
				break
			default:
		}

		this.state = {
			modalIsOpen: true,
			running: false,
			marking: false,
			step: 'markAllReadOpen',
			view: viewString,
		};

		this.openModal             = this.openModal.bind( this );
		this.closeModal            = this.closeModal.bind( this );
		this.triggerMarkAllRead    = this.triggerMarkAllRead.bind( this );
		this.triggerMarkAllReadAll = this.triggerMarkAllReadAll.bind( this );

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

	titleRender() {
		let modalTitle = wfcmFileChanges.markAllRead.markReadModalTitle;
		modalTitle     = modalTitle.replace( '{{$type}}', this.state.view );
		return(
			<h2>{ modalTitle }</h2>
		)
	}

	mainMessageRender() {
		let message = wfcmFileChanges.markAllRead.markReadModalMsg;
		message     = message.replace( '{{$type}}', this.state.view );
		return (
			<p>{ `${ message }`}</p>

		);
	}

	mainButtonsRender() {
		let mainBtnText     = wfcmFileChanges.markAllRead.markReadButtonMain;
		mainBtnText         = mainBtnText.replace( '{{$type}}', this.state.view );
		return (
			<p>
				{
					! this.state.markingAllReadSuccess
						? <input type="button" className="button-primary" value={ mainBtnText } onClick={this.triggerMarkAllRead} disabled={ this.state.marking } />
						: <input type="button" className="button-primary" value={ wfcmFileChanges.markAllRead.markingAllReadSuccess} onClick={this.closeModal} disabled={ this.state.markingAllReadSuccess }/>
				}
				&nbsp;
				{
					! this.state.markingAllReadSuccess
						? <input type="button" className="button" value={ ! this.state.markingAllReadSuccess ? wfcmFileChanges.markAllRead.markReadButtonAll : wfcmFileChanges.markAllRead.markingAllReadSuccess} onClick={this.triggerMarkAllReadAll} disabled={this.state.marking } />
						: null
				}

			</p>
		);
	}

	/**
	 * Start marking all items as read.
	 *
	 * @since 1.5
	 * @retun {Promise}
	 */
	async triggerMarkAllRead( selectedType ) {

		this.setState({
			marking: true,
			markAllReadBtnValue: wfcmFileChanges.markAllRead.running
		});
		const currentView = document.getElementById( 'wfcm-file-changes-view' ).dataset.view;

		let targetType = 'all' === selectedType ? selectedType : currentView;
		const response = await this.props.startMarkAllRead( targetType );

		if ( response ) {
			this.setState({
				marking: false,
				markAllReadBtnValue: wfcmFileChanges.markAllRead.markNow,
				markingAllReadSuccess: true,
			});
			if ( 'all' === targetType ) {
				// refresh the page.
				location.reload();
			}
			// close the modal.
			this.closeModal();
		} else {
			this.setState({
				marking: false,
				markingAllReadFailed: true,
				markAllReadBtnValue: wfcmFileChanges.markAllRead.markingAllReadFailed,
			});
		}
	}

	async triggerMarkAllReadAll() {
		await this.triggerMarkAllRead( 'all' );
	}

	/**
	 * Render the modal.
	 */
	render() {
		return (
			<React.Fragment>
				<Modal isOpen={this.state.modalIsOpen} onRequestClose={this.closeModal} style={modalStyles} contentLabel={wfcmFileChanges.scanModal.scanNow}>
					<div className="wfcm-modal-header">
						<span>
							<img src={wfcmFileChanges.scanModal.logoSrc} alt="WFCM" className="logo" />
							{ this.titleRender() }
						</span>
					</div>
					<div className="wfcm-modal-body">
						{
							this.mainMessageRender()
						}
						{
							this.mainButtonsRender()
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
