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

/**** Cepstral Swift TTS Engine Class ****/
class _tts_engine_swift extends _tts_engine {
	var $info = array(
				'name'				=> 'swift',
				'description'		=> 'Cepstral Swift',
				'can_be_dynamic'	=> 0,
	    		  );

	var $voices = array();

	var $engine_cmd = "swift";

	var $depend_cmds = array();
	var $depend_files = array();

	var $defaults = array (
				'voice'		=> '',
				'arguments'	=> '',
			      );

	function initialize() {
		// Get list of voices
		$vlist = array();
		exec($this->engine_cmdpath . ' --voices | egrep "Hz$" | cut -d\' \' -f 1', $vlist, $rval);
		if ($rval != 0 || empty($vlist)) {
			return false;
		}
		$this->voices = array();
		foreach($vlist as $vname) {
			if (substr($vname, -1, 1) == '|') {
				$vname = substr($vname, 0, -1);
			}
			$this->voices[$vname] = $vname;
		}

		return true;
	}

	function setup_defaults() {
		global $asterisk_conf;

		// See if we have a default voice configured in swift.conf
		$swift_conf = $asterisk_conf['astetcdir'] . '/swift.conf';
		if (file_exists($swift_conf)) {
			$swconf = explode("\n", file_get_contents($swift_conf));
			foreach ($swconf as $cline) {
				if (substr($cline, 0, 6) == "voice=") {
					$this->defaults['voice'] = substr($cline, 6);
					break;
				}
			}
		}

		/*** Check to see if app_swift is available for dynamic support ***/
		// Find the asterisk command first
		$lastone = exec("which asterisk 2>/dev/null", $iout, $rval);
		if ($rval != 0 && isset($amp_conf['AMPBIN']) && !empty($amp_conf['AMPBIN'])) {
			$lastone = exec("which ".trim($amp_conf['AMPBIN'])."/asterisk 2>/dev/null", $iout, $rval);
		}
	
		if ($rval == 0) {
			$astcmd = $lastone;

			$last = exec($astcmd . ' -rx "core show application swift" | egrep "not registered"', $eoutput, $ret);

			if ($ret == 0) {
				// Try to load it
				exec($astcmd . ' -rx "module load app_swift"', $eoutput, $ret);
	
				// Check again
				$last = exec($astcmd . ' -rx "core show application swift" | egrep "not registered"', $eoutput, $ret);
			}

			if ($ret == 1) {
				// App was found, enable dynamic support...
				$this->info['can_be_dynamic'] = 1;
				$this->defaults['dynamic'] = '';
			}
		}
	}

	function config_page_out($table, $tts_vars) {
		global $tabindex;
	
		/****  Form Field: Voice ***/
		$label = fpbx_label(	_('Voice'),
								_('The SWIFT voice to use when synthisizing the text.')
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
			$vopt="-n $voice";
		}
		else {
			$vopt="";
		}
	
		$command = $this->engine_cmdpath . " " . $vopt . " -p audio/channels=1,audio/sampling-rate=8000 " . escapeshellcmd($args) . " -f " . escapeshellarg($textfile) . " -o " . escapeshellarg($outfile);
	
		exec($command, $iout, $rval);
		if ($rval == 0) {
			$output = implode(" ", $iout);
			if (strpos($output, "Usage:") !== FALSE) {
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
		$tts_debug->error_dump("Output", $iout);
		return false;
	}

	function _agi_swift($agi, $text, $voice = null, $tmout = 0, $maxdigits = 0, &$dtmf = null) {
		global $tts_debug;

		$swift_args = '"';
		if ($voice && !empty($voice)) {
			$swift_args .= $voice . '^';
		}
		$swift_args .= $text . '"';
	
		if ($tmout > 0 && $maxdigits > 0) {
			$swift_args .= '|' . $tmout . '|' . $maxdigits;
		}
	
		$ret = $agi->exec('swift', $swift_args);
		if ($ret['result'] != 0) {
			// Something must've happened
			$tts_debug->error("AGI Conversion Failed");
			$tts_debug->error_dump("swift_args", $swift_args);
			return(-1);
		}
	
		$ret = $agi->get_variable("SWIFT_DTMF");
		if ($ret['result'] == 1) {
			if ($dtmf) {
				$dtmf = $ret['data'];
			}
			$tts_debug->notice("AGI Conversion Succeeded, Got DTMF Digit $dtmf");
			$tts_debug->verbose_dump("swift_args", $swift_args);
			return 1;
		}

		$tts_debug->notice("AGI Conversion Succeeded");
		$tts_debug->verbose_dump("swift_args", $swift_args);
		return 0;
	}

	function do_agi_convert($agi, $textfile, $allow_skip, $conf) {
		global $tts_debug;

		// Get the voice we should use from our config
		$voice = isset($conf['voice']) ? $conf['voice'] : $this->defaults['voice'];

		// Make sure we have a text file
		if (!file_exists($textfile)) {
			// Uhoh, what happened??
			$agi->verbose("TTS Swift Dynamic: Unable to open textfile [" . $textfile . "]");
			return false;
		}

		// Read in the contents of the text file
		$text = file_get_contents($textfile);
		if (empty($text)) {
			// ???
			$agi->verbose("TTS Swift Dynamic: No text found inside textfile!");
			return false;
		}

		// Separate the text into lines
		$lines = explode("\n", $text);

		// Now use the Swift AGI command to output all of our lines
		foreach ($lines as $line) {
			if ($allow_skip) {
				if (($ret = $this->_agi_swift($agi, $line, $voice, 1, 1)) < 0) {
					// Hangup maybe?
					$agi->verbose("TTS Swift Dynamic: Swift() app failed (" . $ret . ")");
					return false;
				}
				if ($ret > 0) {
					// Key was pressed, playback aborted/skipped
					break;
				}
			}
			else {
				$this->_agi_swift($agi, $line, $voice);
			}
		}

		// All done with our output
		return true;
	}
}

?>
