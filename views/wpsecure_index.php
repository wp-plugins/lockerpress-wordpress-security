<?php
wp_enqueue_style('jquery-ui-theme-full', $plugin_url . 'css/ui-lightness/jquery-ui-1.8.20.custom.css');
wp_enqueue_script('jquery-ui-slider');
?>
<div class="wrap">
  <div class="postbox-container" style="width:70%">
    <div id="icon-options-general" class="icon32"></div>
    <h2>LockerPress WordPress Security</h2>
    <br />

    <?php if( isset($_GET['settings-updated']) ) { ?>
    <div id="message" class="updated" style="width:90%;">
        <p><strong><?php _e('Settings updated.') ?></strong></p>
    </div>
    <?php } $selected = 'selected="selected"'; ?>

    <form action="" method="post">
      <h3>Email hack attempts:</h3>
      <table id="createuser" class="form-table">
        <tr>
          <th scope="row">
              <label for="email_hack">Send Admin an email on each Hack Attempt?</label>
          </th>
          <td>
            <input type="checkbox" id="email_hack" value="1" <?php if($settings['email_hack'] == 1) echo 'checked=""'; ?> name="email_hack"> &nbsp; Yes please notify me!<br />
            <br />
            <select name="email_hack_failure_count">
              <?php for($i = 1; $i < 15; $i++) { ?>
              <option <?php echo (int)$settings['email_hack_failure_count']==$i?'selected="selected"':''; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
              <?php } ?>
            </select> <span class="description">Number of failed login attempts before Hacker's IP is banned</span>
            <br /><br />
            <input type="text" size="4" id="email_hack_minutes_count" name="email_hack_minutes" readonly="readonly" /></span>&nbsp;<span class="description">... Now choose how many minutes you want the Hacker to be banned for</span>
            <div id="email_hack_minutes"></div>
            <br />
            <span class="description">Enter your Custom Message to be displayed to the Hacker after a Ban is set - don't be nice to him!</span>
            <textarea rows="5" cols="80" name="hack_message"><?php echo get_option('wp_secure_hack_message'); ?></textarea><br />
            Message displayed once user is banned. <a class="button-secondary" href="javascript:preview_message()">preview</a><br />
            <span class="description">Available tags are [IP], [NEWLINE], [ADMIN_EMAIL], [SITE], [BAN_MINUTES]</span>
            <div id="preview_html" style="display:none;"></div>
          </td>
        </tr>
        <tr>
          <td colspan="2"><p>We've allowed some functionality to be hidden on your WordPress Site. You can disable people from right-clicking on your Website.</p></td>
        </tr>
        <tr>
          <th scope="row">Disable Right-Click</th>
          <td>
            <input <?php echo $settings['secure_source'] == 1?'checked':''; ?> type="checkbox" name="secure_source" value="1" />&nbsp;Yes, remove right click functionality<br />(this prevents users from viewing your source code as well when right clicking!)
          </td>
        </tr>
      </table>
      <br />
      <p>
        <input class="button-primary" type="submit" name="save" value="<?php _e('Save Options'); ?>" />
      </p>
    </form>
  </div>
  <div class="postbox-container" style="width:28%">
    <div class="metabox-holder" id="wpsecure-info"></div>
  </div>
</div>
<script type="text/javascript">
  function preview_message()
  {
    jQuery(function($) {
      var message = $("textarea[name=hack_message]").val();
      message = message.replace('[ip]', '<?php echo $_SERVER['REMOTE_ADDR']; ?>');
      message = message.replace('[NEWLINE]', '<br />');
      message = message.replace('[ADMIN_EMAIL]', '<?php echo get_bloginfo('admin_email'); ?>');
      message = message.replace('[SITE]', '<?php echo get_bloginfo('siteurl'); ?>');
      message = message.replace('[BAN_MINUTES]', $("#email_hack_minutes_count").val());
      $("#preview_html").html('<p style="text-align:center">'+message+'<br /><br /><a class="button-secondary" href="javascript:preview_message_close()">close</a></p>');
    });
    var url = "#TB_inline?height=225&width=350&inlineId=preview_html&modal=true";
    tb_show("Message Preview", url);
  }

  function preview_message_close()
  {
    tb_remove();
  }

  jQuery(document).ready(function($) {
    $("#email_hack_minutes").slider({
      value:<?php echo (int)$settings['email_hack_minutes']; ?>,
      min: 10,
      max: 180,
      step: 10,
      slide: function( event, ui ) {
        $("#email_hack_minutes_count").val(ui.value );
      }
    });
    $("#email_hack_minutes_count").val($("#email_hack_minutes").slider( "value" ));
  });
</script>
<?php wpsecure_footer(); ?>