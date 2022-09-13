/**
 * Scan Modal.
 */
import React, { Component } from 'react'
import Modal from 'react-modal'
import fileEvents from '../helper/FileEvents'
import ScanFrequencySelector from './ScanFrequencySelector'
import { CgCheckR } from '@react-icons/all-files/cg/CgCheckR'
import { CgFolderAdd } from '@react-icons/all-files/cg/CgFolderAdd'
import { CgFolderRemove } from '@react-icons/all-files/cg/CgFolderRemove'
import { CgFileRemove } from '@react-icons/all-files/cg/CgFileRemove'
import { CgList } from '@react-icons/all-files/cg/CgList'

export default class ScanModal extends Component {

	/**
	 * Constructor.
	 */
	constructor (props) {
		super(props)

		this.state = {
			modalIsOpen: true,
			scanning: false,
			backgroundScanInitiated: false,
			scanComplete: false,
			emailing: false,
			testMailSent: false,
			step: wfcmFileChanges.scanModal.upgradeInfo ? 'upgrade' : 'welcome',
			savingFrequencySettings: false,
			frequencySettingsSavingFailed: false,
			frequencySettings: { ...wfcmFileChanges.scanModal.frequencySettings }
		}

		this.stepIncrement = this.stepIncrement.bind(this)
		this.openModal = this.openModal.bind(this)
		this.closeModal = this.closeModal.bind(this)
		this.startScan = this.startScan.bind(this)
		this.sendTestEmail = this.sendTestEmail.bind(this)
		this.titleRender = this.titleRender.bind(this)
		this.mainMessageRender = this.mainMessageRender.bind(this)
		this.mainButtonsRender = this.mainButtonsRender.bind(this)
		this.emailMessageRender = this.emailMessageRender.bind(this)
		this.emailButtonsRender = this.emailButtonsRender.bind(this)
		this.emailSentMessage = this.emailSentMessage.bind(this)
		this.onFrequencySettingsChange = this.onFrequencySettingsChange.bind(this)
		this.saveScanFrequencySettings = this.saveScanFrequencySettings.bind(this)

	}

	onFrequencySettingsChange (selectedSettings) {
		this.setState({
			frequencySettings: selectedSettings
		})
	}

	async saveScanFrequencySettings () {
		this.setState({
			savingFrequencySettings: true
		})

		const requestUrl = `${wfcmFileChanges.scanModal.adminAjax}?action=wfcm_set_scan_frequency&security=${wfcmFileChanges.security}`
		const requestParams = {
			method: 'POST',
			body: JSON.stringify(this.state.frequencySettings)
		}

		let response = await fetch(requestUrl, requestParams)
		response = await response.json()

		if (response.success) {
			this.setState({
				savingFrequencySettings: false
			})
			this.stepIncrement()
		} else {
			this.setState({
				savingFrequencySettings: false,
				frequencySettingsSavingFailed: true
			})

		}
	}

	stepIncrement () {
		const currentStep = this.state.step
		let targetStep = 'email'
		switch (currentStep) {
			case 'upgrade':
				targetStep = 'welcome'
				break
			case 'welcome':
				targetStep = 'frequency-settings'
				break
			case 'frequency-settings':
				targetStep = 'email'
				break
			case 'email':
				targetStep = 'actions'
				break
		}

		this.setState({ step: targetStep })
	}

	/**
	 * Open modal.
	 */
	openModal () {
		this.setState({ modalIsOpen: true })
	}

	/**
	 * Close modal.
	 */
	closeModal () {
		this.setState({ modalIsOpen: false })

		const requestUrl = `${wfcmFileChanges.scanModal.adminAjax}?action=wfcm_dismiss_instant_scan_modal&security=${wfcmFileChanges.security}`
		let requestParams = { method: 'GET' }
		fetch(requestUrl, requestParams)
	}

	titleRender () {
		switch (this.state.step) {
			case 'upgrade':
				return (
					<h2>{wfcmFileChanges.scanModal.upgradeInfo.title || wfcmFileChanges.pageHead}</h2>
				)
			case 'frequency-settings':
				return (
					<h2>{this.state.frequencySettingsSavingFailed ? wfcmFileChanges.scanModal.frequencySettingsErrorTitle : wfcmFileChanges.scanModal.frequencySettingsTitle}</h2>
				)
			case 'email':
				return (
					<h2>{!this.state.emailSent ? wfcmFileChanges.scanModal.sendTestMail : wfcmFileChanges.scanModal.emailSentTitle}</h2>
				)
			case 'actions':
				return (
					<h2>{wfcmFileChanges.scanModal.actionsTitle}</h2>
				)
			case 'final':
			default:
				return (
					<h2>{!this.state.scanComplete ? wfcmFileChanges.scanModal.scanNow : wfcmFileChanges.scanModal.headingComplete}</h2>
				)
		}
	}

