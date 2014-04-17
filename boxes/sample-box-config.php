<?php
// name: The name of the custom field, it will be shown to user.
// desc: A short description explaining what it is to the user.
// id: the id of the field, prefixed by the $prefix. It will be used to store the custom field value
// type: the input type: select, text, textarea, radio or checkbox
// options: used to declare an array of options for a some type of input (select, radio)
// std: the default value of the custom field.

function get_sample_box_config() {

	$prefix = 'apt_';

	$metabox = array(
		'id'         => 'announcement-details-meta-box',
		'title'      => 'Announcement Details',
		'post_type'  => 'post',
		'context'    => 'side',
		'priority'   => 'default',
		'fields'     => array(
			array(
				'name' => 'Announcement Start Time',
				'desc' => 'Start displaying on: ',
				'id'   => $prefix . 'announcement_start_time',
				'type' => 'date_from',
				'std'  => date("D, M d, Y - g:ia", time()),
				'options' => array('date_to_id' => $prefix . 'announcement_end_time')
			),
			array(
				'name' => 'Announcement End Time',
				'desc' => 'Stop displaying on: ',
				'id'   => $prefix . 'announcement_end_time',
				'type' => 'date_to',
				'std'  => date("D, M d, Y - g:ia", time() + (3*24*60*60)), // Three days from now
				'options' => array('date_from_id' => $prefix . 'announcement_start_time')
			),
			array(
				'name' => 'Announcement Type',
				'desc' => 'Announcement type: ',
				'id'   => $prefix . 'announcement_type',
				'type' => 'select',
				'options' => array('Event', 'News', 'Alert'),
				'std' => 'News'
			),
			array(
				'name' => 'Announcement Importance',
				'desc' => 'Extremely important: ',
				'id'   => $prefix . 'announcement_importance',
				'type' => 'checkbox'
			)
		)
	);

	return $metabox;
}
