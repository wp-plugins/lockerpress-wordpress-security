<?php

class WPSecure
{
	public $settings;
	public $lockouts;
	public $plugin_dir;

	public function __construct()
	{
		if ( ! session_id()) session_start();

		/* Register Activate / Deactivate */
		register_activation_hook(__FILE__,   array(&$this, 'install'));
		register_deactivation_hook(__FILE__, array(&$this, 'uninstall'));

		$this->settings		= get_option('wp_secure');
		$this->plugin_dir	= dirname(__FILE__) . '/';
		$this->plugin_url	= get_bloginfo('siteurl') . '/wp-content/plugins/lockerpress/';
		$this->plugin_page	= get_bloginfo('siteurl') . '/wp-admin/admin.php?page=lockerpress/core.php';
		$this->licensed		= FALSE;

		add_action('admin_menu', array(&$this, 'setup_menu'));
		add_action('wp_ajax_load_sidebar_info', array(&$this, 'load_sidebar_info'));
		add_action('wp_login_failed',  array(&$this, 'login_fail'));
		add_action('wp_logout', array(&$this, 'logout'));
		add_action('init', array(&$this, 'demo_init'));
	}

	public function demo_init()
	{
		$login = get_option('wp_secure_login');

		if($login != '' && strpos($_SERVER['SCRIPT_NAME'], 'wp-login.php') and $_GET['action'] != 'logout' and !isset($_GET['loggedout']) and !isset($_GET['login_recaptcha_err']))
		{
			if(!isset($_SESSION['secure_login']))
			{
				header('Location: ' . get_bloginfo('url'));
				exit;
			}

			$code = get_option('wp_secure_login_code');

			if($_SESSION['secure_login'] != $code)
			{
				header('Location: ' . get_bloginfo('url'));
				exit;
			}
		}
		else if(!is_user_logged_in() and @strpos($_SERVER['REQUEST_URI'], $login))
		{
			$code = get_option('wp_secure_login_code');
			$_SESSION['secure_login'] = $code;

			header('Location: ' . get_bloginfo('url') . '/wp-login.php');
			exit;
		}

		if(strpos($_SERVER["REQUEST_URI"], 'lockerpress/core.php'))
		{
			wp_enqueue_script('thickbox');
			wp_enqueue_style('thickbox');
		}

		if($this->settings['secure_source'] == 1)
			add_action('wp_head', 'disableRightClick');
	}

	public function check_bans()
	{
		$bans = get_option('wp_secure_bans');

		for($i = 0; $i < count($bans); $i++)
		{
			if($bans[$i]['ip'] == $_SERVER['REMOTE_ADDR'])
			{
				$time = intval($bans[$i]['time']);
				$now = time();
				if(($now - $time) < ($this->settings['email_hack_minutes'] * 60))
					$this->_display_ban_message();

				unset($bans[$i]);
				unset($_SESSION['wpsecure_attempts']);
			}
			else if(time() - intval($bans[$i]['time']) > ($this->settings['email_hack_minutes'] * 60))
				unset($bans[$i]);
		}

		$bans = array_values($bans);
		update_option('wp_secure_bans', $bans);
		return true;
	}

	public function logout()
	{
		unset($_SESSION['secure_login']);
	}

	public function install()
	{
		$wpsecure = array(
			'recaptcha' => 1,
			'allowed_retries' => 4,
			'lockout_duration' => 20,
			'allowed_lockouts' => 4,
			'valid_duration' => 12,
			'cookies' => 1,
			'lockout_notify_log' => 1,
			'lockout_notify_email' => 0,
			'email_after' => 4,
			'secure_source' => 0,
			'email_hack_failure_count' => 2,
			'email_hack_minutes' => 15
		);

		$users = array();

		add_option('wp_secure', $wpsecure, true);
		add_option('wp_secure_lockouts', array(), true);
		add_option('wp_secure_login', '');
		add_option('wp_secure_login_code', '');
		add_option('wp_secure_bans', array(), '', true);
		add_option('wp_secure_hack_message', 'You have been banned for [BAN_MINUTES] minutes!');
	}

	public function uninstall()
	{
		delete_option('wp_secure');
		delete_option('wp_secure_lockouts');
		delete_option('wp_secure_login');
		delete_option('wp_secure_login_code');
		delete_option('wp_secure_bans');
		delete_option('wp_secure_hack_message');
	}

	public function setup_menu()
	{
		add_menu_page('LockerPress', 'LockerPress', 'administrator', __FILE__, array(&$this, 'home_page'));
		add_submenu_page(__FILE__, 'Custom Login URL', 'Custom Login URL', 'administrator', 'login_url', array(&$this, 'login_url'));
	}

