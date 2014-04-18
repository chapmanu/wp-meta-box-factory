<?php

if ( !class_exists('MetaBoxFactory') ){
  /***************************************************************
    MetaBoxHelper Class

    Useage: Instantiate this class with a configuration variable.
    When the constructor runs, it will render the metabox.
    See the example in ../includes/announcement-details.php

    Extending: When adding more types of input to the metabox,
    just add another case to the switch statement of the
    render_meta_box field, then create a function that renders the
    form input.
   **************************************************************/


  class MetaBoxFactory {

    private $box;

    function __construct($_box) {
      $this->box = $_box;
      add_action('add_meta_boxes'   , array($this, 'add_meta_box'));
      add_action('pre_post_update'  , array($this, 'save'), 10, 2);
    }

    function add_meta_box() {
      add_meta_box( $this->box['id'],
                    $this->box['title'],
                    array($this, 'render_meta_box'),
                    $this->box['post_type'],
                    $this->box['context'],
                    $this->box['priority']
                  );
    }

    function save($post_id, $post) {
      if ( !$this->passes_security_check($post) )
        return $post_id;
      foreach ($this->box['fields'] as $field) {
        $key = $field['id'];
        $old_meta = get_post_meta($post_id, $key, true);
        $new_meta = $_POST[$key];

        if ($old_meta != $new_meta)
          update_post_meta( $post_id, $key, $new_meta);
      }
    }

    function render_meta_box($post) {
      wp_nonce_field(basename(__FILE__), $this->nonce());

      foreach ($this->box['fields'] as $field) {
        $current_meta = get_post_meta($post->ID, $field['id'], true);
        if ($current_meta == '') $current_meta = @$field['std'];

        switch ($field['type']) {
          case 'text':
            //render_text_field($field, $current_meta);
            break;
          case 'textarea':
            //render_textarea_field($field, $current_meta);
            break;
          case 'select':
            $this->render_select_field($field, $current_meta);
            break;
          case 'radio':
            //render_radio_field($field, $current_meta);
            break;
          case 'checkbox':
            $this->render_checkbox_field($field, $current_meta);
            break;
          case 'date':
            //render_date_field($field, $current_meta);
          case 'date_from':
            $this->render_datetime_field($field, $current_meta);
            break;
          case 'date_to':
            $this->render_datetime_field($field, $current_meta);
            break;
        }
        echo '<br><br>';
      }
    }

    /**************************************
          FORM RENDERING FUNCITONS
    **************************************/
    private function render_text_field($field, $current_meta) {
      echo    '<label for="', $field['id'], '">', $field['desc'], '</label>';
      echo    '<input type="text"',
                   //'style="font-size: 13px;"',
                   'name="',  $field['id'],   '"',
                   'id="',    $field['id'],   '"',
                   'value="', $current_meta,  '"',
                   '/>';
    }

    private function render_select_field($field, $current_meta) {
      echo '<label for="', $field['id'], '">', $field['desc'], '</label>';
      echo '<select name="', $field['id'],'" id="',$field['id'],'">';

        foreach($field['options'] as $option) {
          $selected = ($option == $current_meta);
          echo '<option value ="', $option ,'" '; if ($selected) echo 'selected="selected" '; echo '>';
          echo $option;
          echo '</option>';
        }
      echo '</select>';
    }

    private function render_checkbox_field($field, $current_meta) {
      echo    '<label for="', $field['id'], '">', $field['desc'], '</label>';
      echo    '<input name="', $field['id'], '" ',
                   'id="', $field['id'], '" ',
                   'type="checkbox" ';
                   if ($current_meta == 'on') echo 'checked ';
                   echo '/>';
    }

    private function render_datetime_field($field, $current_meta) {
      wp_enqueue_script('jquery-ui-datepicker' );
      wp_enqueue_script('jquery-ui-datetimepicker', plugins_url( '../assets/javascripts/timepicker.js' , __FILE__ ));
      wp_enqueue_style( 'jquery-ui-datetimepicker', plugins_url( '../assets/stylesheets/timepicker.css', __FILE__ ));
      $this->render_date_text_field($field, $current_meta);
      $this->datetime_picker($field, $current_meta);
    }

    private function datetime_picker($field, $current_meta) {
      $opposite_direction = $this->opposite_date_direction($field['type']);
      echo '<script>';
      echo
      'jQuery(document).ready(function() {
        jQuery( "#', $field['id'] ,'" ).datetimepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 1,
          stepMinute: 15,
          dateFormat : "D, M d, yy -",
          timeFormat : "h:mmtt"';
          if ($opposite_direction) {
          echo ',
          onClose: function( selectedDateTime ) {
            var friend = jQuery( "#', $field['options']['date_'.$opposite_direction.'_id'] ,'" );
            friend.datetimepicker( "option", "', ($opposite_direction == 'to' ? 'min' : 'max'),'Date", jQuery(this).datetimepicker("getDate") );
            friend.datetimepicker("option", "',  ($opposite_direction == 'to' ? 'min' : 'max'),'Time", jQuery(this).datetimepicker("getDate") );
          }';
          }
          echo
        '});
      });'
      ;
      echo '</script>';
    }

    private function render_date_text_field($field, $current_meta) {
      echo '<label for="', $field['id'], '">';
      echo '<img class="datetime_picker_icon" src="', plugins_url('../assets/images/calendar.svg', __FILE__) ,'" /> ';
      echo $field['desc']; '</label>';
      echo '<input type="text"',
                   'style="font-size: 13px;"',
                   'name="',  $field['id'],   '"',
                   'id="',    $field['id'],   '"',
                   'value="', $current_meta,  '"',
                   '/>';
    }

    private function render_textarea_field($field, $current_meta) {
      // Not implemented
    }

    private function render_radio_field($field, $current_meta) {
      // Not implemented
    }

    /**************************************
              UTILITY FUNCITONS
    **************************************/
    private function nonce() {
      return $this->box['id'] . "_nonce";
    }

    private function passes_security_check($post) {
      return ( $this->nonce_is_valid() && $this->user_has_permission($post) );
    }

    private function nonce_is_valid() {
      return ( isset($_POST[$this->nonce()]) && wp_verify_nonce( $_POST[$this->nonce()], basename(__FILE__)) );
    }

    private function user_has_permission($post) {
      $post_type = get_post_type_object( $post['post_type'] );
      return ( current_user_can($post_type->cap->edit_post, $post_id) );
    }

    private function opposite_date_direction($one_way) {
      if     ($one_way == 'date_from') return 'to';
      elseif ($one_way == 'date_to'  ) return 'from';
      else return false;
    }
  }
}
