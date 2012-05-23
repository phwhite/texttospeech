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

/**** TTS Source Base Class ****/
class _tts_source {
	var $info = array(
				'name'				=> 'name',
				'description'		=> 'description',
				'default'			=> 0,
				'can_be_dynamic'	=> 0,
	   		 );

	var $depend_cmds = array();
	var $depend_files = array();

	var $defaults = array ();
	var $config = array();

	var $is_supported = -1;

	var $destidx = -1;
	var $tts_vars = array();

	function _tts_source() {
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
		return 'src_' . $this->info['name'] . '_' . $ckey;
	}

	function js_func($f) {
		return 'src_' . $this->info['name'] . '_' . $f;
	}

	function process_request($tts_vars, $_req) {
		global $_REQUEST;

		if (!$_req || !is_array($_req)) {
			$_req = $_REQUEST;
		}

		if (!is_array($this->config) || count($this->config) == 0) {
			$this->config = $this->default;
		}

		foreach($this->config as $ckey => $cval) {
			$cfid = $this->config_form_id($ckey);
			if (isset($_req[$cfid])) {
				$this->config[$ckey] = $_req[$cfid];
			}
		}

		if ($this->config['dynamic'] == 1) {
			// Copy destination over
			if (($idx = $this->destidx) > 0) {
				if (isset($_req['goto'.$idx]) && isset($_req[$_req['goto'.$idx].$idx])) {
					$this->config['fail_dest'] = $_req[$_req['goto'.$idx].$idx];
				}
				else {
					$this->config['fail_dest'] = '';
				}
			}
		}
		else {
			$this->config['fail_dest'] = '';
		}

		$this->do_process_request($tts_vars, $_req);
	}
	
	function support_check() {
		global $amp_conf;

		$dcmds = $this->depend_cmds;
		
		foreach($dcmds as $dcmd) {
			$lastone = exec('which ' . $dcmd . ' 2>/dev/null', $iout, $rval);
			if ($rval != 0) {
				$lastone = exec('which '.trim($amp_conf['AMPBIN']).'/'.$dcmd.' 2>/dev/null', $iout, $rval);
			}
		
			if ($rval != 0) {
				// TODO: Add debug here.. No-support cause $dcmd is missing
				return false;
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

	function dynamic_conf_html($label, $fdata) {
		return '<td valign=top>' . $label . '<br><table><tr><td>'
												. $fdata . '</td></tr></table></td>' . "\n";
	}
	
	function config_page($tts_vars) {
		global $tabindex;

		if ($this->is_supported < 1) {
			return false;
		}

		$table = new CI_Table;

		/****  Create dynamic config div output  ****/
		if ($this->config['dynamic'] == 0) {
			$dstyle = 'display: none;';
		}
		else {
			$dstyle = '';
		}
		$dc_div = '<div id="' . $this->config_form_id('dynamic_conf')
													. '" style="' . $dstyle . '">' . "\n";
		$dc_div .= '<smaller><i><table border=0 cellpadding=1 cellspacing=1><tr>' . "\n";

		/****  Dynamic Config: Get engines dynamic config, if it has any  ****/
		$dc_div .= $this->dynamic_config_out($tts_vars);
		
		/****  Dynamic Config: Fail Destination ****/
		$label = fpbx_label(	_('Destination On Fail'),
								_('Destination to goto if dynamic query fails')		);
		$fdest_html = '<table>';
		$fdest_html .= drawselects(empty($this->config['fail_dest']) ?
									  	null : $this->config['fail_dest'], $this->destidx);
		$fdest_html .= '</table>';
		$dc_div .= '<td>' . $label . '<br>' . $fdest_html . '</td>' . "\n";

		$dc_div .= '</tr></table></i></smaller></div>' . "\n";

		/****  Form Field: Dynamic ****/
		if (isset($this->info['can_be_dynamic']) && $this->info['can_be_dynamic'] == 1) {
			$label = fpbx_label(	_('Dynamic'),
									_('If checked, this source will be processed and '
									. 'converted on-the-fly within the dialplan.  This '
									. 'allows the text provided by this source to '
									. 'dynamically change each time it is used.')
							);
			$fdata = array(	'name'		=> $this->config_form_id('dynamic'),
							'id'		=> $this->config_form_id('dynamic'),
							'tabindex'	=> ++$tabindex,
							'value'		=> '1',
							'onChange'	=> 'src_dynamic_changed()'
							);

			if (isset($this->config['dynamic']) && !empty($this->config['dynamic'])) {
				$fdata['checked'] = "checked";
			}

			$label_cell = array(	'data'		=> $label,
									'valign'	=> 'top'		);

			$table->add_row($label_cell, form_checkbox($fdata) . $dc_div);
		}

		return $this->config_page_out($table, $tts_vars);
	}

	function javascript() {
		if ($this->is_supported < 1) {
			return null;
		}

		return $this->javascript_out();
	}

	function get($dest_file, $conf) {
		if ($this->is_supported < 1) {
			return false;
		}

		$sconf = $this->config;
		if (is_array($conf) && !empty($conf)) {
			foreach($conf as $co => $cv) {
				$sconf[$co] = $cv;
			}
		}

		return $this->do_get($dest_file, $sconf);
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
		return;
	}

	function do_process_request($tts_vars, $_req) {
		// Used to process the incoming HTTP request
		return true;
	}


	/*********************************************************************************/
	/*                     THESE MUST BE REPLACED BY REAL ENGINE                     */
	/*********************************************************************************/
	function dynamic_config_out($tts_vars) {
		// Return any variables we may have when configured for dynamic mode only!
		// Use $this->dynamic_conf_html($label, $fdata) to output each variable
		return "";
	}

	function config_page_out($table, $tts_vars) {
		// Output configuration params for config page
		return true;
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

	function do_get($text_file, $conf) {
		// Place text value from configured source into passed text file
		return true;
	}

}

?>
