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
 * debug class: Simple debugging library
 *
 * Written By: Paul White <pwhite@hiddenmatrix.org>
 *
 *******************************************************************/

class debug {
	var $level_names = Array( 'Verbose', 'Notice', 'Warning', 'ERROR' );

	var $config = Array(	'file'		=> null,
							'level'		=> 0,
							'prefix'	=> "",
							'suffix'	=> "\n"		);
	var $logging = false;

	function debug($log_file = null, $log_level = null, $log_prefix = null, $log_suffix = null) {
		return $this->set($log_file, $log_level, $log_prefix, $log_suffix);
	}

	function set($log_file = null, $log_level = null, $log_prefix = null, $log_suffix = null) {
		if (!isset($log_file)) {
			$this->logging = false;
			return true;
		}

		if (is_array($log_file)) {
			foreach($log_file as $cn => $cv) {
				$this->config[$cn] = $cv;
			}

			$this->logging = true;
			return true;
		}

		$this->config['file'] = $log_file;
		if (isset($log_level)) {
			$this->config['level'] = $log_level;
		}
		if (isset($log_prefix)) {
			$this->config['prefix'] = $log_prefix;
		}
		if (isset($log_suffix)) {
			$this->config['suffix'] = $log_suffix;
		}

		$this->logging = true;
		return true;
	}

	function set_prefix($prefix) {
		$this->config['prefix'] = $prefix;
		return true;
	}

	function enabled() {
		return $this->logging;
	}


	function text($txt, $lvl = 0, $dump_varname = null, $dump_var = null) {
		if ($this->logging == false || $lvl < $this->config['level']) {
			return false;
		}

		$my_prefix = str_replace("%l", $lvl, $this->config['prefix']);
		$my_prefix = str_replace("%L", $this->level_names[$lvl], $my_prefix);

		$dbg_text = $my_prefix . $txt . $this->config['suffix'];
		
		if (empty($this->config['file'])) {
			echo $dbg_text;
		}
		else {
			file_put_contents($this->config['file'], $dbg_text, FILE_APPEND);
		}

		if (isset($dump_varname) && isset($dump_var)) {
			return $this->dump($dump_varname, $dump_var, $lvl);
		}

		return true;
	}
	
	function dump($varname, $var, $lvl = 0) {
		if ($this->logging == false || $lvl < $this->config['level']) {
			return false;
		}
	
		ob_start();
		var_dump($var);
		$output = ob_get_contents();
		ob_end_clean();
	
		$outlines = explode("\n", $output);
		$this->text('Vardump... ' . $varname . ' = ', $lvl);
	
		foreach ($outlines as $oline) {
			$this->text('   ' . $oline, $lvl);
		}
	
		return true;
	}

	function verbose($txt, $dump_varname = null, $dump_var = null) {
		return $this->text($txt, 0, $dump_varname, $dump_var);
	}
	function verbose_dump($varname, $var) {
		return $this->dump($varname, $var, 0);
	}

	function notice($txt, $dump_varname = null, $dump_var = null) {
		return $this->text($txt, 1, $dump_varname, $dump_var);
	}
	function notice_dump($varname, $var) {
		return $this->dump($varname, $var, 1);
	}

	function warning($txt, $dump_varname = null, $dump_var = null) {
		return $this->text($txt, 2, $dump_varname, $dump_var);
	}
	function warning_dump($varname, $var) {
		return $this->dump($varname, $var, 2);
	}

	function error($txt, $dump_varname = null, $dump_var = null) {
		return $this->text($txt, 3, $dump_varname, $dump_var);
	}
	function error_dump($varname, $var) {
		return $this->dump($varname, $var, 3);
	}
}
?>
