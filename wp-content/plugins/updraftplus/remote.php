<?php

if (!defined('UPDRAFTPLUS_DIR')) die('No access.');

/*
	- A container for all the RPC commands implemented. Commands map exactly onto method names (and hence this class should not implement anything else, beyond the constructor, and private methods)
	- Return format is array('response' => (string - a code), 'data' => (mixed));
	
	RPC commands are not allowed to begin with an underscore. So, any private methods can be prefixed with an underscore.
	
	TODO:
	
	- Security: need to call wp_set_current_user() when doing actions, so that current_user_can() then works
	
	- Instead of just doing error_log() when there is a problem, also call a WP action - and have something hook that and log it in the DB (plus a way to show it)
	
*/
class UpdraftPlus_RemoteControl_Commands {

	private $rc;
	private $ud;

	public function __construct($rc) {
		$this->rc = $rc;
		global $updraftplus;
		$this->ud = $updraftplus;
	}
	
	public function get_login_url($data, $extra_info) {
		if (is_array($extra_info) && !empty($extra_info['user_id']) && is_numeric($extra_info['user_id'])) {
		
			$user_id = $extra_info['user_id'];
		
			if (false == ($login_key = $this->_get_autologin_key($user_id))) return $this->_generic_error_response('user_key_failure');
		
			// Over-write any previous value - only one can be valid at a time)
			update_user_meta($user_id, 'updraftcentral_login_key', array('key' => $login_key, 'created' => time()));
		
			return $this->_response(array(
				'login_url' => network_site_url('?udcentral_action=login&login_id='.$user_id.'&login_key='.$login_key)
			));

		} else {
			return $this->_generic_error_response('user_unknown');
		}
	}
	
	// This is intended to be short-lived. Hence, there's no intention other than that it is random and only used once - only the most recent one is valid.
	public function _get_autologin_key($user_id) {
		$secure_auth_key = defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : hash('sha256', DB_PASSWORD).'_'.rand(0, 999999999);
		if (!defined('SECURE_AUTH_KEY')) return false;
		$hash_it = $user_id.'_'.microtime(true).'_'.rand(0, 999999999).'_'.$secure_auth_key;
		$hash = hash('sha256', $hash_it);
		return $hash;
	}
	
	public function site_info() {
		global $wp_version, $wpdb;
		@include(ABSPATH.WPINC.'/version.php');

		$ud_version = is_a($this->ud, 'UpdraftPlus') ? $this->ud->version : __('none', 'updraftplus');
		
		return $this->_response(array(
			'versions' => array(
				'ud' => $ud_version,
				'php' => PHP_VERSION,
				'wp' => $wp_version,
				'mysql' => $wpdb->db_version(),
				'udrpc_php' => $this->rc->udrpc_version,
			),
			'bloginfo' => array(
				'url' => network_site_url(),
				'name' => get_bloginfo('name'),
			)
		));
	}
	
	public function updraftplus_backup_progress($params) {
	
		if (false === ($updraftplus_admin = $this->_load_ud_admin())) return $this->_generic_error_response('no_updraftplus');
		
		if (!UpdraftPlus_Options::user_can_manage()) return $this->_generic_error_response('updraftplus_permission_denied');
		
		$request = array(
			'thisjobonly' => $params['job_id']
		);
		
		$activejobs_list = $updraftplus_admin->get_activejobs_list($request);
		
		return $this->_response($activejobs_list);
	
	}
	
	public function updraftplus_backupnow($params) {
		
		if (false === ($updraftplus_admin = $this->_load_ud_admin())) return $this->_generic_error_response('no_updraftplus');
		if (!UpdraftPlus_Options::user_can_manage()) return $this->_generic_error_response('updraftplus_permission_denied');

		$updraftplus_admin->request_backupnow($params, array($this, '_updraftplus_backup_started'));
		
		// Control returns when the backup finished; but, the browser connection should have been closed before
		die;
	}
	
