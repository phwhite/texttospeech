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

/**** Festival Flite TTS Engine Class ****/
class _tts_engine_flite extends _tts_engine {
	var $info = array(
				'name'		=> 'flite',
				'description'	=> 'Festival Flite'
	    		  );

	var $engine_cmd = "flite";

	var $depend_cmds = array();
	var $depend_files = array();

	var $defaults = array (
				'arguments'	=> ''
			      );

	function config_page_out($table, $tts_vars) {
		global $tabindex;

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

		$args = isset($conf['arguments']) ? $conf['arguments'] : $this->defaults['arguments'];

		$command = $this->engine_cmdpath . ' ' . escapeshellcmd($args) . ' -f ' . escapeshellarg($textfile) . ' -o ' . escapeshellarg($outfile);
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
