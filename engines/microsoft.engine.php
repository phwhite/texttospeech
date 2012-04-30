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

require_once(dirname(__FILE__) . "/engine.php");

/**** Microsoft TTS Engine Class ****/
class _tts_engine_microsoft extends _tts_engine {
	var $info = array(
				'name'		=> 'microsoft',
				'description'	=> 'Microsoft Translation'
	    		  );

	var $languages = array(
				'ca'		=> 'ca',
				'ca-es'		=> 'ca-es',
				'da'		=> 'da',
				'da-dk'		=> 'da-dk',
				'de'		=> 'de',
				'de-de'		=> 'de-de',
				'en'		=> 'en',
				'en-au'		=> 'en-au',
				'en-ca'		=> 'en-ca',
				'en-gb'		=> 'en-gb',
				'en-in'		=> 'en-in',
				'en-us'		=> 'en-us',
				'es'		=> 'es',
				'es-es'		=> 'es-es',
				'es-mx'		=> 'es-mx',
				'fi'		=> 'fi',
				'fi-fi'		=> 'fi-fi',
				'fr'		=> 'fr',
				'fr-ca'		=> 'fr-ca',
				'fr-fr'		=> 'fr-fr',
				'it'		=> 'it',
				'it-it'		=> 'it-it',
				'ja'		=> 'ja',
				'ja-jp'		=> 'ja-jp',
				'ko'		=> 'ko',
				'ko-kr'		=> 'ko-kr',
				'nb-no'		=> 'nb-no',
				'nl'		=> 'nl',
				'nl-nl'		=> 'nl-nl',
				'no'		=> 'no',
				'pl'		=> 'pl',
				'pl-pl'		=> 'pl-pl',
				'pt'		=> 'pt',
				'pt-br'		=> 'pt-br',
				'pt-pt'		=> 'pt-pt',
				'ru'		=> 'ru',
				'ru-ru'		=> 'ru-ru',
				'sv'		=> 'sv',
				'sv-se'		=> 'sv-se',
				'zh-chs'	=> 'zh-chs',
				'zh-cht'	=> 'zh-cht',
				'zh-cn'		=> 'zh-cn',
				'zh-hk'		=> 'zh-hk',
				'zh-tw'		=> 'zh-tw'
				);

	var $engine_cmd = "ms-tts";

	var $depend_cmds = array('mpg123', 'sox');
	var $depend_files = array();

	var $defaults = array (
				'language'			=> 'en',
				"client_id"			=> '',
				'cilent_secret'		=> '',
			      );

	var $mstts_conf_file = "/etc/asterisk/microsoft-tts.conf";

	function setup_defaults() {
		if (file_exists($this->mstts_conf_file)) {
			$mstts_conf_str = file_get_contents($this->mstts_conf_file);
			$mstts_conf = explode(":", $mstts_conf_str);
			$this->defaults['client_id'] = $mstts_conf[0];
			$this->defaults['client_secret'] = $mstts_conf[1];
		}

		return true;
	}

