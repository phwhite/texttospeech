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
class _tts_source_file extends _tts_source {
	var $info = array(	'name'				=> 'file',
						'description'		=> 'File',
						'can_be_dynamic'	=> 1	);

	var $depend_cmds = array();
	var $depend_files = array();

	var $defaults = array(	'file'		=> '',
							'dynamic'	=> 0	);

	function config_page_out($table, $tts_vars) {
		global $tabindex;

		/****  Form Field: Name ****/
		$label = fpbx_label(	_('Filename'),
								_('Enter the filename of a text file that should be used. '
								. 'If this entry is dynamic, then the file will be read '
								. 'when the generated dialplan context is called.')
			);
		$fdata = array(	'name'		=> $this->config_form_id('file'),
						'tabindex'	=> ++$tabindex,
						'size	'	=> '65',
						'maxlength'	=> '255',
						'value'		=> $this->config['file']
			);
		$table->add_row($label, form_input($fdata));

		return  $table->generate() . $table->clear() . "\n";
	}

	function javascript_out() {

?>
<script language="javascript">
<!--

function <?php echo $this->js_func('submit_check') ?>(frm) {
	<?php echo tts_js_var(Array(
		'msgNoFile' 	=>
		_('No filename was specified.  This is a required field.'),
	)); ?>

	defaultEmptyOK=false;
	if (isEmpty(frm.<?php echo $this->config_form_id('file')?>.value))
		return warnInvalid(frm.<?php echo $this->config_form_id('file')?>, msgNoFile);

	return true;
}

-->
</script>
<?php

	} // end of javascript_out()

	function do_get($text_file, $conf) {
		global $tts_debug;

		if (!isset($conf['file']) || empty($conf['file'])) {
			return false;
		}

		if (!file_exists($conf['file'])) {
			$tts_debug->error("Failed to get text, file does not exist");
			$tts_debug->error_dump("file", $conf['file']);
			return false;
		}

		$tts_debug->notice("Retreived text from file sucessfully");
		$tts_debug->verbose_dump("file", $conf['file']);

		$text = file_get_contents($conf['file']);
		if (file_put_contents($text_file, $text)) {
			return true;
		}

		return false;
	}
}
