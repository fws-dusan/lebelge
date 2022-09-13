/**
 * Events Table Rows.
 */
import React, { Component } from 'react'
import ContentModal from '../modal/ContentModal'
import ReactTooltip from 'react-tooltip'
import { confirmAlert } from 'react-confirm-alert'
import 'react-confirm-alert/src/react-confirm-alert.css'
import { CgFileRemove } from '@react-icons/all-files/cg/CgFileRemove'
import { CgFolderRemove } from '@react-icons/all-files/cg/CgFolderRemove'
import { CgCheckR } from '@react-icons/all-files/cg/CgCheckR'
import { CgFolderAdd } from '@react-icons/all-files/cg/CgFolderAdd'

export default class EventsTableRow extends Component {
	accentsMap = {
		a: 'á|à|ã|â|À|Á|Ã|Â|ä|Ä',
		e: 'é|è|ê|É|È|Ê|ë|Ë',
		i: 'í|ì|î|Í|Ì|Î|ï|Ï',
		o: 'ó|ò|ô|õ|Ó|Ò|Ô|Õ|ö|Ö',
		u: 'ú|ù|û|ü|Ú|Ù|Û|Ü',
		c: 'ç|Ç',
		n: 'ñ|Ñ',
		'-': ' |\\.|_'
	}

	slugify = text => Object.keys(this.accentsMap).reduce((acc, cur) => acc.replace(new RegExp(this.accentsMap[cur], 'g'), cur), text)

	showFolderEventDeletionPopup (title, message, path) {
		confirmAlert({
			title: title,
			message: message,
			buttons: [
				{
					label: wfcmFileChanges.table.yes,
					onClick: () => this.props.deleteEventsInFolder(path)
				},
				{
					label: wfcmFileChanges.table.no
				}
			]
		})
	}

	async onExcludeDirectoryClicked (eventId) {
		const responseData = await this.props.excludeEvent(eventId, 'dir')
		if (responseData.path && responseData.message) {
			this.showFolderEventDeletionPopup(responseData.title, responseData.message, responseData.path)
		}
	}

	async onAllowDirectoryClicked (eventId) {
		confirmAlert({
			title: wfcmFileChanges.table.allowDirInCoreConfirmPopup.title,
			message: wfcmFileChanges.table.allowDirInCoreConfirmPopup.message,
			buttons: [
				{
					label: wfcmFileChanges.scanModal.ok,
					onClick: async () => {
						const responseData = await this.props.allowEventInCore(eventId, 'dir')
						if (responseData.path && responseData.message) {
							this.showFolderEventDeletionPopup(responseData.title, responseData.message, responseData.path)
						}
					}
				},
				{
					label: wfcmFileChanges.scanModal.cancel
				}
			]
		})
	}

	render () {
		const event = this.props.event
		const contentType = event.contentType.toLowerCase()
		const originCssClassSuffix = this.slugify(event.origin).toLowerCase()
		const typeLabel = event.eventContext ? event.eventContext.replace('WordPress ', 'WordPress<br />') : event.contentType

		return (

			<tr>
				<td><input type="checkbox" value={event.id} checked={event.checked}
									 onChange={this.props.selectEvent.bind(this, event.id)}/></td>
				<td>{event.path}</td>
				<td>{event.filename}</td>
				<td>
					{'wp.org' === event.origin && 'added' === event.type ?
						<>
							<span className={`content-type ${contentType} origin-${originCssClassSuffix}`}
										dangerouslySetInnerHTML={{
											__html: `${typeLabel} <span class="dashicons dashicons-info" data-tip="${wfcmFileChanges.table.coreFileTooltip}" data-place="bottom" data-class="wide-tooltip""></span>`
										}}>
							</span>
							<ReactTooltip effect="solid" type="dark"/>
						</> :
						<span className={`content-type ${contentType} origin-${originCssClassSuffix}`}
									dangerouslySetInnerHTML={{ __html: typeLabel }}></span>
					}
				</td>
				<td>{event.dateTime}</td>
				<td>
					<button className="wfcm-action-button"
									data-tip={wfcmFileChanges.table.markAsRead}
									onClick={this.props.markEventAsRead.bind(this, event)}>
						<CgCheckR size={24}/>
					</button>
					{'wp.org' === event.origin && 'added' === event.type ?
						<button className="wfcm-action-button wfcm-allow-btn-directory"
										data-tip={wfcmFileChanges.table.allowDirInCore}
										onClick={this.onAllowDirectoryClicked.bind(this, event.id)}>
							<CgFolderAdd size={24}/>
						</button> : null
					}
					{'file' === contentType ?
						<button className="wfcm-action-button wfcm-exclude-btn-file"
										data-tip={wfcmFileChanges.table.excludeFile}
										onClick={this.props.excludeEvent.bind(this, event.id, 'file')}>
							<CgFileRemove size={24}/>
						</button> : null
					}
					<button className="wfcm-action-button wfcm-exclude-btn-directory"
									data-tip={wfcmFileChanges.table.excludeDir}
									onClick={this.onExcludeDirectoryClicked.bind(this, event.id)}>
						<CgFolderRemove size={24}/>
					</button>
					{
						'directory' === contentType ?
							<ContentModal eventFiles={event.content}/> :
							null
					}
					<ReactTooltip effect="solid" type="dark"/>
				</td>
			</tr>
		)
	}
}
