<div class="wrap">
  <div class="postbox-container" style="width:70%">
    <h2><img src="/wp-content/plugins/lockerpress/images/lockerpress_logo.png" />LockerPress - Custom Login URL</h2>
    <?php if( isset($_GET['settings-updated']) ) { ?>
    <div id="message" class="updated">
      <p><strong><?php _e('Settings updated.') ?></strong></p>
    </div>
    <?php } $selected = 'selected="selected"'; ?>
    <div>
		<h3>Overview:</h3>
		<p>One of the most dreadful mistakes when setting up a WordPress site is having the same login structure that everyone else has.</p>
		<p>By default, WordPress sets you up with /wp-admin and /wp-login.php so you can login and start modifying your website.</p>
		<p>Unfortunately, 88% of WordPress users do not change or modify these URL's - giving Hackers &amp; Bots direct access to your login page.</p>
		<p>Now you can easily create your own custom URL. When you do, you disable the default /wp-admin and /wp-login.php logins URL's</p>
		<p>The best part... your custom URL is not indexed! This creates extreme aggravation and frustration to Hackers &amp; Bots since they can't simply scan and find your Custom Login URL.</p>
		<h3>Instructions:</h3>
      <p>Enter your Custom Login URL in the field below and click "Save LockerPress Settings".</p>
      <form method="post">
        <p>
        <input type="text" name="login" value="<?php echo get_option('wp_secure_login'); ?>" /><br /><br />
        <span class="description">Example:  secret-login-page<br /><br />
        <strong>IMPORTANT NOTE:</strong> If you're using the default permalinks, please add a question mark (?) before<br />your custom login URL when accessing the link.<br /><br />
        Example:  http://www.example.com/?secret-login-page</span>
        </p>
        <p class="submit">
          <input type="submit" class="button-primary" value="Save LockerPress Settings" name="save">
        </p>
      </form>
    </div>
  </div>
  <div class="postbox-container" style="width:28%">
    <div class="metabox-holder" id="wpsecure-info"></div>
  </div>
</div>
<?php wpsecure_footer(); ?>