	public function _updraftplus_backup_started($msg) {
	
		// Under-the-hood hackery to allow the browser connection to be closed, and the backup to continue
		
		$rpc_response = $this->rc->return_rpc_message($this->_response($msg));
		
		$data = isset($rpc_response['data']) ? $rpc_response['data'] : null;

		$ud_rpc = $this->rc->get_current_udrpc();
		
		$encoded = json_encode($ud_rpc->create_message($rpc_response['response'], $data, true));
		
		$this->_load_ud()->close_browser_connection($encoded);

	}
	
	private function _load_ud() {
		global $updraftplus;
		return is_a($updraftplus, 'UpdraftPlus') ? $updraftplus : false;
	}
	
	private function _load_ud_admin() {
		if (!defined('UPDRAFTPLUS_DIR') || !is_file(UPDRAFTPLUS_DIR.'/admin.php')) return false;
		require_once(UPDRAFTPLUS_DIR.'/admin.php');
		global $updraftplus_admin;
		return $updraftplus_admin;
	}
	
	public function ud_get_log($job_id) {
	
		if (false === ($updraftplus = $this->_load_ud())) return $this->_generic_error_response('no_updraftplus');
	
		if (!UpdraftPlus_Options::user_can_manage()) return $this->_generic_error_response('updraftplus_permission_denied');
		
		if (!preg_match("/^[0-9a-f]{12}$/", $job_id)) return $this->_generic_error_response('updraftplus_permission_invalid_jobid');
		
		$updraft_dir = $updraftplus->backups_dir_location();
		$log_file = $updraft_dir.'/log.'.$job_id.'.txt';
		
		if (is_readable($log_file)) {
			return $this->_response(array('log' => file_get_contents($log_file)));
		} else {
			return $this->_generic_error_response('updraftplus_unreadable_log');
		}
	
	}
	
	public function ud_activejobs_delete($job_id) {
	
		if (false === ($updraftplus_admin = $this->_load_ud_admin())) return $this->_generic_error_response('no_updraftplus');
		if (!UpdraftPlus_Options::user_can_manage()) return $this->_generic_error_response('updraftplus_permission_denied');

		$delete = $updraftplus_admin->activejobs_delete((string)$job_id);
		return $this->_response($delete);

	}
	
	public function ud_rescan($what) {

		if (false === ($updraftplus_admin = $this->_load_ud_admin()) || false === ($updraftplus = $this->_load_ud())) return $this->_generic_error_response('no_updraftplus');
		if (!UpdraftPlus_Options::user_can_manage()) return $this->_generic_error_response('updraftplus_permission_denied');
		
		$remotescan = ('remotescan' == $what);
		$rescan = ($remotescan || 'rescan' == $what);
		
		$history_status = $updraftplus_admin->get_history_status($rescan, $remotescan);

		return $this->_response($history_status);
		
	}
	
	public function phpinfo() {
	
		$phpinfo = $this->_get_phpinfo_array();
		
		if (!empty($phpinfo)){
			return $this->_response($phpinfo);
		}
		
		return $this->_generic_error_response('phpinfo_fail');

	}
	
	// https://secure.php.net/phpinfo
	private function _get_phpinfo_array() {
		ob_start();
		phpinfo(11);
		$phpinfo = array('phpinfo' => array());

		if (preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER)){
			foreach($matches as $match){
			if(strlen($match[1])){
				$phpinfo[$match[1]] = array();
			}elseif(isset($match[3])){
			$keys1 = array_keys($phpinfo);
			$phpinfo[end($keys1)][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
			} else {
				$keys1 = array_keys($phpinfo);
				$phpinfo[end($keys1)][] = $match[2];     
			
			}
		
			}
			return $phpinfo;
		}

		return false;
		
	}
	