	mainMessageRender () {
		return (
			{
				__html:
					!this.state.backgroundScanInitiated ?
						wfcmFileChanges.scanModal.initialMsg :
						wfcmFileChanges.scanModal.bgScanMsg
			}
		)
	}

	emailMessageRender () {
		return !this.state.testMailSent
			? { __html: wfcmFileChanges.scanModal.emailMsg }
			: this.emailSentMessage()
	}

	frequencySettingsMessageRender () {
		return {
			__html: this.state.frequencySettingsSavingFailed ? wfcmFileChanges.scanModal.frequencySettingsErrorMessage : wfcmFileChanges.scanModal.frequencySettingsMessage
		}
	}

	emailSentMessage () {
		return (
			{
				__html:
					wfcmFileChanges.scanModal.emailSentLine1 + '<br><br>' + wfcmFileChanges.scanModal.emailSentLine2
			}
		)
	}

	mainButtonsRender () {
		return (
			<>
				<p>
					{
						!this.state.backgroundScanInitiated ?
							<input type="button" className="button-primary"
										 value={!this.state.scanning ? wfcmFileChanges.scanModal.scanNowButton : wfcmFileChanges.scanModal.scanning}
										 onClick={this.startScan} disabled={this.state.scanning}/> :
							<input type="button" className="button-primary" value={wfcmFileChanges.scanModal.ok}
										 onClick={this.stepIncrement}/>
					}
					&nbsp;
					{
						!this.state.scanComplete && !this.state.backgroundScanInitiated ?
							<input type="button" className="button" value={wfcmFileChanges.scanModal.scanDismiss}
										 onClick={this.stepIncrement} disabled={this.state.scanning}/> :
							null
					}
				</p>
				<p className="description" dangerouslySetInnerHTML=
					{
						!this.state.backgroundScanInitiated ?
							{
								__html:
								wfcmFileChanges.scanModal.scheduleHelpTxt
							} :
							null
					}
				/>
			</>
		)
	}

	emailButtonsRender () {
		return (
			<p>
				{
					!this.state.testMailSent ?
						<input type="button" className="button button-primary"
									 value={!this.state.emailing ? wfcmFileChanges.scanModal.sendTestMail : wfcmFileChanges.scanModal.emailSending}
									 onClick={this.sendTestEmail} disabled={this.state.testMailSent}/> :
						(() => {
							return (
								<input type="button" className="button button-primary" value={wfcmFileChanges.scanModal.emailSent}
											 disabled={this.state.testMailSent}/>
							)
						})()
				}
				&nbsp;
				<input
					type="button"
					className="button button-primary"
					onClick={this.stepIncrement}
					value={wfcmFileChanges.scanModal.next}/>
			</p>
		)
	}

	frequencySettingsButtonsRender () {
		return (
			<p>
				{this.state.frequencySettingsSavingFailed ?
					<input type="button" className="button button-primary"
								 onClick={this.stepIncrement}
								 value={wfcmFileChanges.scanModal.ok}/>
					: !this.state.savingFrequencySettings ?
						<input type="button" className="button button-primary" value={wfcmFileChanges.scanModal.save}
									 onClick={this.saveScanFrequencySettings}/> :
						<input type="button" className="button button-primary" value={wfcmFileChanges.scanModal.saving} disabled/>
				}
				{!this.state.frequencySettingsSavingFailed &&
				<input type="button" className="button button-primary" onClick={this.stepIncrement}
							 value={wfcmFileChanges.scanModal.skip}/>}
			</p>
		)
	}

	/**
	 * Start the scan.
	 */
	async startScan (element) {
		this.setState(() => ({
			scanning: true,
			backgroundScanInitiated: true
		}))
		const targetElement = element.target

		const scanRequest = fileEvents.getRestRequestObject('GET', wfcmFileChanges.monitor.start)
		let response = await fetch(scanRequest)
		response = await response.json()

		if (response) {
			this.setState(() => ({
				scanning: false,
				scanComplete: true
			}))
		} else {
			targetElement.value = wfcmFileChanges.scanModal.scanFailed
		}
	}

