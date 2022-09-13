/**
 * Events Table Head
 */
import React, { Component } from 'react'
import ReactTooltip from 'react-tooltip'
import { CgList } from '@react-icons/all-files/cg/CgList'
import { CgCheckR } from '@react-icons/all-files/cg/CgCheckR'
import { CgFolderAdd } from '@react-icons/all-files/cg/CgFolderAdd'
import { CgFolderRemove } from '@react-icons/all-files/cg/CgFolderRemove'
import { CgFileRemove } from '@react-icons/all-files/cg/CgFileRemove'

export default class EventsTableHead extends Component {

	render () {
		return (
			<thead>
			<td className="check-column"><input type="checkbox" name="select-all" checked={this.props.selectAll}
																					onChange={this.props.selectAllEvents}/></td>
			<th>{wfcmFileChanges.table.path}</th>
			<th className="column-event-name">{wfcmFileChanges.table.name}</th>
			<th className="column-content-type">{wfcmFileChanges.table.type}</th>
			<th className="column-event-datetime">{wfcmFileChanges.table.dateTime}</th>
			<th className="column-event-actions">
				{wfcmFileChanges.table.actions}
				<span class="dashicons dashicons-info"
							data-tip data-for="wfcm-actions-info"
							data-place="left"
							data-class="actions-info-tooltip"></span>
				<ReactTooltip effect="solid" type="dark" id="wfcm-actions-info">
					<CgCheckR size={20}/><h4>{wfcmFileChanges.table.markAsRead}</h4>
					<p>{wfcmFileChanges.table.markAsReadTooltip}</p>
					<CgFolderAdd size={20}/><h4>{wfcmFileChanges.table.allowDirInCore}</h4>
					<p>{wfcmFileChanges.table.allowDirInCoreTooltip} {wfcmFileChanges.table.canBeChangedInPluginSettings}</p>
					<CgFolderRemove size={20}/><h4>{wfcmFileChanges.table.excludeDir}</h4>
					<p>{wfcmFileChanges.table.excludeDirTooltip} {wfcmFileChanges.table.canBeChangedInPluginSettings}</p>
					<CgFileRemove size={20}/><h4>{wfcmFileChanges.table.excludeFile}</h4>
					<p>{wfcmFileChanges.table.excludeFileTooltip} {wfcmFileChanges.table.canBeChangedInPluginSettings}</p>
					<CgList size={20}/><h4>{wfcmFileChanges.table.showListOfFiles}</h4>
					<p>{wfcmFileChanges.table.fileListTooltip}</p>
				</ReactTooltip>
			</th>
			</thead>
		)
	}
}