	public function ud_get_fragment($fragment) {
	
		if (false === ($updraftplus_admin = $this->_load_ud_admin()) || false === ($updraftplus = $this->_load_ud())) return $this->_generic_error_response('no_updraftplus');
		
		if (!UpdraftPlus_Options::user_can_manage()) return $this->_generic_error_response('updraftplus_permission_denied');

		if (is_array($fragment)) {
			$data = $fragment['data'];
			$fragment = $fragment['fragment'];
		}
		
		$error = false;
		switch ($fragment) {
			case 'backupnow_modal_contents':
				$output = $updraftplus_admin->backupnow_modal_contents();
			break;
			case 'panel_download_and_restore':
				// TODO: Replace the onsubmit handlers on the button forms
				
				$backup_history = UpdraftPlus_Options::get_updraft_option('updraft_backup_history');
				if (empty($backup_history)) {
					$updraftplus->rebuild_backup_history();
					$backup_history = UpdraftPlus_Options::get_updraft_option('updraft_backup_history');
				}
				$backup_history = is_array($backup_history) ? $backup_history : array();
				
				$output = $updraftplus_admin->settings_downloading_and_restoring($backup_history, true, $data);
			break;
			case 'disk_usage':
				$output =  $updraftplus_admin->get_disk_space_used($data);
			break;
			default:
			// We just return a code - translation is done on the other side
			$output = 'ud_get_fragment_could_not_return';
			$error = true;
			break;
		}
		
		if (empty($error)) {
			return $this->_response(array(
				'output' => $output,
			));
		} else {
			return $this->_generic_error_response($output);
		}
		
	}
	
	private function _response($data = null, $code = 'rpcok') {
		return array(
			'response' => $code,
			'data' => $data
		);
	}
	
	private function _generic_error_response($code = 'central_unspecified', $data = null) {
		return $this->_response(
			array(
				'code' => $code,
				'data' => $data
			),
			'rpcerror'
		);
	}
	
}

class UpdraftPlus_RemoteControl {

	public $udrpc_version;
	private $ud = null;
	private $receivers = array();
	private $extra_info = array();
	private $php_events = array();
	private $commands;
	private $current_udrpc = null;

	public function __construct($keys = array()) {
		global $updraftplus;
		$this->ud = $updraftplus;

		$this->commands = new UpdraftPlus_RemoteControl_Commands($this);
		
		foreach ($keys as $name_hash => $key) {
			if (!is_array($key) || empty($key['extra_info'])) return;
			$indicator = $name_hash.'.central.updraftplus.com';
			$ud_rpc = $this->ud->get_udrpc($indicator);
			$this->udrpc_version = $ud_rpc->version;
			
			// Only turn this on if you are comfortable with potentially anything appearing in your PHP error log
			if (defined('UPDRAFTPLUS_UDRPC_FORCE_DEBUG') && UPDRAFTPLUS_UDRPC_FORCE_DEBUG) $ud_rpc->set_debug(true);
			$this->receivers[$indicator] = $ud_rpc;
			$this->extra_info[$indicator] = isset($key['extra_info']) ? $key['extra_info'] : null;
			$ud_rpc->set_key_local($key['key']);
			$ud_rpc->set_key_remote($key['publickey_remote']);
			// Create listener (which causes WP actions to be fired when messages are received)
			$ud_rpc->activate_replay_protection();
			if (!empty($key['extra_info']) && isset($key['extra_info']['mothership']) && false != ($parsed = parse_url($key['extra_info']['mothership'])) && is_array($parsed)) {
				$url = $parsed['scheme'].'://'.$parsed['host'];
				$ud_rpc->set_allow_cors_from(array($url));
			}
			$ud_rpc->create_listener();
		}
		
		// If we ever need to expand beyond a single GET action, this can/should be generalised and put into the commands class
		if (!empty($_GET['udcentral_action']) && 'login' == $_GET['udcentral_action']) {
			# auth_redirect() does not return, according to the documentation; but the code shows that it can
			# auth_redirect();

			if (!empty($_GET['login_id']) && is_numeric($_GET['login_id']) && !empty($_GET['login_key'])) {
				$login_user = get_user_by('id', $_GET['login_id']);
				if (is_a($login_user, 'WP_User')) {
					// Allow site implementers to disable this functionality
					$allow_autologin = apply_filters('updraftcentral_allow_autologin', true, $login_user);
					if ($allow_autologin) {
						$login_key = get_user_meta($login_user->ID, 'updraftcentral_login_key', true);
						if (is_array($login_key) && !empty($login_key['created']) && $login_key['created'] > time() - 60 && !empty($login_key['key']) && $login_key['key'] == $_GET['login_key']) {
							$autologin = true;
						}
					}
				}
			}
			if (!empty($autologin)) {
				delete_user_meta($login_user->ID, 'updraftcentral_login_key');
				$this->autologin_user($login_user);
			}
		}
		
		add_filter('udrpc_action', array($this, 'udrpc_action'), 10, 5);

	}
	
