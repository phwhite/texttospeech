#!/usr/bin/env php
<?php
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.

/***** TESTING ONLY *****/

$engines = array();

foreach (glob(dirname(__FILE__) . "/../engines/*.engine.php") as $efile) {
	$engine = substr(basename($efile), 0, -11);

	echo "eng \"$engine\".....\"$efile\"\n";
	include_once($efile);

	$ename = "_tts_engine_" . $engine;
	if (class_exists($ename)) {
		$eng = New $ename;
		$einfo = $eng->get_info();
		if ($eng->supported()) {
			$einfo['supported'] = 1;
		}
		else {
			$einfo['supported'] = 0;
		}
		$einfo['ptr'] = $eng;

		$engines[$engine] = $einfo;
	}
}

echo "\n\n";

if (count($argv) > 1 && $argv[1] == '-v') {
	$verbose = true;
	array_shift($argv);
}
else {
	$verbose = false;
}

if (count($argv) > 1) {
	$match = $argv[1];
	array_shift($argv);
}
else {
	$match = null;
}

if ($verbose == true && !$match) {
	print_r($engines);
	echo "\n";
	exit(0);
}

foreach ($engines as $ename => $einfo) {
	if ($match && $ename != $match) {
		continue;
	}

	if ($verbose) {
		print_r($einfo);
		echo "\n";
		exit(0);
	}
	echo "Engine [" . $ename . " - " . $einfo['description'] . "], Supported [";
	if ($einfo['supported'] == 1) {
		echo "true";
	}
	else {
		echo "false";
	}
	echo "], Can be dynamic [";
	if (isset($einfo['can_be_dynamic']) && $einfo['can_be_dynamic'] == 1) {
		echo "true";
	}
	else {
		echo "false";
	}
	echo "]\n";
}


?>
