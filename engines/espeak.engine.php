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

/**** eSpeak TTS Engine Class ****/
class _tts_engine_espeak extends _tts_engine {
	var $info = array(
				'name'		=> 'espeak',
				'description'	=> 'eSpeak'
	    		  );

	var $voices = array();

	var $engine_cmd = "espeak";

	var $depend_cmds = array("sox");
	var $depend_files = array();

	var $defaults = array (
				'voice'		=> 'default',
				'arguments'	=> ''
			      );
	
	function initialize() {
		$vlist = array();
		exec($this->engine_cmdpath . ' --voices | tail -n +2', $vlist, $rval);
		if ($rval != 0 || empty($vlist)) {
			return false;
		}

		$this->voices = array();
		foreach($vlist as $vent) {
			$vlang = rtrim(substr($vent, 4, 15));
			$vgend = rtrim(substr($vent, 19, 1));
			$vname = rtrim(substr($vent, 22, 18));

			switch($vgend) {

			case "M":
				$vdesc = "Male, ";
				break;

			case "F":
				$vdesc = "Female, ";
				break;

			default:
				$vdesc = "";
				break;

			}
			$vdesc .= $vlang;

			$this->voices[$vname] = "$vname ($vdesc)";
		}

		return true;
	}

	function config_page_out($table, $tts_vars) {
		global $tabindex;

		/****  Form Field: Voice ***/
		$label = fpbx_label(	_('Voice'),
					_('The eSpeak voice file to use when synthisizing the text.')
			);
	
		$fappend = 'tabindex=' . ++$tabindex;
		$table->add_row($label, form_dropdown($this->config_form_id('voice'),
										$this->voices,
										$this->config['voice'],
										$fappend));

		/****  Form Field: Arguments ***/
		$label = fpbx_label(	_('Arguments'),
					_('Additional arguments that will be passed to the engine during the conversion process.')
			);
		$fdata = array(	'name'		=> $this->config_form_id('arguments'),
						'tabindex'	=> ++$tabindex,
						'size	'	=> '40',
						'maxlength'	=> '40',
						'value'		=> $this->config['arguments']
					);
		$table->add_row($label, form_input($fdata));
	
		return $table->generate() . $table->clear() . "\n";
	}
	
	function do_convert($textfile, $outfile, $conf) {
		global $tts_debug;

		$voice = isset($conf['voice']) ? $conf['voice'] : $this->defaults['voice'];
		$args = isset($conf['arguments']) ? $conf['arguments'] : $this->defaults['arguments'];

		if (!empty($voice)) {
			$vopt="-v $voice";
		}
		else {
			$vopt="";
		}
	
		$command = $this->engine_cmdpath . ' ' . escapeshellcmd($args) . ' -f ' . escapeshellarg($textfile) . ' ' . $vopt . ' --stdout | sox -q -t wav - -r 8000 ' . escapeshellarg($outfile);
		exec('(' . $command . ' >/dev/null 2>&1) 2>&1', $iout, $rval);
	
		if ($rval == 0) {
			if (!empty($iout)) {
				$tts_debug->error("Conversion Command Failed");
				$tts_debug->error_dump("cmd", $command);
				$tts_debug->error_dump("Output", $iout);
				return false;
			}
			$tts_debug->notice("Conversion Command Succeeded");
			$tts_debug->verbose_dump("cmd", $command);
			return true;
		}
		$tts_debug->error("Conversion Command Failed");
		$tts_debug->error_dump("cmd", $command);
		return false;
	}
}

?>
