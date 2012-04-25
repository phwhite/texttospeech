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
class _tts_source_text extends _tts_source {
	var $info = array(	'name'				=> 'text',
						'description'		=> 'Text',
						'default'			=> 1,
						'can_be_dynamic'	=> 0	);

	var $depend_cmds = array();
	var $depend_files = array();

	var $defaults = array(	'text'		=> ''	);

	function config_page_out($table, $tts_vars) {
		global $tabindex;

		/****  Form Field: Text ****/
		$label = fpbx_label(	_('Text'),
								_('Enter the text you want to synthetize.  If your voice '
								. 'engine (e.g. swift) supports Speech Synthesis Markup '
								. 'Language (SSML) you can also use markup tags such as '
								. 'phoneme, voice, emphasis, break, prosody, etc. to '
								. 'change the sound of the voice itself.  You can even '
								. 'enter full SSML XML documents into this field.  Learn '
								. 'about SSML at W3C.org and Cepstral.com.')
					);

		$fdata = array(	'name'		=> $this->config_form_id('text'),
						'tabindex'	=> ++$tabindex,
						'rows'		=> '10',
						'cols'		=> '50',
						'value'		=> $this->config['text']
					);
		$table->add_row($label, form_textarea($fdata));

		return  $table->generate() . $table->clear() . "\n";
	}

	function javascript_out() {

?>
<script language="javascript">
<!--

function <?php echo $this->js_func('submit_check') ?>(frm) {
	<?php echo tts_js_var(Array(
		'msgNoText' 	=>
		_('No text was specified.  This is a required field.'),
	)); ?>

	defaultEmptyOK=false;
	if (isEmpty(frm.<?php echo $this->config_form_id('text')?>.value))
		return warnInvalid(frm.<?php echo $this->config_form_id('text')?>, msgNoText);

	return true;
}

-->
</script>
<?php

	} // end of javascript_out()

	function do_get($text_file, $conf) {
		if (!isset($conf['text']) || empty($conf['text'])) {
			return true;
		}

		if (file_put_contents($text_file, $conf['text'])) {
			return true;
		}

		return false;
	}
}
