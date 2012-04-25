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
 * sox class: Quick way to convert audio files
 *
 * Written By: Paul White <pwhite@hiddenmatrix.org>
 *
 *******************************************************************/

class sox {
	var		$sox = null;

	function sox() {
		$last = exec("which sox", $tmpout, $ret);
		if ($ret != 0) {
			// No sox binary found!
			return false;
		}

		$this->sox = $last;
	}

	function convert($input, $outfile, $opts) {
		if (!$this->sox) {
			return false;
		}

		$soxcmd = $this->sox . ' -q';

		foreach($opts as $on => $ov) {
			switch($on) {

			case 't':
			case 'T':
			case 'Type':
				$soxcmd .= ' -t ' . $ov;
				break;

			case 'r':
			case 'R':
			case 'Rate':
				$soxcmd .= ' -r ' . $ov;
				break;

			case 'c':
			case 'C':
			case 'Chans':
			case 'Channels':
				$soxcmd .= ' -c ' . $ov;
				break;

			case 'v':
			case 'V':
			case 'Volume':
				$soxcmd .= ' -v ' . $ov;
				break;

			case 'b':
			case 'B':
			case 'Bits':
				$soxcmd .= ' -b ' . $ov;
				break;

			case 'Combine':
				$ctype = strtolower($ov);
				switch($ctype) {

				case 'concatenate':
				case 'sequence':
				case 'mix':
				case 'merge':
					break;

				default:
					return false;

				}

				$soxcmd .= ' --combine ' . $ctype;
				break;

			default:
				return false;

			}
		}

		if (is_array($input)) {
			if (count($input) == 0) {
				return false;
			}

			foreach($input as $ifile) {
				$soxcmd .= ' "' . $ifile . '"';
			}
		}
		else {
			if (empty($input)) {
				return false;
			}

			$soxcmd .= ' "' . $input . '"';
		}

		$soxcmd .= ' ' . $outfile;

		echo 'SOX Cmd: "' . $soxcmd . '"' . "\n";
		return true;
	}
}


?>
