/**
 * Scan Modal.
 */
import React, { Component } from 'react';
import Modal from 'react-modal';

export default class ScanErrorModal extends Component {

	/**
	 * Constructor.
	 */
	constructor() {
		super();

		this.state = {
			modalIsOpen: true
		};

		this.openModal = this.openModal.bind( this );
		this.closeModal = this.closeModal.bind( this );
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

	/**
	 * Render the modal.
	 */
	render() {
		return (
			<React.Fragment>
				<Modal isOpen={this.state.modalIsOpen} onRequestClose={this.closeModal} style={modalStyles} contentLabel={wfcmFileChanges.scanErrorModal.heading}>
					<div className="wfcm-modal-header">
						<span>
							<img src={wfcmFileChanges.scanModal.logoSrc} alt="WFCM" className="logo" />
							<h2>{wfcmFileChanges.scanErrorModal.heading}</h2>
						</span>
					</div>
					<div className="wfcm-modal-body">
						<p className="notice notice-error" dangerouslySetInnerHTML={{ __html: wfcmFileChanges.scanErrorModal.body }} style={errorStyles} />
						<p><input type="button" className="button" value={wfcmFileChanges.scanErrorModal.dismiss} onClick={this.closeModal} /></p>
					</div>
				</Modal>
			</React.Fragment>
		);
	}
}

const errorStyles = {
	marginRight: 0,
	marginLeft: 0,
	padding: '12px'
};

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
