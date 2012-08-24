<?php

class NewsletterModuleControls {

  var $data;
  var $action = false;

  function __construct($options = null) {
    if ($options == null)
      $this->data = stripslashes_deep($_POST['options']);
    else
      $this->data = $options;

    $this->action = $_REQUEST['act'];
  }

  function merge_defaults($defaults) {
    if ($this->data == null)
      $this->data = $defaults;
    else
      $this->data = array_merge($defaults, $this->data);
  }

  /**
   * Return true is there in an asked action is no action name is specified or
   * true is the requested action matches the passed action.
   * Dies if it is not a safe call.
   */
  function is_action($action = null) {
    if ($action == null)
      return $this->action != null;
    if ($this->action == null)
      return false;
    if ($this->action != $action)
      return false;
    if (check_admin_referer())
      return true;
    die('Invalid call');
  }

  /**
   * Show the errors and messages. 
   */
  function show() {
    if (!empty($this->errors)) {
      echo '<div class="error">';
      echo $this->errors;
      echo '</div>';
    }
    if (!empty($this->messages)) {
      echo '<div class="updated">';
      echo $this->messages;
      echo '</div>';
    }
  }

  function yesno($name) {
    $value = isset($this->data[$name]) ? (int) $this->data[$name] : 0;

    echo '<select style="width: 60px" name="options[' . $name . ']">';
    echo '<option value="0"';
    if ($value == 0)
      echo ' selected';
    echo '>No</option>';
    echo '<option value="1"';
    if ($value == 1)
      echo ' selected';
    echo '>Yes</option>';
    echo '</select>&nbsp;&nbsp;&nbsp;';
  }

  function enabled($name) {
    $value = isset($this->data[$name]) ? (int) $this->data[$name] : 0;

    echo '<select style="width: 100px" name="options[' . $name . ']">';
    echo '<option value="0"';
    if ($value == 0)
      echo ' selected';
    echo '>Disabled</option>';
    echo '<option value="1"';
    if ($value == 1)
      echo ' selected';
    echo '>Enabled</option>';
    echo '</select>';
  }

  function page_themes($name) {
    $themes[''] = 'Standard page themes';
    $themes['page-1'] = 'Page theme 1';

    $handle = @opendir(ABSPATH . 'wp-content/plugins/newsletter-custom/themes-page');
    $list = array();
    if ($handle) {
      while ($file = readdir($handle)) {
        if ($file == '.' || $file == '..')
          continue;
        if (!is_dir(ABSPATH . 'wp-content/plugins/newsletter-custom/themes-page/' . $file))
          continue;
        if (!is_file(ABSPATH . 'wp-content/plugins/newsletter-custom/themes-page/' . $file . '/theme.php'))
          continue;
        $list['*' . $file] = $file;
      }
      closedir($handle);
    }
    


    $this->select_grouped($name, array(
        array_merge(array('' => 'Custom page themes'), $list),
        $themes
    ));
  }

  function checkbox_group($name, $value, $label = '') {
    echo '<input type="checkbox" id="' . $name . '" name="options[' . $name . '][]" value="' . $value . '"';
    if (is_array($this->data[$name]) && array_search($value, $this->data[$name]) !== false)
      echo ' checked="checked"';
    echo '/>';
    if ($label != '')
      echo ' <label for="' . $name . '">' . $label . '</label>';
  }

  function select_group($name, $options) {
    echo '<select name="options[' . $name . '][]">';

    foreach ($options as $key => $label) {
      echo '<option value="' . $key . '"';
      if (is_array($this->data[$name]) && array_search($value, $this->data[$name]) !== false)
        echo ' selected';
      echo '>' . htmlspecialchars($label) . '</option>';
    }

    echo '</select>';
  }

  function select($name, $options, $first = null) {
    $value = $this->data[$name];

    echo '<select id="options-' . $name . '" name="options[' . $name . ']">';
    if (!empty($first)) {
      echo '<option value="">' . htmlspecialchars($first) . '</option>';
    }
    foreach ($options as $key => $label) {
      echo '<option value="' . $key . '"';
      if ($value == $key)
        echo ' selected';
      echo '>' . htmlspecialchars($label) . '</option>';
    }
    echo '</select>';
  }

  function select_grouped($name, $groups) {
    $value = $this->data[$name];

    echo '<select name="options[' . $name . ']">';

    foreach ($groups as $group) {
      echo '<optgroup label="' . htmlspecialchars($group['']) . '">';
      if (!empty($group)) {
        foreach ($group as $key => $label) {
          if ($key == '')
            continue;
          echo '<option value="' . $key . '"';
          if ($value == $key)
            echo ' selected';
          echo '>' . htmlspecialchars($label) . '</option>';
        }
      }
      echo '</optgroup>';
    }
    echo '</select>';
  }

  /**
   * Generated a select control with all available templates. From version 3 there are
   * only on kind of templates, they are no more separated by type.
   */
  function themes($name, $theme_dir) {
    $handle = @opendir($theme_dir);
    $list = array();
    while ($file = readdir($handle)) {
      if ($file == '.' || $file == '..')
        continue;
      // TODO: optimize the string concatenation
      if (!is_dir($theme_dir . '/' . $file))
        continue;
      if (!is_file($theme_dir . '/' . $file . '/theme.php'))
        continue;
      $list[$theme_dir . '/' . $file] = $file;
    }
    closedir($handle);

    $this->select($name, $list);
  }

