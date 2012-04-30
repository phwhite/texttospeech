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
class _tts_source_cmd extends _tts_source {
	var $info = array(	'name'				=> 'cmd',
						'description'		=> 'Command',
						'can_be_dynamic'	=> 1	);

	var $depend_cmds = array();
	var $depend_files = array();

	var $defaults = array(	'cmd'			=> '',
							'dynamic'		=> 0,
							'fail_dest'		=> ''	);

	function config_page_out($table, $tts_vars) {
		global $tabindex;

		$myhtml = '';

		/****  Form Field: Command ****/
		$label = fpbx_label(	_('Command'),
								_('Command to execute, including any arguments')
						);
		$fdata = array(	'name'		=> $this->config_form_id('cmd'),
						'tabindex'	=> ++$tabindex,
						'size	'	=> '65',
						'maxlength'	=> '255',
						'value'		=> $this->config['cmd']
			);
		$table->add_row($label, form_input($fdata));

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

	function javascript_out() {

?>
<script language="javascript">
<!--

function <?php echo $this->js_func('submit_check') ?>(frm) {
	<?php echo tts_js_var(Array(
		'msgNoCmd' 	=>
		_('No command was specified.  This is a required filed.'),
	)); ?>

	defaultEmptyOK=false;
	if (isEmpty(frm.<?php echo $this->config_form_id('cmd')?>.value))
		return warnInvalid(frm.<?php echo $this->config_form_id('cmd')?>, msgNoCmd);

	return true;
}

-->
</script>
<?php

	} // end of javascript_out()

	function do_get($text_file, $conf) {
		global $tts_debug;

		if (!isset($conf['cmd']) || empty($conf['cmd'])) {
			return false;
		}

		$lines = Array();
		exec(escapeshellcmd($conf['cmd']), $lines, $ret);
		if ($ret != 0) {
			$tts_debug->error("Failed to get text");
			$tts_debug->error_dump("cmd", $conf['cmd']);
			$tts_debug->error_dump("Output", $lines);
			return false;
		}
		$tts_debug->notice("Command completed successfully");
		$tts_debug->verbose_dump("cmd", $conf['cmd']);
		$tts_debug->verbose_dump("Output", $lines);

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
