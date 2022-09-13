/**
 * Scan frequency selector.
 */
import React, { Component } from 'react'

export default class ScanFrequencySelector extends Component {

	dayPartSelection

	/**
	 * Constructor.
	 */
	constructor (props) {
		super(props)
		this.onFrequencyChanged = this.onFrequencyChanged.bind(this)
		this.onHourChanged = this.onHourChanged.bind(this)
		this.onDayChanged = this.onDayChanged.bind(this)
		this.onAmPmChanged = this.onAmPmChanged.bind(this)
	}

	parseHourValue (hourAsString) {
		return parseInt(hourAsString, 10)
	}

	formatHourString (hourAsNumber) {
		return hourAsNumber.toString(10).padStart(2, '0')
	}

	onFrequencyChanged (e) {
		let updatedData = { ...this.props.data }
		updatedData.frequency = e.target.value
		this.props.onSave(updatedData)
	}

	onHourChanged (e) {
		let updatedData = { ...this.props.data }
		if (wfcmFileChanges.scanModal.is_time_format_am_pm) {
			updatedData.hour = this.dayPartSelection === 'am' ? e.target.value : this.formatHourString(this.parseHourValue(e.target.value) + 12)
		} else {
			updatedData.hour = e.target.value
		}
		this.props.onSave(updatedData)
	}

	onAmPmChanged (e) {
		this.dayPartSelection = e.target.value
		let updatedData = { ...this.props.data }
		let hourAsNumber = this.parseHourValue(updatedData.hour)
		updatedData.hour = this.formatHourString(this.dayPartSelection === 'am' ? hourAsNumber - 12 : hourAsNumber + 12)
		this.props.onSave(updatedData)
	}

	onDayChanged (e) {
		let updatedData = { ...this.props.data }
		updatedData.day = e.target.value
		this.props.onSave(updatedData)
	}

	render () {
		return (
			<React.Fragment>
				<table class="form-table wfcm-table">
					<tr>
						<th>
							<label for="wfcm-settings-frequency">{wfcmFileChanges.scanModal.frequency}</label>
						</th>
						<td>
							<fieldset>
								<select name="wfcm-settings[scan-frequency]" value={this.props.data.frequency}
												onChange={this.onFrequencyChanged}>
									{wfcmFileChanges.scanModal.frequencyOptions.map(({ value, label }) =>
										<option value={value}>
											{label}
										</option>
									)}
								</select>
							</fieldset>
						</td>
					</tr>
					{'weekly' === this.props.data.frequency &&
					<tr id="scan-day-row">
						<th>
							<label for="wfcm-settings-scan-day">{wfcmFileChanges.scanModal.scanDayTitle}</label>
						</th>
						<td>
							<fieldset>
								<label>
									<select name="wfcm-settings[scan-day]"
													value={this.props.data.day}
													onChange={this.onDayChanged}>
										{wfcmFileChanges.scanModal.daysOptions.map(({ value, label }) =>
											<option value={value}>
												{label}
											</option>
										)}
									</select>
								</label>
							</fieldset>
						</td>
					</tr>}
					{('daily' === this.props.data.frequency || 'weekly' === this.props.data.frequency) && this.renderHourSelector()}
				</table>
			</React.Fragment>
		)
	}

	renderHourSelector () {

		let selected_hour = this.parseHourValue(this.props.data.hour)
		let hours_to_display = wfcmFileChanges.scanModal.hoursOptions
		const day_part = selected_hour < 12 ? 'am' : 'pm'
		if (!this.dayPartSelection) {
			this.dayPartSelection = day_part
		}
		if (wfcmFileChanges.scanModal.is_time_format_am_pm) {
			hours_to_display = wfcmFileChanges.scanModal.hoursOptions.slice(0, 12)
			if (day_part === 'pm') {
				selected_hour -= 12
			}
		}

		selected_hour = this.formatHourString(selected_hour)

		return (
			<tr id="scan-hour-row">
				<th>
					<label htmlFor="wfcm-settings-scan-hour">{wfcmFileChanges.scanModal.scanHourTitle}</label>
				</th>
				<td>
					<fieldset>
						<label>
							<select name="wfcm-settings[scan-hour]"
											value={selected_hour}
											onChange={this.onHourChanged}>
								{hours_to_display.map(({ value, label }) =>
									<option value={value}>
										{label}
									</option>
								)}
							</select>
							{wfcmFileChanges.scanModal.is_time_format_am_pm &&
							<select name="wfcm-settings[scan-hour-am]"
											value={day_part}
											onChange={this.onAmPmChanged}>
								<option value='am'>AM</option>
								<option value='pm'>PM</option>
								)}
							</select>}
						</label>
					</fieldset>
				</td>
			</tr>
		)
	}
}

