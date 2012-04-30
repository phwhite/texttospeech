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

$label = fpbx_label(empty($tts_vars['id']) ? _("<i>Add Text To Speech</i>") :
					sprintf(_("<b>Text To Speech:</b> <i>%s</i>"), $tts_vars['name']), '');

$usage_html = '';
if (!empty($tts_vars['id'])) {
	$used_by = framework_display_destination_usage(texttospeech_getdest($tts_vars['id']));
	if (!empty($used_by)) {
		$usage_text = isset($used_by['text']) ? $used_by['text'] : '';
		$usage_tooltip = isset($used_by['tooltip']) ? $used_by['tooltip'] : '';
		$usage_html = '[<a href="#" class="info">' . $usage_text
										. '<span>' . $usage_tooltip . '</span></a>]';
	}
}

echo '<table border=0 cellpadding=0 cellspacing=0 width=75%>';

echo '<tr>';
echo '<td>' . heading($label, 3) . '</td>';
echo '<td valign=bottom align=right>' . heading($usage_html, 5) . '</td>';
echo '</tr>';

echo '<tr><td align=left width=300px>';
if (!empty($tts_vars['id'])) {
	$soundfile = texttospeech_sound_file($tts_vars['name']);
	if (!file_exists($soundfile)) {
	   if (!$tts->source->is_dynamic() && !$tts->engine->is_dynamic()) {
			echo '<table bgcolor="#FF5555" width=230px border=1'
								. ' cellpadding=0 cellspacing=0><tr><td align=middle>';
			echo '<h2><font color=black><b>Error</b>:<br><i>Conversion failed</i></font></h2>';
			echo '</td></tr></table>';
	   }
	}
	else {
		echo '<table bgcolor="#F5F5F5" width=230px border=1'
							. ' cellpadding=0 cellspacing=0><tr><td align=middle>';
		$crypt = new Crypt();


		if (isset($amp_conf['AMPPLAYKEY']) && trim($amp_conf['AMPPLAYKEY']) != '') {
			$play_key = trim($amp_conf['AMPPLAYKEY']);
		}
		else {
			$play_key = 'MaryTheWindCries';
		}

		$file = urlencode($crypt->encrypt($soundfile,$play_key));
		if ($tts->source->is_dynamic()) {
			echo "<font size=-2><b>Play Last Dynamically Generated File:</b></font><br>";
		}
		else {
			echo "<font size=-2><b>Play Last Generated Sound File:</b></font><br>";
		}
		echo("<embed width='95%' type='audio/basic' src='"
				. $_SERVER['PHP_SELF']
				. "?skip_astman=1&quietmode=1&handler=file&module="
				. $display . "&file=play_audio.html.php&audio_file=" . $file
				. "' width=300, height=25 class=#F5F5F5 autoplay=false"
	    		. " loop=false></embed>");
		echo '</td></tr></table>';
	}
}
echo '</td><td align=right valign=middle>';
echo '</td></tr>';

echo '</table><br>';

?>
