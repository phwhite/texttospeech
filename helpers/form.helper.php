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
 *
 * ==================================================================
 * 
 * This is just a stub helper included when it is detected that the
 * current FreePBX version does not contain form_helper (i.e. v2.9)
 *
 *******************************************************************/

if ($tts_legacy_fpbx == true) {
	// Copied right from functions.inc.php from FreePBX 2.10
	if (!defined('BASEPATH')){define('BASEPATH', '');}
	if (!function_exists('get_instance')) {
		function get_instance(){return new ci_def();}
	}
	if (!class_exists('ci_def')) {
		class ci_def {function __construct(){$this->lang = new ci_lan_def(); $this->config = new ci_config(); $this->uri = new ci_uri_string();}}
	}
	if (!class_exists('ci_lan_def')) {
		class ci_lan_def {function load(){return false;} function line(){return false;}} 
	}
	if (!class_exists('ci_config')) {
		class ci_config {function __construct(){return false;} function site_url($v){return $v;} function item(){return false;}} 
	}
	if (!class_exists('ci_uri_string')) {
		class ci_uri_string {function  uri_string(){return false;}} 
	}
	if (!function_exists('config_item')) {
		function config_item(){}
	}
	
	require_once(dirname(__FILE__) . '/form_helper.php');
}