	// Do verification before calling this method
	private function autologin_user($user, $redirect = true) {
		if (!is_user_logged_in()) {
	// 		$user = get_user_by('id', $user_id);
			if (!is_object($user) || !is_a($user, 'WP_User')) return;
			wp_set_current_user($user->ID, $user->user_login);
			wp_set_auth_cookie($user->ID);
			do_action('wp_login', $user->user_login, $user);
		}
		if ($redirect) {
			wp_safe_redirect(network_admin_url());
			exit;
		}
	}

	
	public function udrpc_action($response, $command, $data, $key_name_indicator, $ud_rpc) {

		if (empty($this->receivers[$key_name_indicator])) return $response;
		$this->initialise_listener_error_handling($key_name_indicator);

		if ('_' == substr($command, 0, 1) || !method_exists($this->commands, $command)) {
			if (defined('UPDRAFTPLUS_UDRPC_FORCE_DEBUG') && UPDRAFTPLUS_UDRPC_FORCE_DEBUG) error_log("Unknown RPC command received: ".$command);
			return $this->return_rpc_message(array('response' => 'rpcerror', 'data' => array('code' => 'unknown_rpc_command', 'data' => $command)));
		}

		$extra_info = isset($this->extra_info[$key_name_indicator]) ? $this->extra_info[$key_name_indicator] : null;
		
		// Make it so that current_user_can() checks can apply + work
		if (!empty($extra_info['user_id'])) wp_set_current_user($extra_info['user_id']);
		
		$this->current_udrpc = $ud_rpc;
		
		// Despatch
		$msg = call_user_func(array($this->commands, $command), $data, $extra_info);
	
		return $this->return_rpc_message($msg);
	}
	
	public function get_current_udrpc() {
		return $this->current_udrpc;
	}
	
	private function initialise_listener_error_handling($hash) {
		$this->ud->error_reporting_stop_when_logged = true;
		set_error_handler(array($this->ud, 'php_error'), E_ALL & ~E_STRICT);
		$this->php_events = array();
		add_action('updraftplus_logline', array($this, 'updraftplus_logline'), 10, 4);
		if (!UpdraftPlus_Options::get_updraft_option('updraft_debug_mode')) return;
// 		$this->ud->nonce = $hash;
// 		$this->ud->logfile_open($hash);
	}
	
	public function updraftplus_logline($line, $nonce, $level, $uniq_id) {
		if ('notice' === $level && 'php_event' === $uniq_id) {
			$this->php_events[] = $line;
		}
	}

	public function return_rpc_message($msg) {
		if (is_array($msg) && isset($msg['response']) && 'error' == $msg['response']) {
			$this->ud->log('Unexpected response code in remote communications: '.serialize($msg));
		}
		if (!empty($this->php_events)) {
			if (!isset($msg['data'])) $msg['data'] = null;
			$msg['data'] = array('php_events' => array(), 'previous_data' => $msg['data']);
			foreach ($this->php_events as $logline) {
				$msg['data']['php_events'][] = $logline;
			}
		}
		restore_error_handler();

		return $msg;
	}
	
}