	public function login_fail()
	{
		if(!isset($_SESSION['wpsecure_attempts']))
			$_SESSION['wpsecure_attempts'] = 1;
		else
			$_SESSION['wpsecure_attempts'] = intval($_SESSION['wpsecure_attempts']) + 1;

		if($this->settings['email_hack_failure_count'] == 1)
		{
			$this->_ban_user();
			$this->_display_ban_message();
		}
	}

	public function home_page()
	{
		$plugin_url = $this->plugin_url;
		$settings = $this->settings;
		if(isset($_POST['save']))
		{
			$settings = $this->settings;
			$fields = array('email_hack', 'email_hack_minutes', 'email_hack_failure_count', 'secure_source');
			foreach($fields as $field)
				$settings[$field] = $_POST[$field];
			update_option('wp_secure', $settings);
			update_option('wp_secure_hack_message', strip_tags($_POST['hack_message']));
			echo '<script type="text/javascript">window.location="' . $this->plugin_page . '&settings-updated=1' . '";</script>';
			exit;
		}
		require_once 'views/wpsecure_index.php';
	}

	public function login_url()
	{
		if(isset($_POST['save']))
		{
			update_option('wp_secure_login', $_POST['login']);
			update_option('wp_secure_login_code', md5(time()));
			$_GET['settings-updated'] = true;
		}

		require_once 'views/login_url.php';
	}

	public function load_sidebar_info()
	{
		$response = wp_remote_post('http://lockerpress.com/update/wpsecurity/demo_info.php');
		echo json_encode(array('info' => unserialize($response['body'])));
		exit;
	}

	private function _display_ban_message()
	{
		if($this->settings['unban_url'] != '' AND strpos($_SERVER['REQUEST_URI'], $this->settings['unban_url'])) {
			if( ! isset($_POST['unban_password']) OR $_POST['unban_password'] == '' OR
				md5($_POST['unban_password']) != $this->settings['unban_password']) {

				$message = '';
				if(isset($_POST['unban_password']) AND md5($_POST['unban_password']) != $this->settings['unban_password'])
					$message = '<p style="color:red">Incorrect password.</p>';
echo <<<EOF
<div style="text-align:center">
<strong>Please enter Unban Password</strong><br /><br />
{$message}
<form method="post" action="">
<p style="text-align:center">
Unban Password: <input type="password" name="unban_password" />
<input type="submit" value="Go" />
</p>
</form>
</div>
EOF;
exit;
			}
		} else {
			$search = array('[IP]','[NEWLINE]','[ADMIN_EMAIL]','[SITE]','[BAN_MINUTES]');
			$replace = array($_SERVER['REMOTE_ADDR'], '<br />', get_bloginfo('admin_email'), get_bloginfo('siteurl'), $this->settings['email_hack_minutes']);

			echo '<p style="text-align:center;"><br /><br />' . str_replace($search, $replace, get_option('wp_secure_hack_message')) . '</p>';
			exit;
		}
	}

	private function _ban_user()
	{
		$bans = get_option('wp_secure_bans');
		$ban = array(
			'ip' => $_SERVER['REMOTE_ADDR'],
			'time' => time()
			);
		$bans[] = $ban;

		update_option('wp_secure_bans', $bans);
	}
}

$wpSecure = new WPSecure;

function wpsecure_header()
{
	echo "\n<style type=\"text/css\">#message {width:90%;}</style>\n";
	//Flash messages
	if(isset($_SESSION['success_message'])) {
		echo '<div id="message" class="updated fade"><p><strong>' . $_SESSION['success_message'] . '</strong></p></div>';
		unset($_SESSION['success_message']);
	}
	if(isset($_SESSION['error_message'])) {
		echo '<div id="message" class="error"><p><strong>' . $_SESSION['error_message'] . '</strong></p></div>';
		unset($_SESSION['error_message']);
	}
}

function wpsecure_footer()
{
	require_once 'views/footer.php';
}

function disableRightClick()
{
	//@@ on the front-end
	if (!is_admin())
	{
echo <<<EOF
<script type="text/javascript">
<!--
function clickIE() {if (document.all) {return false;}}
function clickNS(e) {if
(document.layers||(document.getElementById&&!document.all)) {
if (e.which==2||e.which==3) {return false;}}}
if (document.layers)
{document.captureEvents(Event.MOUSEDOWN);document.onmousedown=clickNS;}
else{document.onmouseup=clickNS;document.oncontextmenu=clickIE;}
document.oncontextmenu=new Function("return false")
// -->
</script>
EOF;
	}
}