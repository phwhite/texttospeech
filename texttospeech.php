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
 * texttospeech class: Provides dynamic engine support, and an easy
 * way to covert text from anywhere
 *
 * Written By: Paul White <pwhite@hiddenmatrix.org>
 *
 *******************************************************************/

class texttospeech {
	// Load our engines
	var $engines = array();
	var	$sources = array();

	var $engine = null;
	var $source = null;

	function texttospeech() {
		if ($this->load_engines() == 0) {
			return false;
		}

		if ($this->load_sources() == 0) {
			return false;
		}

		return true;
	}

	function default_engine() {
		foreach ($this->engines as $ename => $einfo) {
			if ($einfo['supported'] == 1 && isset($einfo['default']) &&
																$einfo['default'] == 1) {
				return $ename;
			}
		}
		return null;
	}

	function default_source() {
		foreach ($this->sources as $sname => $sinfo) {
			if ($sinfo['supported'] == 1 && isset($sinfo['default']) &&
																$sinfo['default'] == 1) {
				return $sname;
			}
		}
		return null;
	}

	function load_engines() {
		$this->engines = array();
	
		$cnt = 0;
		foreach (glob(dirname(__FILE__) . "/engines/*.engine.php") as $efile) {
			$engine = substr(basename($efile), 0, -11);
	
			include_once($efile);
	
			$ename = "_tts_engine_" . $engine;
			if (class_exists($ename)) {
				$eng = New $ename;
				$einfo = $eng->get_info();
				if ($eng->supported()) {
					$einfo['supported'] = 1;
					$cnt++;
				}
				else {
					$einfo['supported'] = 0;
				}
				$einfo['ptr'] = $eng;
		
				$this->engines[$engine] = $einfo;
			}
		}
	
		return $cnt;
	}
	
	function find_engine($engine) {
		$einfo = $this->engines[$engine];
		if (is_array($einfo) && $einfo['supported'] == 1) {
			return $einfo['ptr'];
		}
	
		return null;
	}

	function set_engine($engine) {
		$this->engine = $this->find_engine($engine);
		if (!$this->engine) {
			return false;
		}

		return true;
	}

	function get_engine() {
		return $this->engine;
	}

	function set_engine_config($confstr, $engine = null) {
		if ($engine) {
			$eng = $this->find_engine($engine);
		}
		else {
			$eng = $this->engine;
		}

		if (!$eng) { 
			return false;
		}

		if (empty($confstr)) {
			$conf = array();
		}
		else {
			$conf = unserialize($confstr);
		}

		return $eng->set_config($conf);
	}

	function get_engine_config($engine = null) {
		if ($engine) {
			$eng = $this->find_engine($engine);
		}
		else {
			$eng = $this->engine;
		}

		if (!$eng) { 
			return false;
		}

		$conf = $eng->get_config();
		$confstr = serialize($conf);

		return $confstr;
	}

	function load_sources() {
		global $destidx;

		$this->sources = array();
		$cnt = 0;
		foreach (glob(dirname(__FILE__) . "/sources/*.source.php") as $sfile) {
			$source = substr(basename($sfile), 0, -11);
	
			include_once($sfile);
	
			$sname = "_tts_source_" . $source;
			if (class_exists($sname)) {
				$src = New $sname;
				$sinfo = $src->get_info();
				if ($src->supported()) {
					$sinfo['supported'] = 1;
					$cnt++;

					if (isset($src->info['can_be_dynamic']) &&
													$src->info['can_be_dynamic'] == 1) {
						$src->destidx = $destidx++;
					}
				}
				else {
					$sinfo['supported'] = 0;
				}
				$sinfo['ptr'] = $src;
		
				$this->sources[$source] = $sinfo;
			}
		}
	
		return $cnt;
	}
	
	function find_source($source) {
		$sinfo = $this->sources[$source];
		if (is_array($sinfo) && $sinfo['supported'] == 1) {
			return $sinfo['ptr'];
		}
	
		return null;
	}

	function set_source($source) {
		$this->source = $this->find_source($source);
		if (!$this->source) {
			return false;
		}

		return true;
	}

	function get_source() {
		return $this->source;
	}

	function set_source_config($confstr, $source = null) {
		if ($source) {
			$src = $this->find_source($source);
		}
		else {
			$src = $this->source;
		}

		if (!$src) { 
			return false;
		}

		if (empty($confstr)) {
			$conf = array();
		}
		else {
			$conf = unserialize($confstr);
		}

		return $src->set_config($conf);
	}

	function get_source_config($source = null) {
		if ($source) {
			$src = $this->find_source($source);
		}
		else {
			$src = $this->source;
		}

		if (!$src) { 
			return false;
		}

		$conf = $src->get_config();
		$confstr = serialize($conf);

		return $confstr;
	}

	function set_tts_vars($tts_vars) {
		if ($this->engine) {
			$this->engine->tts_vars = $tts_vars;
		}
		if ($this->source) {
			$this->source->tts_vars = $tts_vars;
		}
	}

	function process_request($tts_vars, $_req = null, $engine = null, $source = null) {
		if ($engine) {
			$eng = $this->find_engine($engine);
		}
		else {
			$eng = $this->engine;
		}

		if ($source) {
			$src = $this->find_source($source);
		}
		else {
			$src = $this->source;
		}

		if (!$eng || !$src) { 
			return false;
		}

		if ($src->process_request($tts_vars, $_req) === false) {
			return false;
		}

		return $eng->process_request($tts_vars, $_req);
	}

	function generate($text_file, $dest_file, $engine = null,
									$eng_conf = null, $source = null, $src_conf = null) {
		if ($engine) {
			$eng = $this->find_engine($engine);
		}
		else {
			$eng = $this->engine;
		}

		if ($source) {
			$src = $this->find_source($source);
		}
		else {
			$src = $this->source;
		}

		if (!$eng || !$src) { 
			return false;
		}

		if ($src->is_dynamic()) {
			return true;
		}

		if ($eng_conf) {
			$econf = unserialize($eng_conf);
		}
		else {
			$econf = $eng->get_config();
		}

		if ($src_conf) {
			$sconf = unserialize($src_conf);
		}
		else {
			$sconf = $src->get_config();
		}

		if (!$src->get($text_file, $sconf)) {
			return false;
		}

		if ($eng->is_dynamic()) {
			return true;
		}

		return $eng->convert($text_file, $dest_file, $econf);
	}
}
