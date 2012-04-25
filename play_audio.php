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

if (!defined('FREEPBX_IS_AUTH')) {
	die('No direct script access allowed');
}

// Play an audio file
if (!isset($_REQUEST['audio_file'])) {
	die('No auto_file provided');
}
$crypt_file = $_REQUEST['audio_file'];

if (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != '') {
	$play_key = trim($amp_conf['AMPPLAYKEY']);
}
else {
	$play_key = 'MaryTheWindCries';
}


include_once(dirname(__FILE__) . "/helpers/crypt.helper.php");
$crypt = new Crypt();
$audio_file = $crypt->decrypt($crypt_file, $play_key);

$audio_name = basename($audio_file);
$audio_size = filesize($audio_file);
$audio_type = strtolower(substr(strrchr($audio_name, "."), 1));

switch ($audio_type) {

case "wav":
case "sln":
	$ctype = "x-wav";
	break;

case "ulaw":
	$ctype = "x-basic";
	break;

case "alaw":
	$ctype = "x-alaw-basic";
	break;

case "gsm":
	$ctype = "x-gsm";
	break;

case "g729":
	$ctype = "x-g729";
	break;

default:
	die("Cannot Play That Audio File Format");
	break;

}

$f = fopen($audio_file, "rb");
if (!$f) {
	die("Could not open that audio file");
}

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: audio file");
header("Content-Type: audio/" . $ctype);
header("Content-Disposition: attachment; filename=" . $audio_name);
header("Content-Transfer-Encoding: binary");
header("Content-length: " . $audio_size);

fpassthru($f);

?>
