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

$sources = array();

foreach (glob(dirname(__FILE__) . "/../sources/*.source.php") as $sfile) {
	$source = substr(basename($sfile), 0, -11);

	echo "src \"$source\".....\"$sfile\"\n";
	include_once($sfile);

	$sname = "_tts_source_" . $source;
	if (class_exists($sname)) {
		$src = New $sname;
		$sinfo = $src->get_info();
		if ($src->supported()) {
			$sinfo['supported'] = 1;
		}
		else {
			$sinfo['supported'] = 0;
		}
		$sinfo['ptr'] = $src;

		$sources[$source] = $sinfo;
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
	print_r($sources);
	echo "\n";
	exit(0);
}

foreach ($sources as $sname => $sinfo) {
	if ($match && $sname != $match) {
		continue;
	}

	if ($verbose) {
		print_r($sinfo);
		echo "\n";
		exit(0);
	}
	echo "Source [" . $sname . " - " . $sinfo['description'] . "], Supported [";
	if ($sinfo['supported'] == 1) {
		echo "true";
	}
	else {
		echo "false";
	}
	echo "], Can be dynamic [";
	if (isset($sinfo['can_be_dynamic']) && $sinfo['can_be_dynamic'] == 1) {
		echo "true";
	}
	else {
		echo "false";
	}
	echo "]\n";
}


?>