	/**
	 * Trigger a test email to send.
	 *
	 * @method sendTestEmail
	 */
	async sendTestEmail (element) {
		this.setState({ emailing: true })
		const targetElement = element.target

		const requestUrl = `${wfcmFileChanges.scanModal.adminAjax}?action=wfcm_send_test_email&security=${wfcmFileChanges.security}`
		const requestParams = { method: 'GET' }

		let response = await fetch(requestUrl, requestParams)
		response = await response.json()

		if (response.success) {
			this.setState({
				emailing: false,
				testMailSent: true
			})
		} else {
			targetElement.value = wfcmFileChanges.scanModal.sendingFailed
		}
	}

	/**
	 * Render the modal.
	 */
	render () {
		return (
			<React.Fragment>
				<Modal isOpen={this.state.modalIsOpen} onRequestClose={this.closeModal} style={modalStyles}
							 contentLabel={wfcmFileChanges.scanModal.scanNow}>
					<div className="wfcm-modal-header">
						<span>
							<img src={wfcmFileChanges.scanModal.logoSrc} alt="WFCM" className="logo"/>
							{this.titleRender()}
						</span>
					</div>
					<div className="wfcm-modal-body">
						{this.state.step === 'actions' ? this.actionsInfoRender() :
							<p dangerouslySetInnerHTML=
									 {
										 this.state.step === 'upgrade' ? { __html: wfcmFileChanges.scanModal.upgradeInfo.text } :
											 this.state.step === 'email' ? this.emailMessageRender() :
												 this.state.step === 'frequency-settings' ? this.frequencySettingsMessageRender() :
													 this.mainMessageRender()
									 }
							/>}
						{this.state.step === 'frequency-settings' && !this.state.frequencySettingsSavingFailed &&
						<ScanFrequencySelector
							data={this.state.frequencySettings}
							onSave={this.onFrequencySettingsChange}/>}
						{(() => {
							switch (this.state.step) {
								case 'upgrade':

									return (
										<p>
											<input
												type="button"
												className="button button-primary"
												onClick={this.stepIncrement}
												value={wfcmFileChanges.scanModal.ok}/>
										</p>
									)
								case 'email':
									return (
										this.emailButtonsRender()
									)
								case 'actions':
									return (
										<p>
											<input type="button" className="button"
														 value={wfcmFileChanges.scanModal.exitButton}
														 onClick={this.closeModal}/>
										</p>
									)
								case 'frequency-settings':
									return (
										this.frequencySettingsButtonsRender()
									)
								default:
									return this.mainButtonsRender()
							}
						})()}
					</div>
				</Modal>
			</React.Fragment>
		)
	}

	actionsInfoRender () {
		return (
			<>
				<p>{wfcmFileChanges.scanModal.actionsMessage}</p>
				<div className="action-info">
					<CgCheckR size={16}/>
					<p dangerouslySetInnerHTML={{
						__html: `- ${wfcmFileChanges.scanModal.actionsInfo.markAsRead}`
					}}></p>
				</div>
				<div className="action-info">
					<CgFolderAdd size={16}/>
					<p dangerouslySetInnerHTML={{
						__html: `- ${wfcmFileChanges.scanModal.actionsInfo.allowDirInCore} ${wfcmFileChanges.scanModal.actionsInfo.readMoreLinkAllowed}`
					}}></p>
				</div>
				<div className="action-info">
					<CgFolderRemove size={16}/>
					<p dangerouslySetInnerHTML={{
						__html: `- ${wfcmFileChanges.scanModal.actionsInfo.excludeDir} ${wfcmFileChanges.scanModal.actionsInfo.readMoreLinkExcluded}`
					}}></p>
				</div>
				<div className="action-info">
					<CgFileRemove size={16}/>
					<p dangerouslySetInnerHTML={{
						__html: `- ${wfcmFileChanges.scanModal.actionsInfo.excludeFile} ${wfcmFileChanges.scanModal.actionsInfo.readMoreLinkExcluded}`
					}}></p>
				</div>
				<div className="action-info">
					<CgList size={16}/>
					<p dangerouslySetInnerHTML={{
						__html: `- ${wfcmFileChanges.scanModal.actionsInfo.fileList}`
					}}></p>
				</div>
			</>
		)
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
}

Modal.defaultStyles.overlay.backgroundColor = 'rgba(0,0,0,0.5)'
Modal.setAppElement('#wfcm-file-changes-view')