  function value($name) {
    echo htmlspecialchars($this->data[$name]);
  }

  function value_date($name) {
    $time = $this->data[$name];
    echo gmdate(get_option('date_format') . ' ' . get_option('time_format'), $time + get_option('gmt_offset') * 3600);
  }

  function text($name, $size = 20) {
    echo '<input name="options[' . $name . ']" type="text" size="' . $size . '" value="';
    echo htmlspecialchars($this->data[$name]);
    echo '"/>';
  }

  function hidden($name) {
    echo '<input name="options[' . $name . ']" type="hidden" value="';
    echo htmlspecialchars($this->data[$name]);
    echo '"/>';
  }

  function button($action, $label, $function = null) {
    if ($function != null) {
      echo '<input class="button-secondary" type="button" value="' . $label . '" onclick="this.form.act.value=\'' . $action . '\';' . htmlspecialchars($function) . '"/>';
    } else {
      echo '<input class="button-secondary" type="button" value="' . $label . '" onclick="this.form.act.value=\'' . $action . '\';this.form.submit()"/>';
    }
  }

  function button_confirm($action, $label, $message, $data = '') {
    echo '<input class="button-secondary" type="button" value="' . $label . '" onclick="this.form.btn.value=\'' . $data . '\';this.form.act.value=\'' . $action . '\';if (confirm(\'' .
    htmlspecialchars($message) . '\')) this.form.submit()"/>';
  }

  function editor($name, $rows = 5, $cols = 75) {
    echo '<textarea class="visual" name="options[' . $name . ']" style="width: 100%" wrap="off" rows="' . $rows . '" cols="' . $cols . '">';
    echo htmlspecialchars($this->data[$name]);
    echo '</textarea>';
  }

  function textarea($name, $width = '100%', $height = '50') {
    echo '<textarea class="dymanic" name="options[' . $name . ']" wrap="off" style="width:' . $width . ';height:' . $height . '">';
    echo htmlspecialchars($this->data[$name]);
    echo '</textarea>';
  }

  function textarea_fixed($name, $width = '100%', $height = '50') {
    echo '<textarea name="options[' . $name . ']" wrap="off" style="width:' . $width . ';height:' . $height . '">';
    echo htmlspecialchars($this->data[$name]);
    echo '</textarea>';
  }

  function email($prefix) {
    echo 'Subject:<br />';
    $this->text($prefix . '_subject', 70);
    echo '<br />Message:<br />';
    $this->editor($prefix . '_message');
  }

  function checkbox($name, $label = '') {
    echo '<input type="checkbox" id="' . $name . '" name="options[' . $name . ']" value="1"';
    if (!empty($this->data[$name]))
      echo ' checked="checked"';
    echo '/>';
    if ($label != '')
      echo ' <label for="' . $name . '">' . $label . '</label>';
  }

  function hours($name) {
    $hours = array();
    for ($i = 0; $i < 24; $i++) {
      $hours['' . $i] = '' . $i;
    }
    $this->select($name, $hours);
  }

  function days($name) {
    $days = array(0 => 'Every day', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday');
    $this->select($name, $days);
  }

  function init() {
    echo '<script type="text/javascript">
    jQuery(document).ready(function(){
        jQuery("textarea.dynamic").focus(function() {
            jQuery("textarea.dynamic").css("height", "50px");
            jQuery(this).css("height", "400px");
        });
      tabs = jQuery("#tabs").tabs({ cookie: { expires: 30 } });       
    });
</script>
';
    echo '<input name="act" type="hidden" value=""/>';
    echo '<input name="btn" type="hidden" value=""/>';
    wp_nonce_field();
  }

  function button_link($action, $url, $anchor) {
    if (strpos($url, '?') !== false)
      $url .= $url . '&';
    else
      $url .= $url . '?';
    $url .= 'act=' . $action;

    $url .= '&_wpnonce=' . wp_create_nonce();

    echo '<a class="button" href="' . $url . '">' . $anchor . '</a>';
  }

}

/**
 * Generic administrative functions.
 */
class NewsletterAdmin {

  static function get_test_subscribers() {
    global $newsletter, $wpdb;

    $subscribers = array();
    for ($i = 0; $i < 5; $i++) {
      if (empty($newsletter->options_main['test_email_' . $i]))
        continue;
      $subscriber = new stdClass();
      $subscriber->name = $newsletter->options_main['test_name_' . $i];
      $subscriber->email = $newsletter->options_main['test_email_' . $i];
      $subscriber->sex = $newsletter->options_main['test_sex_' . $i];
      $subscriber->token = 'notokenitsatest';
      $subscriber->id = 0;
      $subscriber->feed_time = 0;
      $subscriber->followup_time = 0;

      $subscribers[] = $subscriber;
    }

    $others = $wpdb->get_results("select * from " . $wpdb->prefix . "newsletter where test=1");
    if (!empty($others)) {
      foreach ($others as &$other) {
        $subscribers[] = $other;
      }
    }
    return $subscribers;
  }

  /**
   * Prints a formatted dated of the time passed or a dash if time is empty.
   */
  static function date_i18n($time) {
    if (empty($time))
      echo '-'; else
      echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $time);
  }

}

?>
