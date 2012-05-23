<?php
/********************************************************************
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * ==================================================================
 *
 * FreePBX Module: texttospeech
 *     Maintainer: Paul White <pwhite@hiddenmatrix.org>
 *******************************************************************/

require_once(dirname(__FILE__) . "/source.php");

/**** Text TTS Source Class ****/
class _tts_source_url extends _tts_source {
	var $info = array(	'name'				=> 'url',
						'description'		=> 'URL',
						'can_be_dynamic'	=> 1	);

	var $depend_cmds = array();
	var $depend_files = array();

	var $defaults = array(	'url'			=> '',
							'dynamic'		=> 0,
							'strip_tags'	=> 1,
							'timeout'		=> 5,
							'fail_dest'		=> ''	);

	function config_page_out($table, $tts_vars) {
		global $tabindex;

		$myhtml = '';

		/****  Form Field: Name ****/
		$label = fpbx_label(	_('URL'),
								_('A URL address whose contents should be used as the '
								. 'source text for conversion.')
						);
		$fdata = array(	'name'		=> $this->config_form_id('url'),
						'tabindex'	=> ++$tabindex,
						'size	'	=> '65',
						'maxlength'	=> '255',
						'value'		=> $this->config['url']
			);
		$table->add_row($label, form_input($fdata));

		$label = fpbx_label(	_('Strip Tags'),
								_('If checked, Any tags (html, php, etc) received from the configured URL will '
								. 'be stripped from the output.  This should be checked for any URLs given that '
								. 'return web (HTML) pages, unless the URL returns SSML formatted output.')
						);
		$fdata = array(	'name'		=> $this->config_form_id('strip_tags'),
						'tabindex'	=> ++$tabindex,
						'value'		=> '1',
						);

		if (isset($this->config['strip_tags']) && !empty($this->config['strip_tags'])) {
			$fdata['checked'] = "checked";
		}
		$table->add_row($label, form_checkbox($fdata));

		$myhtml .= $table->generate() . $table->clear() . "\n\n";

		if (isset($this->tts_vars['name']) && !empty($this->tts_vars['name'])) {
			$text_file = texttospeech_text_file($this->tts_vars['name']);
			if (file_exists($text_file)) {
				$myhtml .= '<table cellpadding=0 cellspacing=0><tr>';
				$myhtml .= '<td width=120></td><td>';
				$myhtml .= '<table border=1 bgcolor="e0e0e0" cellpadding=10 cellspacing=1><tr><td>';
				$myhtml .= '<b><i><u>Text returned last query:</u></i></b>';
				$myhtml .= '<pre>' . htmlSpecialChars(file_get_contents($text_file)) . '</pre>';
				$myhtml .= '</td></tr></table></td></tr></table>';
			}
		}

		return $myhtml;
	}

	function dynamic_config_out($tts_vars) {
		$dc_html = '';

		/****  Dynamic config: timeout ****/
		$label = fpbx_label(	_('Timeout'),
								_('Set the network timeout in seconds.  Fetching the '
								. 'configured URL will be aborted after this timeout.')
						);
		$fdata = array(	'name'		=> $this->config_form_id('timeout'),
						'tabindex'	=> ++$tabindex,
						'size	'	=> '2',
						'maxlength'	=> '2',
						'value'		=> $this->config['timeout']
					  );

		$dc_html .= $this->dynamic_conf_html($label, form_input($fdata));

		return $dc_html;
	}

	function javascript_out() {

?>
<script language="javascript">
<!--

function <?php echo $this->js_func('submit_check') ?>(frm) {
	<?php echo tts_js_var(Array(
		'msgNoURL' 	=>
		_('No URL was specified.  This is a required filed.'),
		'msgInvalidURL' 	=>
		_('The URL you entered is not a valid http, https, or ftp URL.  Please try again.'),
		'msgInvalidTimeout' 	=>
		_('Invalid timeout specified.'),
	)); ?>

	defaultEmptyOK=false;
	if (isEmpty(frm.<?php echo $this->config_form_id('url')?>.value))
		return warnInvalid(frm.<?php echo $this->config_form_id('url')?>, msgNoURL);

	if (!tts_isurl(frm.<?php echo $this->config_form_id('url')?>.value))
		return warnInvalid(frm.<?php echo $this->config_form_id('url')?>, msgInvalidURL);

	if (frm.<?php echo $this->config_form_id('dynamic')?>.checked)
		if (!tts_isnum(frm.<?php echo $this->config_form_id('timeout')?>.value))
			return warnInvalid(frm.<?php echo $this->config_form_id('timeout')?>, msgInvalidTimeout);

	return true;
}

-->
</script>
<?php

	} // end of javascript_out()

	function do_get($text_file, $conf) {
		global $tts_debug;

		if (!isset($conf['url']) || empty($conf['url'])) {
			$tts_debug->error("Failed to get text from URL: No URL configured");
			return false;
		}

		if (($pos = strpos($conf['url'], ':')) === false) {
			$tts_debug->error("Failed to get text from URL: Bad URL configured");
			$tts_debug->error_dump("url", $conf['url']);
			return false;
		}
		if (substr($conf['url'], $pos, 3) != '://') {
			$tts_debug->error("Failed to get text from URL: Bad URL configured (no <stream>:// found");
			$tts_debug->error_dump("url", $conf['url']);
			return false;
		}

		$proto = substr($conf['url'], 0, $pos);
		$wrappers = stream_get_wrappers();
		if (!in_array($proto, $wrappers)) {
			$tts_debug->error("Failed to get text from URL: No stream wrapper for '" . $proto . "' was found");
			$tts_debug->error_dump("url", $conf['url']);
			$tts_debug->error_dump("wrappers", $wrappers);
			return false;
		}

		$ctx_opts = array(	'http' => array(	'method'	=> "GET",
												'timeout'	=> $conf['timeout']
										   )
						 );
		$ctx = stream_context_create($ctx_opts);
		if (($fp = fopen($conf['url'], 'r', false, $ctx)) == null) {
			$tts_debug->error("Failed to get text from URL");
			$tts_debug->error_dump("url", $conf['url']);
			return false;
		}

		$tts_debug->notice("Text retreived from URL successfully");
		$tts_debug->verbose_dump("url", $conf['url']);

		$text = stream_get_contents($fp);
		fclose($fp);

		if (!empty($conf['strip_tags'])) {
			$text = strip_tags($text);
		}

		$lines = explode("\n", $text);
		$text = '';
		foreach($lines as $line) {
			$trimmed = trim($line);
			if (strlen($trimmed) > 0) {
				$text .= $trimmed . "\n";
			}
		}

		if (!file_put_contents($text_file, $text)) {
			return false;
		}

		return true;
	}
}
