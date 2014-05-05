<?php

if (class_exists('MetaBox') && !is_object(MetaBox::$create_field)) {
	MetaBox::$create_field = (object) array();

	// <input type="text">
	MetaBox::$create_field->text = function ($name, $data) {
		// cache id, label, placeholder
		$id = $name.'_id';
		$label = @$data['label'];
		$placeholder = @$data['placeholder'];

		// cache first value or blank
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';

		// create wrapped text field
		$layout = array(
			array('p',
				array('input', 'type' => 'text', 'id' => $id, 'name' => $name, 'value' => $opt_valueue, 'placeholder' => $placeholder)
			)
		);

		// conditionally prepend wrapped label
		if (isset($data['label'])) {
			array_unshift($layout, array('p',
				array('label', 'for' => $id,
					array('strong', $data['label'])
				)
			));
		}

		return $layout;
	};

	// <input type="password">
	MetaBox::$create_field->password = function ($name, $data) {
		// cache id, label, placeholder
		$id = $name.'_id';
		$label = @$data['label'];
		$placeholder = @$data['placeholder'];

		// cache first value or blank
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';

		// create wrapped password field
		$layout = array(
			array('p',
				array('input', 'type' => 'password', 'id' => $id, 'name' => $name, 'value' => $opt_valueue, 'placeholder' => $placeholder)
			)
		);

		// conditionally prepend wrapped label
		if (isset($data['label'])) {
			array_unshift($layout, array('p',
				array('label', 'for' => $id,
					array('strong', $data['label'])
				)
			));
		}

		return $layout;
	};

	// <textarea>
	MetaBox::$create_field->textarea = function ($name, $data) {
		// cache id, label, placeholder, rows (default 6), cols (default 10)
		$id = $name.'_id';
		$label = @$data['label'];
		$placeholder = @$data['placeholder'];

		// cache first value or blank
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';

		// create textarea
		$textarea = array('textarea', 'id' => $id, 'class' => 'widefat', 'name' => $name, 'placeholder' => $placeholder, $opt_valueue);

		if (isset($data['rows'])) {
			$textarea['rows'] = $data['rows'];
		}

		if (isset($data['cols'])) {
			$textarea['cols'] = $data['cols'];
		}

		if (isset($data['max'])) {
			$textarea['maxlength'] = $data['max'];
		}

		// create paragraph wrapping textarea
		$layout = array(
			array('p', $textarea)
		);

		// conditionally prepend wrapped label
		if (isset($data['label'])) {
			array_unshift($layout, array('p',
				array('label', 'for' => $id,
					array('strong', $data['label'])
				)
			));
		}

		return $layout;
	};

	// <select>
	MetaBox::$create_field->select = function ($name, $data) {
		// cache id, label
		$id = $name.'_id';
		$label = @$data['label'];

		// cache first value or blank
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';

		// initialize options collection
		$options = array();

		// for each option
		foreach ($data['options'] as $opt_name => $opt_value) {
			// create option field
			$option = array('option', $opt_value);

			// conditionally set value attribute
			if (is_string($opt_name)) {
				$option['value'] = $opt_name;
			}

			// conditionally set selected attribute
			if ((is_string($opt_name) && $opt_name === $opt_valueue) || (!is_string($opt_name) && ($opt_name === $opt_valueue || $opt_value === $opt_valueue))) {
				$option['selected'] = '';
			}

			// add to options collection
			array_push($options, $option);
		}

		// create wrapped options collection
		$layout = array(
			array('p',
				array_merge(array('select', 'id' => $id, 'name' => $name), $options)
			)
		);

		// conditionally prepend wrapped label
		if (isset($data['label'])) {
			array_unshift($layout, array('p',
				array('label', 'for' => $id,
					array('strong', $data['label'])
				)
			));
		}

		return $layout;
	};

	// <input type="checkbox">
	MetaBox::$create_field->checkbox = function ($name, $data) {
		// cache id, label
		$id = $name.'_id';
		$label = @$data['label'];

		// assign first value (even if blank) or blank
		$opt_valueue = isset($data['value']) ? $data['value'] : array();

		// initialize options collection, index
		$options = array();
		$index = 0;

		// for each option
		foreach ($data['options'] as $opt_name => $opt_value) {
			// create option field
			$checkbox = array('input', 'type' => 'checkbox', 'name' => $name, 'id' => $name.$index);
			// create label
			$label    = array('label', 'for' => $name.$index, $opt_value);

			// set value attribute as option key or option value
			$checkbox['value'] = is_string($opt_name) ? $opt_name : $opt_value;

			// conditionally set checked attribute
			if (in_array($opt_name, $opt_valueue, true) || in_array($opt_value, $opt_valueue, true)) {
				$checkbox['checked'] = '';
			}

			// add to options collection
			array_push($options, array('p', $checkbox, $label));

			// advance index
			++$index;
		}

		// create wrapped options collection
		$layout = array(
			array_merge(array('p'), $options)
		);

		// conditionally prepend wrapped label
		if (isset($data['label'])) {
			array_unshift($layout, array('p',
				array('strong', $data['label'])
			));
		}

		return $layout;
	};

	// <input type="color">
	MetaBox::$create_field->color = function ($name, $data) {
		// cache id, label
		$id = $name.'_id';
		$label = @$data['label'];

		// cache first value or blank
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';

		// create wrapped text field
		$layout = array(
			array('p',
				array('input', 'type' => 'color', 'id' => $id, 'name' => $name, 'value' => $opt_valueue),
				array('script', 'jQuery(function($){$("#'.$id.'").wpColorPicker()})')
			)
		);

		// conditionally prepend wrapped label
		if (isset($data['label'])) {
			array_unshift($layout, array('p',
				array('label', 'for' => $id,
					array('strong', $data['label'])
				)
			));
		}

		wp_enqueue_style('wp-color-picker');
		wp_enqueue_script('wp-color-picker');

		return $layout;
	};

	// <input type="file">
	MetaBox::$create_field->file = function ($name, $data) {
		// cache id, label
		$id = $name.'_id';
		$label = @$data['label'];

		// cache first value or blank
		$opt_valueue = count(@$data['value']) ? $data['value'][0] : '';

		// create wrapped text field
		$layout = array(
			array('p',
				array('input', 'type' => 'text', 'id' => $id, 'name' => $name, 'value' => $opt_valueue),
				array('script', 'jQuery(function ($) {
					var meta_image_frame;

					$("#'.$id.'").click(function (e) {
						e.preventDefault();

						// If the frame already exists, re-open it.
						if ( meta_image_frame ) {
							wp.media.editor.open();
							return;
						}

						// Sets up the media library frame
						meta_image_frame = wp.media.frames.meta_image_frame = wp.media({
							title: meta_image.title,
							button: { text:  meta_image.button },
							library: { type: "image" }
						});

						// Runs when an image is selected.
						meta_image_frame.on("select", function () {
							// Grabs the attachment selection and creates a JSON representation of the model.
							var media_attachment = meta_image_frame.state().get("selection").first().toJSON();

							// Sends the attachment URL to our custom image input field.
							$("#meta-image").val(media_attachment.url);
						});

						// Opens the media library frame.
						wp.media.editor.open();
					});
				});')
			)
		);

		// conditionally prepend wrapped label
		if (isset($data['label'])) {
			array_unshift($layout, array('p',
				array('label', 'for' => $id,
					array('strong', $data['label'])
				)
			));
		}

		wp_enqueue_style('wp-media-upload');
		wp_enqueue_script('wp-media-upload');

		return $layout;
	};
}
