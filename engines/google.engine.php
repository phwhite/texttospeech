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

/**** Google TTS Engine Class ****/
class _tts_engine_google extends _tts_engine {
	var $info = array(
				'name'			=> 'google',
				'description'	=> 'Google Translation',
				'default'		=> 1,
	    		  );

	var $languages = array(
				'af' 		=> 'Afrikaans',
				'sq' 		=> 'Albanian',
				'am' 		=> 'Amharic',
				'ar' 		=> 'Arabic',
				'hy' 		=> 'Armenian',
				'az' 		=> 'Azerbaijani',
				'eu' 		=> 'Basque',
				'be' 		=> 'Belarusian',
				'bn' 		=> 'Bengali',
				'bh' 		=> 'Bihari',
				'bs' 		=> 'Bosnian',
				'br' 		=> 'Breton',
				'bg' 		=> 'Bulgarian',
				'km' 		=> 'Cambodian',
				'ca' 		=> 'Catalan',
				'zh-CN' 	=> 'Chinese (Simplified)',
				'zh-TW' 	=> 'Chinese (Traditional)',
				'co' 		=> 'Corsican',
				'hr' 		=> 'Croatian',
				'cs' 		=> 'Czech',
				'da' 		=> 'Danish',
				'nl' 		=> 'Dutch',
				'en' 		=> 'English',
				'eo' 		=> 'Esperanto',
				'et' 		=> 'Estonian',
				'fo' 		=> 'Faroese',
				'tl' 		=> 'Filipino',
				'fi' 		=> 'Finnish',
				'fr' 		=> 'French',
				'fy' 		=> 'Frisian',
				'gl' 		=> 'Galician',
				'ka' 		=> 'Georgian',
				'de' 		=> 'German',
				'el' 		=> 'Greek',
				'gn' 		=> 'Guarani',
				'gu' 		=> 'Gujarati',
				'xx-hacker' 	=> 'Hacker',
				'ha' 		=> 'Hausa',
				'iw' 		=> 'Hebrew',
				'hi' 		=> 'Hindi',
				'hu' 		=> 'Hungarian',
				'is' 		=> 'Icelandic',
				'id' 		=> 'Indonesian',
				'ia' 		=> 'Interlingua',
				'ga' 		=> 'Irish',
				'it' 		=> 'Italian',
				'ja' 		=> 'Japanese',
				'jw' 		=> 'Javanese',
				'kn' 		=> 'Kannada',
				'kk' 		=> 'Kazakh',
				'rw' 		=> 'Kinyarwanda',
				'rn' 		=> 'Kirundi',
				'xx-klingon' 	=> 'Klingon',
				'ko' 		=> 'Korean',
				'ku' 		=> 'Kurdish',
				'ky' 		=> 'Kyrgyz',
				'lo' 		=> 'Laothian',
				'la' 		=> 'Latin',
				'lv' 		=> 'Latvian',
				'ln' 		=> 'Lingala',
				'lt' 		=> 'Lithuanian',
				'mk' 		=> 'Macedonian',
				'mg' 		=> 'Malagasy',
				'ms' 		=> 'Malay',
				'ml' 		=> 'Malayalam',
				'mt' 		=> 'Maltese',
				'mi' 		=> 'Maori',
				'mr' 		=> 'Marathi',
				'mo' 		=> 'Moldavian',
				'mn' 		=> 'Mongolian',
				'sr-ME' 	=> 'Montenegrin',
				'ne' 		=> 'Nepali',
				'no' 		=> 'Norwegian',
				'nn' 		=> 'Norwegian (Nynorsk)',
				'oc' 		=> 'Occitan',
				'or' 		=> 'Oriya',
				'om' 		=> 'Oromo',
				'ps' 		=> 'Pashto',
				'fa' 		=> 'Persian',
				'xx-pirate' 	=> 'Pirate',
				'pl' 		=> 'Polish',
				'pt' 		=> 'Portuguese',
				'pt-BR' 	=> 'Portuguese (Brazil)',
				'pt-PT' 	=> 'Portuguese (Portugal)',
				'pa' 		=> 'Punjabi',
				'qu' 		=> 'Quechua',
				'ro' 		=> 'Romanian',
				'rm' 		=> 'Romansh',
				'ru' 		=> 'Russian',
				'gd' 		=> 'Scots Gaelic',
				'sr' 		=> 'Serbian',
				'sh' 		=> 'Serbo-Croatian',
				'st' 		=> 'Sesotho',
				'sn' 		=> 'Shona',
				'sd' 		=> 'Sindhi',
				'si' 		=> 'Sinhalese',
				'sk' 		=> 'Slovak',
				'sl' 		=> 'Slovenian',
				'so' 		=> 'Somali',
				'es' 		=> 'Spanish',
				'su' 		=> 'Sundanese',
				'sw' 		=> 'Swahili',
				'sv' 		=> 'Swedish',
				'tg' 		=> 'Tajik',
				'ta' 		=> 'Tamil',
				'tt' 		=> 'Tatar',
				'te' 		=> 'Telugu',
				'th' 		=> 'Thai',
				'ti' 		=> 'Tigrinya',
				'to' 		=> 'Tonga',
				'tr' 		=> 'Turkish',
				'tk' 		=> 'Turkmen',
				'tw' 		=> 'Twi',
				'ug' 		=> 'Uighur',
				'uk' 		=> 'Ukrainian',
				'ur' 		=> 'Urdu',
				'uz' 		=> 'Uzbek',
				'vi' 		=> 'Vietnamese',
				'cy' 		=> 'Welsh',
				'xh' 		=> 'Xhosa',
				'yi' 		=> 'Yiddish',
				'yo' 		=> 'Yoruba',
				'zu' 		=> 'Zulu'
				);

	var $engine_cmd = "google-tts";

	var $depend_cmds = array('mpg123', 'sox');
	var $depend_files = array();

	var $defaults = array (
				'language'	=> 'en',
				'speed'		=> '1.2'
			      );

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
	
		/****  Form Field: Speed ***/
		$label = fpbx_label(	_('Speed Factor'),
								_('Output Voice Speed Factor.')
			);
		$fdata = array(	'name'		=> $this->config_form_id('speed'),
						'tabindex'	=> ++$tabindex,
						'size	'	=> '5',
						'maxlength'	=> '5',
						'value'		=> $this->config['speed']
			);
		$table->add_row($label, form_input($fdata) . "x");
	
		return $table->generate() . $table->clear() . "\n";
	}

	function javascript_out() {

?>
<script language="javascript">
<!--

function <?php echo $this->js_func('submit_check') ?>(frm) {
	<?php echo tts_js_var(Array(
		'msgNoSpeed' 	=>
		_('No Speed factor was specified.  This is a required filed.  An example value would be: 1.2'),
	)); ?>

	defaultEmptyOK=false;
	if (isEmpty(frm.<?php echo $this->config_form_id('speed')?>.value))
		return warnInvalid(frm.<?php echo $this->config_form_id('speed')?>, msgNoSpeed);

	return true;
}

-->
</script>
<?php

	} // end of javascript_out()
	
	function do_convert($textfile, $outfile, $conf) {
		global $tts_debug;

		$lang = isset($conf['language']) ? $conf['language'] : $this->defaults['language'];
		$speed = isset($conf['speed']) ? $conf['speed'] : $this->defaults['speed'];
	
		$command = $this->engine_cmdpath . " -r 8000 -o " . escapeshellarg( $outfile ) . " -l " . $lang . " -s " . $speed . " -f " . escapeshellarg( $textfile );
	
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
