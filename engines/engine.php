<?php

/********************************************************************
 *
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
 * _tts_engine class: Engine base class which implements a common
 * engine interface, and includes code common between multiple
 * engines.  The TTS engine modules use this as their base class.
 *
 * Written-By: Paul White <pwhite@hiddenmatrix.org>
 *
 *******************************************************************/

/**** TTS Engine Base Class ****/
class _tts_engine {
	var $info = array(
				'name'				=> 'name',
				'description'		=> 'description',
				'default'			=> 0,
				'can_be_dynamic'	=> 0,
	    		  );

	var $engine_cmd = "";

	var $depend_cmds = array('mpg123', 'sox');
	var $depend_files = array();

	var $defaults = array ();

	var $config = array();

	var $engine_cmdpath;

	var $is_supported = -1;

	var	$tts_vars = array();

	function _tts_engine() {
		$this->setup_defaults();
		$this->config = $this->defaults;

		$this->is_supported = $this->support_check();
		return $this->is_supported;
	}

	function get_info() {
		return $this->info;
	}

	function get_defaults() {
		return $this->defaults;
	}

	function get_config() {
		return $this->config;
	}

	function set_config($newconf) {
		$this->config = $this->defaults;

		foreach ($newconf as $co => $cv) {
			$this->config[$co] = $cv;
		}
		return true;
	}

	function is_dynamic() {
		if ($this->info['can_be_dynamic'] == 1) {
			if (isset($this->config['dynamic']) && !empty($this->config['dynamic'])) {
				return true;
			}
		}

		return false;
	}

	function config_form_id($ckey) {
		return 'eng_' . $this->info['name'] . '_' . $ckey;
	}

	function js_func($f) {
		return 'eng_' . $this->info['name'] . '_' . $f;
	}

	function process_request($tts_vars, $_req) {
		global $_REQUEST;

		if (!$_req || !is_array($_req)) {
			$_req = $_REQUEST;
		}

		if (!is_array($this->config) || count($this->config) == 0) {
			$this->config = $this->defaults;
		}

		foreach($this->config as $ckey => $cval) {
			$cfid = $this->config_form_id($ckey);
			if (isset($_req[$cfid])) {
				$this->config[$ckey] = $_req[$cfid];
			}
		}

		$this->do_process_request($tts_vars, $_req);
	}
	
	function support_check() {
		global $amp_conf;

		$dcmds = $this->depend_cmds;
		array_push($dcmds, $this->engine_cmd);
		
		foreach($dcmds as $dcmd) {
			$lastone = exec("which $dcmd 2>/dev/null", $iout, $rval);
			if ($rval != 0 && isset($amp_conf['AMPBIN']) && !empty($amp_conf['AMPBIN'])) {
				$lastone = exec("which ".trim($amp_conf['AMPBIN'])."/$dcmd 2>/dev/null", $iout, $rval);
			}
		
			if ($rval != 0) {
				// TODO: Add debug here.. No-support cause $dcmd is missing
				return false;
			}

			if ($dcmd == $this->engine_cmd) {
				$this->engine_cmdpath = $lastone;
			}
		}
	
		return $this->initialize();
	}
	
	function supported() {
		if ($this->is_supported < 0) {
			$this->is_supported = $this->support_check();
		}

		return $this->is_supported;
	}
	
	function config_page($tts_vars) {
		global $tabindex;

		if ($this->is_supported < 1) {
			return false;
		}

		$table = new CI_Table;

		/****  Form Field: Dynamic ****/
		if (isset($this->info['can_be_dynamic']) && $this->info['can_be_dynamic'] == 1) {
			$label = fpbx_label(	_('Dynamic'),
									_('If checked, the conversion will take place '
									. 'on-the-fly within the dialplan.  NOTE: This option '
									. 'is disabled when playback control is allowed.')
							);
			$fdata = array(	'name'		=> $this->config_form_id('dynamic'),
							'id'		=> $this->config_form_id('dynamic'),
							'tabindex'	=> ++$tabindex,
							'value'		=> '1',
							'onChange'	=> 'eng_dynamic_changed()'
							);

			if (!empty($tts_vars['allow_control'])){ 
				$fdata['disabled'] = "disabled";
			}
			else {
				if (isset($this->config['dynamic']) && !empty($this->config['dynamic'])) {
					$fdata['checked'] = "checked";
				}
			}
			$table->add_row($label, form_checkbox($fdata));
		}

		return $this->config_page_out($table, $tts_vars);
	}

	function javascript() {
		if ($this->is_supported < 1) {
			return null;
		}

		return $this->javascript_out();
	}
	
	function convert($textfile, $outfile, $conf) {
		if ($this->is_supported < 1) {
			return false;
		}

		$econf = $this->config;
		foreach($conf as $co => $cv) {
			$econf[$co] = $cv;
		}

		return $this->do_convert($textfile, $outfile, $econf);
	}

	function agi_convert($agi, $text_file, $allow_skip, $nconf = null) {
		if ($this->is_supported < 1) {
			return false;
		}

		if ($this->info['can_be_dynamic'] != 1) {
			$agi->verbose('TTS Dynamic: ERROR - agi_convert() called for engine [' . $this->info['name'] . '], and dynamic is not supported!');
			return false;
		}

		if ($allow_skip) {
			$askstr = "true";
		}
		else {
			$askstr = "false";
		}

		$agi->verbose('TTS Dynamic: Eng [' . $this->info['name'] . '] agi_convert(skip = ' . $askstr . ')');

		$econf = $this->config;
		if ($nconf) {
			foreach($nconf as $co => $cv) {
				$econf[$co] = $cv;
			}
		}

		return $this->do_agi_convert($agi, $text_file, $allow_skip, $econf);
	}


	/*********************************************************************************/
	/*                      THESE MAY BE REPLACED BY REAL ENGINE                     */
	/*********************************************************************************/
	function initialize() {
		// Any code needed to "initialize" the engine.  Returning false will disable
		// support for the engine, otherwise return true.
		return true;
	}

	function setup_defaults() {
		// Used to setup default config/etc
		return true;
	}

	function do_process_request($tts_vars, $_req) {
		// Used to process the incoming HTTP request
		return;
	}

	function do_agi_convert($agi, $textfile, $allow_skip, $conf) {
		// Engines with dynamic support must replace this function.  This
		// function is used to perform AGI script calls to output the converted
		// text from $textfile to the user.  The $agi handle is passed along as
		// a baseline, and will be an AGI class object from the /agi-bin/phpagi.php
		// file.

		return false;
	}


	/*********************************************************************************/
	/*                     THESE MUST BE REPLACED BY REAL ENGINE                     */
	/*********************************************************************************/
	function config_page_out($table) {
		// Output configuration params for config page
		return false;
	}

	function javascript_out() {
		// Output javascript.  Must at least output submit check func

?>
<script language="javascript">
<!--

function <?php echo $this->js_func('submit_check') ?>(frm) {
	return true;
}

-->
</script>
<?php

	} // end of javascript_out()

	function do_convert($textfile, $outfile, $conf) {
		// Convert text from textfile to outfile using params in config array
		return false;
	}

}

?>
