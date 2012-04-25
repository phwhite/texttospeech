#!/usr/bin/env php
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

$test_prefix = "A-test-";
$debug_mode = false; 
$remove_only = false;
$add_only = false;

// Pull in the freepbx system
ob_start();
include_once('/etc/freepbx.conf');
ob_end_clean();

$tts = New texttospeech;

$test_num = 1;

function dump_test($sname, $ename, $dynamic) {
	global	$tts;

	$_req = Array();

	if ($dynamic == true) {
		$pfx = "Dynamic ";
	}
	else {
		$pfx = "";
	}
	if (!$ename) {
		$source = $sname;
		$engine = 'swift';
		$title = "BEGIN TEST FOR: " . $pfx . "source [$source], engine [$engine]";
		$desc = $pfx . "$source-$engine defaults";
	}
	else if (!$sname) {
		$engine = $ename;
		$source = 'text';
		$title = "BEGIN TEST FOR:$pfx engine [$engine], source [$source]";
		$desc = $pfx . "$engine-$source defaults";
	}
	else {
		return;
	}

	$_req['source'] = $source;
	$_req['engine'] = $engine;
	$tts_vars = texttospeech_process_request($_req);

	if ($source == 'text') {
		$tts->source->config['text'] = 'test 1 2 3 4 5.';
	}

	if ($dynamic == true) {
		if (!$ename) {
			$tts->source->config['dynamic'] = "1";
			$tts->source->config['fail_dest'] = "app-blackhole,congestion,1";
		}
		else {
			$tts->engine->config['dynamic'] = "1";
			$tts->engine->config['fail_dest'] = "app-blackhole,congestion,1";
		}
	}

	echo "/****************************************************************\n";
	echo " * " . sprintf("%-60.60s", $title) . " *\n";
	echo " ****************************************************************/\n";
	echo '$tts_vars =		Array(		';
	$flg = 0;
	foreach($tts_vars as $vn => $vv) {
		if (strlen($vn) > 6) {
			$tmp = substr($vn, 0, 6);
		}
		else {
			$tmp = $vn;
		}
		switch($tmp) {

		case "engine":
		case "source":
		case "name":
		case "id":
			$skip = true;
			break;

		default:
			$skip = false;
			break;

		}

		if ($skip) {
			continue;
		}
			
		$a = floor(strlen($vn) / 4);
		$tabs = 5 - 1 - $a;
		if ($flg == 0) {
			$flg = 1;
		}
		else {
			echo "\t\t\t\t\t\t\t";
		}
		echo "$vn";
		while ($tabs > 0) {
			echo "\t";
			$tabs--;
		}
		echo "=> '$vv',\n";
	}
	echo "\t\t\t\t\t);\n";

	echo '$src_conf =		Array(		';
	$flg = 0;
	foreach($tts->source->config as $vn => $vv) {
		$a = floor(strlen($vn) / 4);
		$tabs = 5 - 1 - $a;
		if ($flg == 0) {
			$flg = 1;
		}
		else {
			echo "\t\t\t\t\t\t\t";
		}
		echo "$vn";
		while ($tabs > 0) {
			echo "\t";
			$tabs--;
		}
		echo "=> '$vv',\n";
	}
	echo "\t\t\t\t\t);\n";

	echo '$eng_conf =		Array(		';
	$flg = 0;
	foreach($tts->engine->config as $vn => $vv) {
		$a = floor(strlen($vn) / 4);
		$tabs = 5 - 1 - $a;
		if ($flg == 0) {
			$flg = 1;
		}
		else {
			echo "\t\t\t\t\t\t\t";
		}
		echo "$vn";
		while ($tabs > 0) {
			echo "\t";
			$tabs--;
		}
		echo "=> '$vv',\n";
	}
	echo "\t\t\t\t\t);\n";

	echo 'create_test("' . $desc . '", "'
								. $tts_vars['source'] . '", $src_conf, "'
								. $tts_vars['engine'] . '", $eng_conf, $tts_vars);' . "\n";

	echo "\n\n";
}

