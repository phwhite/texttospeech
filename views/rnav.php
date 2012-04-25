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

// Output rnav: Table of entries on right hand side
echo '<div class="rnav">';

$li = array();

$li[] =		'<a id="' . ((!empty($tts_vars['id'])) ? '' : 'current')
			. '" href="config.php?display=' . urlencode($display) . '">'
			. ((!empty($tts_vars['id'])) ? '' : '<b>') . _("<i>Add Text To Speech</i>")
			. ((!empty($tts_vars['id'])) ? '' : '</b>') . '</a>';

foreach (texttospeech_list() as $ent) {
	$li[] = '<a id="'	. (($ent['id'] == $tts_vars['id']) ? 'current' : '') . '" '
						. 'href="config.php?display=' . urlencode($display) . '&'
						. 'id=' . urlencode($ent['id']) . '">'
						. (($ent['id'] == $tts_vars['id'] ) ? '<b>' : '')
						. $ent['name']
						. (($ent['id'] == $tts_vars['id'] ) ? '</b>' : '')
						. '</a>';
}

echo ul($li);
echo '</div>';

?>
