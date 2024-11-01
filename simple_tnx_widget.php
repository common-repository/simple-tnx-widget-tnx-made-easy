<?php
/**
 * simple_tnx_widget.php
 *
 * @package default
 */

/*
Plugin Name: Simple TNX Widget
Plugin URI: http://hamdi.web.id/wp-plugins/simple-tnx-widget.html
Author: Hamdi Azis
Description: Simple TNX Widget automatically add code of <a href="http://www.tnx.net/?p=119600759" rel="nofollow">TNX.NET</a> ads on your blog in the sidebar. Please note that installed plugin will show a link to TNX.net as a test link to see if plugin is worked before actual advertiser put link on your site, after you link has been sold, this link will be dissapeared and changed with real advertiser link. <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9780169"><strong>Buy Me a cup of coffee</strong></a>
Version: 1.2
Author URI: http://hamdi.web.id
*/

/*
Copyright (C) 2009 hamdi.web.id

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

function tnx_widget($args) {
	extract($args);
	echo $before_widget;
	echo $before_title;
	echo get_option('tnx_widget_title');
	echo $after_title;
	echo '<ul>';
	$tnx = new TNX_l($_login = get_option('tnx_widget_login'));
	echo $tnx->show_link(1);
	echo $tnx->show_link(1);
	echo $tnx->show_link(1);
	echo $tnx->show_link();
	echo '</ul>';
	echo $after_widget;
}

function register_tnx_widget() {
	register_sidebar_widget('Simple TNX widget', 'tnx_widget');
	register_widget_control('Simple TNX widget', 'tnx_widget_control' );
}

function tnx_widget_control() {
	if (!empty($_REQUEST['tnx_widget_login'])) {
		update_option('tnx_widget_login', $_REQUEST['tnx_widget_login']);}?>
	Your TNX username:&nbsp;<i><b><?php echo get_option('tnx_widget_login');?></b></i>
    <br /><input type="text" name="tnx_widget_login" /><br /><br />

    <?php echo 'Widget Title:&nbsp;<i><b>'.get_option('tnx_widget_title').'</b></i>';?>

<div id="element" >
    <?php update_option('tnx_widget_title', $_REQUEST['tnx_widget_title']);?>
    <input type="text" name="tnx_widget_title" /><br />
</div>
<?php
}

add_action('init', 'register_tnx_widget');

class TNX_l {
	var $_timeout_connect = 5;
	var $_connect_using = 'curl';
	var $_html_delimiter1 = '<li>';
	var $_html_delimiter2 = '</li>';
	var $_encoding = 'UTF-8';
	var $_exceptions = 'PHPSESSID';
	var $_return_point = 0;
	var $_content = '';
	var $_tnx_check = '<a href="http://www.tnx.net?p=119600759" rel="nofollow" title="TNX">TNX invite code: ffffffffeb742fd3</a>';

	function TNX_l($_login) {
		if($this->_connect_using == 'fsock' AND !function_exists('fsockopen')){echo 'fsock function is disabled on your server, contact your provider or try to use the CURL version of the TNX code.'; return false;}
		if($this->_connect_using == 'curl' AND !function_exists('curl_init')){echo 'Error, CURL is not supported, try using fsock.'; return false;}
        if(!empty($this->_encoding) AND !function_exists("iconv")){echo 'CURL function is disabled on your server, contact your provider or try to use the fsock version of the TNX code.'; return false;}
		if ($_SERVER['REQUEST_URI'] == '') $_SERVER['REQUEST_URI'] = '/';
		if (strlen($_SERVER['REQUEST_URI']) > 180) return false;

		if (!empty($this->_exceptions)) {
			$exceptions = explode(' ', $this->_exceptions);
			for ($i=0; $i<sizeof($exceptions); $i++) {
				if ($_SERVER['REQUEST_URI'] == $exceptions[$i]) return false;
				if ($exceptions[$i] == '/' and preg_match("#^\/index\.\w{1,5}$#", $_SERVER['REQUEST_URI'])) return false;
				if (strpos($_SERVER['REQUEST_URI'], $exceptions[$i]) !== false) return false;
			}
		}

		$this->_login = strtolower($_login); $this->_host = $this->_login . '.tnx.net'; $file = base64_encode($_SERVER['REQUEST_URI']);
		$user_pref = substr($this->_login, 0, 2); $md5 = md5($file); $index = substr($md5, 0, 2);
		$site = str_replace('www.', '', $_SERVER['HTTP_HOST']);
		$this->_path = '/users/' . $user_pref . '/' . $this->_login . '/' . $site. '/' . substr($md5, 0, 1) . '/' . substr($md5, 1, 2) . '/' . $file . '.txt';
		$this->_url = 'http://' . $this->_host . $this->_path;
		$this->_content = $this->get_content();
		if ($this->_content !== false) {
			$this->_content_array = explode('<br>', $this->_content);
			for ($i=0; $i<sizeof($this->_content_array); $i++) {
				$this->_content_array[$i] = trim($this->_content_array[$i]);
			}
		}
	}

	function show_link($num = false) {
		if (!isset($this->_content_array)) return false;
		$links = '';
		if (!isset($this->_content_array_count)) {$this->_content_array_count = sizeof($this->_content_array);}
		if ($this->_return_point >= $this->_content_array_count) return false;

		if ($num === false or $num >= $this->_content_array_count) {
			for ($i = $this->_return_point; $i < $this->_content_array_count; $i++) {
				if (empty($this->_content_array[$i])) $links .= $this->_html_delimiter1 . $this->_tnx_check . $this->_html_delimiter2;
				$links .= $this->_html_delimiter1 . $this->_content_array[$i] . $this->_html_delimiter2;
			}
			$this->_return_point += $this->_content_array_count;
		}
		else {
			if ($this->_return_point + $num > $this->_content_array_count) return false;
			for ($i = $this->_return_point; $i < $num + $this->_return_point; $i++) {
				if (empty($this->_content_array[$i])) $links .= $this->_html_delimiter1 . $this->_tnx_check . $this->_html_delimiter2;
				$links .= $this->_html_delimiter1 . $this->_content_array[$i] . $this->_html_delimiter2;
			}
			$this->_return_point += $num;
		}
		return (!empty($this->_encoding)) ? iconv("windows-1251", $this->_encoding, $links) : $links;
	}

	function get_content() {
		$user_agent = 'TNX_l ip: ' . $_SERVER['REMOTE_ADDR'];
		$page = '';
		if ($this->_connect_using == 'curl' or ($this->_connect_using == '' and function_exists('curl_init'))) {
			$c = curl_init($this->_url);
			curl_setopt($c, CURLOPT_CONNECTTIMEOUT, $this->_timeout_connect);
			curl_setopt($c, CURLOPT_HEADER, false);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($c, CURLOPT_TIMEOUT, $this->_timeout_connect);
			curl_setopt($c, CURLOPT_USERAGENT, $user_agent);
			$page = curl_exec($c);
			if (curl_error($c) or (curl_getinfo($c, CURLINFO_HTTP_CODE) != '200' and curl_getinfo($c, CURLINFO_HTTP_CODE) != '404') or strpos($page, 'fsockopen') !== false) {
				curl_close($c);
				return false;
			}
			curl_close($c);
		}
		elseif ($this->_connect_using == 'fsock') {
			$buff = '';
			$fp = @fsockopen($this->_host, 80, $errno, $errstr, $this->_timeout_connect);
			if ($fp) {
				fputs($fp, "GET " . $this->_path . " HTTP/1.0\r\n");
				fputs($fp, "Host: " . $this->_host . "\r\n");
				fputs($fp, "User-Agent: " . $user_agent . "\r\n");
				fputs($fp, "Connection: Close\r\n\r\n");

				stream_set_blocking($fp, true);
				stream_set_timeout($fp, $this->_timeout_connect);
				$info = stream_get_meta_data($fp);

				while ((!feof($fp)) and (!$info['timed_out'])) {
					$buff .= fgets($fp, 4096);
					$info = stream_get_meta_data($fp);
				}
				fclose($fp);

				if ($info['timed_out']) return false;

				$page = explode("\r\n\r\n", $buff);
				$page = $page[1];
				if ((!preg_match("#^HTTP/1\.\d 200$#", substr($buff, 0, 12)) and !preg_match("#^HTTP/1\.\d 404$#", substr($buff, 0, 12))) or $errno!=0 or strpos($page, 'fsockopen') !== false) return false;
			}
		}
		if (strpos($page, '404 Not Found')) return '';
		return $page;
	}
}
?>