<?php

if (!class_exists('MetaBox')) {
	class MetaBox {
		// initialize
		function __construct($opts = array()) {
			// sanitize options
			$opts = (array) $opts;

			// set title
			$this->title = isset($opts['title']) ? $opts['title'] : 'More';

			// set id
			$this->id = isset($opts['id']) ? $opts['id'] : preg_replace('/[^a-z0-9]/', '', strtolower($this->title));

			// initialize fields, screens, actions
			$this->fields  = array();
			$this->screens = array();
			$this->actions = array();

			// conditionally add fields
			if (isset($opts['fields']) && is_array($opts['fields'])) {
				foreach ($opts['fields'] as $name => $field) {
					$this->add_field($name, $field);
				}
			}

			// conditionally add screens
			if (isset($opts['screens'])) {
				if (is_string($opts['screens'])) {
					$opts['screens'] = preg_split('/\s*,\s*/', $opts['screens']);
				}

				if (is_array($opts['screens'])) {
					call_user_func_array(array($this, 'add_screen'), $opts['screens']);
				}
			}

			// set context
			$this->set_context(@$opts['context']);

			// set priority
			$this->set_priority(@$opts['priority']);
		}

		// active metabox in wordpress
		public function activate() {
			add_action('add_meta_boxes',  array($this, 'mbf_add_meta_box'));
			add_action('pre_post_update', array($this, 'mbf_pre_post_update'), 10, 2);

			return $this;
		}

		// deactive metabox in wordpress
		public function deactivate() {
			remove_action('add_meta_boxes',  array($this, 'mbf_add_meta_box'));
			remove_action('pre_post_update', array($this, 'mbf_pre_post_update'), 10, 2);

			return $this;
		}

		// add activation to wordpress action(s)
		public function activate_on() {
			foreach (func_get_args() as $name) {
				if (!isset($this->actions[$name])) {
					add_action($name, array($this, 'activate'));

					$this->actions[$name] = true;
				}
			}

			return $this;
		}

		// remove activation from wordpress action(s)
		public function activate_off() {
			foreach (func_get_args() as $name) {
				if (isset($this->actions[$name])) {
					remove_action($name, array($this, 'activate'));

					unset($this->actions[$name]);
				}
			}

			return $this;
		}

		// add field to metabox
		public function add_field($nameOrFields = 'sample', $data = array()) {
			if (is_array($nameOrFields)) {
				foreach ($nameOrFields as $name => $field) {
					$this->fields[$name] = $field;
				}
			} elseif (is_string($nameOrFields)) {
				$this->fields[$nameOrFields] = $data;
			}

			return $this;
		}

		// add metabox to screen
		public function add_screen() {
			foreach (func_get_args() as $screen) {
				// conditionally add screen if not already present
				if (!$this->has_screen($screen)) {
					array_push($this->screens, $screen);

					$this->activate_on('load-post.php', 'load-post-new.php');
				}
			}

			return $this;
		}

		// remove metabox from screen
		public function remove_screen() {
			foreach (func_get_args() as $screen) {
				// conditionally remove screen if already present
				if ($this->has_screen($screen)) {
					array_splice($this->screens, array_search($screen, $this->screens, true), 1);
					
					$this->activate_off('load-post.php', 'load-post-new.php');
				}
			}

			return $this;
		}

		// return whether metabox has screen
		public function has_screen($screen = null) {
			return in_array($screen, $this->screens, true);
		}

		// set metabox context
		public function set_context($context) {
			$this->context = preg_match('/^(normal|advanced|side)$/', $context) ? $context : 'advanced';

			return $this;
		}

		// set metabox priority
		public function set_priority($priority) {
			$this->priority = preg_match('/^(high|core|default|low)$/', $priority) ? $priority : 'default';

			return $this;
		}

		// set metabox title
		public function set_title($title = 'Advanced') {
			$this->title = $title;
		}

		// INTERNAL: add wordpress metabox
		function mbf_add_meta_box() {
			// add meta box to each screen
			foreach ($this->screens as $screen) {
				add_meta_box($this->id, $this->title, array($this, 'mbf_render_meta_box'), $screen, $this->context, $this->priority);
			}
		}

		// INTERNAL: render wordpress metabox
		function mbf_render_meta_box($post) {
			// initialize nonce for secure communication
			wp_nonce_field('mbf_meta_box', 'mbf_meta_box_nonce');

			$buffer = '';

			// render each field
			foreach ($this->fields as $name => $data) {
				$func = isset($data['type']) ? @self::$create_field->{$data['type']} : self::$create_field->text;

				if ($func) {
					if (count(get_post_meta($post->ID, $name))) {
						$data['value'] = get_post_meta($post->ID, $name);
					} else if (!isset($data['value'])) {
						$data['value'] = array();
					} else {
						$data['value'] = is_array($data['value']) ? $data['value'] : array($data['value']);
					}

					$layout = $func($name, $data);

					$buffer .= is_string($layout) ? $layout : forward_static_call_array(array('MetaBox', 'create_element'), $layout);
				}
			}

			print($buffer);
		}

		// INTERNAL: update wordpress metabox
		function mbf_pre_post_update($post_id) {
			// conditionally stop if nonce unverified
			if (!isset($_POST['mbf_meta_box_nonce']) || !wp_verify_nonce($_POST['mbf_meta_box_nonce'], 'mbf_meta_box')) {
				return;
			}

			// conditionally stop if autosaving in progress
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return;
			}

			// conditionally stop if user not permitted
			if (!current_user_can(isset($_POST['post_type']) && $_POST['post_type'] === 'page' ? 'edit_page' : 'edit_post', $post_id)) {
				return;
			}

			// conditionally update each field if set
			foreach ($this->fields as $name => $data) {
				if (isset($_POST[$name])) {
					update_post_meta($post_id, $name, sanitize_text_field($_POST[$name]));
				}
			}
		}

		// create element from array
		static function create_element() {
			// buffer is HTML
			$buffer = '';

			// for each element
			foreach (func_get_args() as $opts) {
				// if element is HTML string, add to buffer and move on
				if (is_string($opts)) {
					$buffer .= $opts;

					continue;
				}

				// open element, element name is first index in array
				$buffer .= '<'.$opts[0];

				// add element attributes from associative keys in array
				foreach ($opts as $name => $value) {
					if (is_string($name)) {
						$buffer .= ' '.$name.'="'.$value.'"';
					}
				}

				// close element, conditionally append children from additional indexes in array
				if (isset($opts[1])) {
					$buffer .= '>';

					for ($i = 1; isset($opts[$i]); ++$i) {
						$buffer .=  self::create_element($opts[$i]);
					}

					// close element
					$buffer .= '</'.$opts[0].'>';
				} else {
					// close element
					$buffer .= '/>';
				}
			}

			return $buffer;
		}

		// create new metabox from json file
		static function load($filename = null, $relative = null) {
			if (!is_string($relative) || !file_exists($relative)) {
				$path = dirname(__FILE__).'/'.$filename;
			} else {
				$path = (is_dir($relative) ? $relative : dirname($relative)).'/'.$filename;
			}

			return new self(json_decode(file_get_contents($path), true));
		}

		// static property will contain metabox field HTML builders
		static $create_field;
	}

	require_once('meta-box-factory-fields.php');
}