	function config_page_out($table, $tts_vars) {
		global $tabindex;
	
		/****  Form Field: Language ***/
		$label = fpbx_label(	_('Language'),
								_('The language in which the text should be spoken.')
			);
	
		$fappend = 'tabindex=' . ++$tabindex;
		$table->add_row($label, form_dropdown($this->config_form_id('language'),
										$this->languages,
										$this->config['language'],
										$fappend));
	
		/****  Form Field: Client ID ***/
		$label = fpbx_label(	_('Client ID'),
								_('This engine makes use of the Microsoft Translator text-to-speech API and supports several different languages.<br>&nbsp;<br>NOTICE: A Client ID and Secret is required to use the MS Translator TTS API.  Their basic subscription up to 2 million characters a month is free.  First, you must subscribe on Microsofts Azure Marketplace (https://datamarket.azure.com/dataset/1899a118-d202-492c-aa16-ba21c33c06cb), and then you must register your apllication (https://datamarket.azure.com/developer/applications/) where you will choose your unique Client ID, and Client Secret.')
			);
		$fdata = array(	'name'		=> $this->config_form_id('client_id'),
						'tabindex'	=> ++$tabindex,
						'size	'	=> '55',
						'maxlength'	=> '55',
						'value'		=> $this->config['client_id']
					);
		$table->add_row($label, form_input($fdata));

		/****  Form Field: Client Secret ***/
		$label = fpbx_label(	_('Client Secret'),
								_('This engine makes use of the Microsoft Translator text-to-speech API and supports several different languages.<br>&nbsp;<br>NOTICE: A Client ID and Secret is required to use the MS Translator TTS API.  Their basic subscription up to 2 million characters a month is free.  First, you must subscribe on Microsofts Azure Marketplace (https://datamarket.azure.com/dataset/1899a118-d202-492c-aa16-ba21c33c06cb), and then you must register your apllication (https://datamarket.azure.com/developer/applications/) where you will choose your unique Client ID, and Client Secret.')
			);
		$fdata = array(	'name'		=> $this->config_form_id('client_secret'),
						'tabindex'	=> ++$tabindex,
						'size	'	=> '60',
						'maxlength'	=> '60',
						'value'		=> $this->config['client_secret']
					);
		$ahtml = form_input($fdata);

		$fdata = array(	'name'		=> $this->config_form_id('client_default'),
						'tabindex'	=> ++$tabindex,
						'value'		=> '1'
					);
		$ahtml .= '&nbsp;&nbsp;' . form_checkbox($fdata) . 'Make Default';
		$table->add_row($label, $ahtml);
	
		return $table->generate() . $table->clear() . "\n";
	}

	function javascript_out() {

?>
<script language="javascript">
<!--

function <?php echo $this->js_func('submit_check') ?>(frm) {
	<?php echo tts_js_var(Array(
		'msgNoClientID' 	=>
		_('No Client ID was provided.  This is required to access Microsofts Text-To-Speech API.'),

		'msgNoClientSecret' 	=>
		_('No Client Secret was provided.  This is required to access Microsofts Text-To-Speech API.'),
	)); ?>

	defaultEmptyOK=false;
	if (isEmpty(frm.<?php echo $this->config_form_id('client_id')?>.value))
		return warnInvalid(frm.<?php echo $this->config_form_id('client_id')?>, msgNoClientID);
	if (isEmpty(frm.<?php echo $this->config_form_id('client_secret')?>.value))
		return warnInvalid(frm.<?php echo $this->config_form_id('client_secret')?>, msgNoClientSecret);

	return true;
}

-->
</script>
<?php

	} // end of javascript_out()

	function do_process_request($tts_vars, $_req) {
		$client_id_var = $this->config_form_id('client_id');
		$client_secret_var = $this->config_form_id('client_secret');
		$client_def_var = $this->config_form_id('client_default');

		if ($_req[$client_def_var] == '1') {
			if (isset($_req[$client_id_var]) && isset($_req[$client_secret_var])) {
				$mstts_conf_str = $_req[$client_id_var] . ':' . $_req[$client_secret_var];
				if (file_exists($this->mstts_conf_file)) {
					unlink($this->mstts_conf_file);
				}

				file_put_contents($this->mstts_conf_file, $mstts_conf_str);
			}
		}

		return;
	}
	
	function do_convert($textfile, $outfile, $conf) {
		global $tts_debug;

		if ($this->is_supported < 1) {
			return false;
		}

		$lang = isset($conf['language']) ? $conf['language'] : $this->defaults['language'];
		$client_id = isset($conf['client_id']) ? $conf['client_id'] : $this->defaults['client_id'];
		$client_secret = isset($conf['client_secret']) ? $conf['client_secret'] : $this->defaults['client_secret'];
	
		$client_info = $client_id . ':' . $client_secret;	

		$command = $this->engine_cmdpath . " -o " . escapeshellarg( $outfile ) . " -r 8000 -l " . $lang . " -f " . escapeshellarg( $textfile ) . " -c " . escapeshellarg( $client_info );
	
		exec($command, $iout, $rval);
		if ($rval == 0) {
			$tts_debug->notice("Conversion Command Succeeded");
			$tts_debug->verbose_dump("cmd", $command);
			return true;
		}
		$tts_debug->error("Conversion Command Failed");
		$tts_debug->error_dump("cmd", $command);
		$tts_debug->error_dump("Output", $iout);
		return false;
	}
}

?>