function dump() {
	global	$tts;

	echo '<?php' . "\n\n";

	foreach($tts->sources as $sname => $sinfo) {
		dump_test($sname, null, false);
		if ($sinfo['can_be_dynamic'] == 1) {
			dump_test($sname, null, true);
		}
	}

	echo "\n\n";

	foreach($tts->engines as $ename => $einfo) {
		dump_test(null, $ename, false);
		if ($einfo['can_be_dynamic'] == 1) {
			dump_test(null, $ename, true);
		}
	}

	echo "\n";
}

function create_warn($msg) {
	echo "              *-*-* Warnning: $msg\n";
}

function create_skip($errmsg, $extra = null, $critical = false) {
	echo "              !*!*! Skipping: $errmsg\n";
	if ($extra) {
		echo "              $extra\n";
	}
	echo "\n";

	if ($critical) {
		exit(1);
	}
}

function create_test($desc, $source, $src_conf, $engine, $eng_conf, $opts) {
	global $tts;
	global $test_num;
	global $debug_mode;
	global $test_prefix;

	$warn_msg = '';
	$test_name = $test_prefix . sprintf("%02d", $test_num);
	echo "-> $test_name: desc [$desc]\n";
	echo "               src [$source]\n";
	echo "               eng [$engine]\n";

	if ($tts->set_source($source) !== true) {
		create_skip("Source not found");
		return;
	}
	else if ($tts->source->is_supported != 1) {
		create_skip("Source not supported");
		return;
	}
	if ($tts->source->set_config($src_conf) !== true) {
		create_skip("FAILED to set source config");
		return;
	}


	if ($tts->set_engine($engine) !== true) {
		create_skip("Engine not found");
		return;
	}
	else if ($tts->engine->is_supported != 1) {
		create_skip("Engine not supported");
		return;
	}
	if ($tts->engine->set_config($eng_conf) !== true) {
		create_skip("FAILED to set engine config");
		return;
	}

	$src_conf_str = '';
	foreach($src_conf as $vn => $vv) {
		if (strlen($src_conf_str) > 0) {
			$src_conf_str .= ", ";
		}
		$src_conf_str .= "$vn='$vv'";
	}

	$eng_conf_str = '';
	foreach($eng_conf as $vn => $vv) {
		if (strlen($eng_conf_str) > 0) {
			$eng_conf_str .= ", ";
		}
		$eng_conf_str .= "$vn='$vv'";
	}

	$tts_vars = $opts;
	$tts_vars['name'] = $test_name;
	$tts_vars['source'] = $source;
	$tts_vars['source_conf'] = $tts->get_source_config();
	$tts_vars['engine'] = $engine;
	$tts_vars['engine_conf'] = $tts->get_engine_config();

	if (!$debug_mode) {
		ob_start();
		$ret = texttospeech_add_entry($tts_vars);
		$output = ob_get_contents();
		ob_end_clean();
		if ($ret !== true) {
			$warn_msg = "Entry added but conversion failed";
		}
	}

	echo "          src_conf [" . $src_conf_str . "]\n";
	echo "          eng_conf [" . $eng_conf_str . "]\n";

	if (!empty($warn_msg)) {
		create_warn($warn_msg);
	}

	echo "\n";

	$test_num++;
	return;
}

function remove_test_entries() {
	global $test_prefix;
	global $debug_mode;

	echo "Looking for and deleting old test entries....\n";
	
	$plen = strlen($test_prefix);
	foreach(texttospeech_list() as $ent) {
		if (substr($ent['name'], 0, $plen) == $test_prefix) {
			echo "   -> Found " . $ent['name'] . " as ID " . $ent['id'] . ", removing...";
			if (!$debug_mode) {
				if (texttospeech_remove_entry($ent['id']) !== true) {
					echo "FAILED\n";
					exit(1);
				}
			}
			echo "done\n";
		}
	}
}


while (count($argv) > 1) {
	if (count($argv) > 1) {
		switch($argv[1]) {
	
		case "--dump":
			dump();
			exit(0);
	
		case "--debug":
			$debug_mode = true;
			break;

		case "--add-only":
			$add_only = true;
			break;

		case "--remove-only":
			$remove_only = true;
			break;

		default:
			exit(1);
	
		}

		array_shift($argv);
	}
}

if (!$add_only) {
	remove_test_entries();
	if ($remove_only) {
		exit(0);
	}
}

echo "\nCreating test entries....\n";

include_once(dirname(__FILE__) . "/create-tests.inc.php");

exit(0);
	
?>
