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

foreach($tts->engines as $ename => $einfo) {
	if ($einfo['supported'] == 1) {
		$eng = $einfo['ptr'];

		/***************************  ENGINE CONFIG  *************************/
		if ($ename != $tts_vars['engine']) {
			$dstyle = 'display: none;';
		}
		else {
			$dstyle = '';
		}
		echo '<div id="texttospeech_eng_' . $ename . '" style="' . $dstyle . '">' . "\n";
		echo heading($einfo['description'] . ' ' . _('Settings')
											. '<hr class="texttospeech-hr">', 4);
		echo $eng->config_page($tts_vars);
		/************************  END OF ENGINE SETTINGS  **********************/
		echo '</div>' . "\n\n";
	}
}

?>
