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

/***************************  SETTINGS  *************************/
echo heading(_('Settings') . '<hr class="texttospeech-hr">', 4);

/****  Form Field: Name ****/
$label = fpbx_label(	_('Name'),
						_('Enter the Name of this Text To Speech entry to help you '
						. 'identify it. It can only be composed of alpha-numeric '
						. 'characters (a-z A-Z 0-9), the underscore (_) and dash (-) '
						. 'but no spaces or other characters.')
	);
$fdata = array(	'name'		=> 'name',
				'tabindex'	=> ++$tabindex,
				'value'		=> $tts_vars['name']
	);
$table->add_row($label, form_input($fdata));

/****  Form Field: Source ****/
$label = fpbx_label(	_('Source'),
						_('Source of where text should come from, i.e. static text, '
						. 'A file, URL, etc.')
	);
$srcselect = '<select name="source" id="source" tabindex=' . ++$tabindex
													. ' onChange="source_change()">';
foreach($tts->sources as $sname => $sinfo) {
	$srcselect .= '<option value="' . $sname . '"';
	if ($sinfo['supported'] != 1) {
		$srcselect .= ' disabled="disabled"';
	}
	if ($tts_vars['source'] == $sname) {
		$srcselect .= ' selected="selected"';
	}
	$srcselect .= '>' . $sinfo['description'] . '</option>';
}
$srcselect .= '</select>';
$table->add_row($label, $srcselect);

/****  Form Field: Engine ****/
$label = fpbx_label(	_('Engine'),
						_('List of Text To Speech engines detected on the server (e.g. '
						. 'swift, flite, text2wave, espeak).  Choose the one you want '
						. 'to use for the current text.  If you do not see the engines '
						. 'that you have installed on the system ensure that their '
						. 'executable binaries are located or linked to a popular bin '
						. 'folder that is in the system PATH.  (This module does not use '
						. 'the specific Asterisk internal applicationts access these '
						. 'engines so they do not have to be installed into Asterisk, '
						. 'instead it executes the binaries directly from the system to '
						. 'create the sounds files to be cached.)')
	);
$engselect = '<select name="engine" id="engine" tabindex=' . ++$tabindex
													. ' onChange="engine_change()">';
foreach($tts->engines as $ename => $einfo) {
	$engselect .= '<option value="' . $ename . '"';
	if ($einfo['supported'] != 1) {
		$engselect .= ' disabled="disabled"';
	}
	if ($tts_vars['engine'] == $ename) {
		$engselect .= ' selected="selected"';
	}
	$engselect .= '>' . $einfo['description'] . '</option>';
}
$engselect .= '</select>';
$table->add_row($label, $engselect);

/****  Create control div output for controllable field  ****/
if (empty($tts_vars['allow_control'])) {
	$dstyle = 'display: none;';
}
else {
	$dstyle = '';
}
$ctrl_div = '<div id="tts_control" style="' . $dstyle . '">' . "\n";
$ctrl_div .= '<smaller><i><table border=0 cellpadding=1 cellspacing=1><tr>' . "\n";

// Control div: Rewind key
$label = fpbx_label(	_('Abort'),
						_('Escape/Abort Key. Will abort playback when pressed.  Leave '
						. 'blank to disable aborting. Can be 0-9, *, or #.')	);
$fdata = array(	'name'		=> 'control_esc',
				'tabindex'	=> ++$tabindex,
				'size'		=> '1',
				'maxlength'	=> '1',
				'value'		=> $tts_vars['control_esc']
	);
$ctrl_div .= '<td>' . $label . '<br>' . form_input($fdata) . '</td>' . "\n";

// Control div: Rewind key
$label = fpbx_label(	_('Rewind'),
						_('Rewind Key.  Can be 0-9, *, or #.')	);
$fdata = array(	'name'		=> 'control_rew',
				'tabindex'	=> ++$tabindex,
				'size'		=> '1',
				'maxlength'	=> '1',
				'value'		=> $tts_vars['control_rew']
	);
$ctrl_div .= '<td>' . $label . '<br>' . form_input($fdata) . '</td>' . "\n";

// Control div: Pause key
$label = fpbx_label(	_('Pause'),
						_('Pause Key.  Can be 0-9, *, or #.')	);
$fdata = array(	'name'		=> 'control_pause',
				'tabindex'	=> ++$tabindex,
				'size'		=> '1',
				'maxlength'	=> '1',
				'value'		=> $tts_vars['control_pause']
	);
$ctrl_div .= '<td>' . $label . '<br>' . form_input($fdata) . '</td>' . "\n";

// Control div: Fast-Forward key
$label = fpbx_label(	_('Forward'),
						_('Fast-Forward Key.  Can be 0-9, *, or #.')	);
$fdata = array(	'name'		=> 'control_fwd',
				'tabindex'	=> ++$tabindex,
				'size'		=> '1',
				'maxlength'	=> '1',
				'value'		=> $tts_vars['control_fwd']
	);
$ctrl_div .= '<td>' . $label . '<br>' . form_input($fdata) . '</td>' . "\n";

// Control div: Skip time in ms
$label = fpbx_label(	_('Time'),
						_('Amount of time in milliseconds to fast-forward or rewind. '
						. 'Minimum value is 1000 (one second).')	);
$fdata = array(	'name'		=> 'control_skipms',
				'tabindex'	=> ++$tabindex,
				'size'		=> '4',
				'maxlength'	=> '4',
				'value'		=> $tts_vars['control_skipms']
	);
$ctrl_div .= '<td>' . $label . '<br>' . form_input($fdata) . '</td>' . "\n";

$ctrl_div .= '</tr></table></i></smaller></div>';


/****  Form Field: Allow playback control  ****/
$label = fpbx_label(	_('Allow Playback Control'),
						_('If selected, allows caller to fast-forward, rewind, or pause '
						. 'playback.  NOTE: Cannot use an engine in dynamic mode when this '
						. 'option is enabled.')
					);
$fdata = array(	'name'		=> 'allow_control',
				'id'		=> 'allow_control',
				'tabindex'	=> ++$tabindex,
				'value'		=> '1',
				'onChange'	=> 'allow_control_change()'
				);
if (!empty($tts_vars['allow_control'])) {
	$fdata['checked'] = "checked";
}

$label_cell = array(	'data'		=> $label,
						'valign'	=> 'top'	);

$table->add_row($label_cell, form_checkbox($fdata) . $ctrl_div);

/****  Form Field: Allow Skip ****/
$label = fpbx_label(	_('Allow Aborting Playback'),
						_('If selected, allows caller to abort the playback message by '
						. 'hitting a key.  NOTE: This option is not available when '
						. 'Allow Playback Control is enabled.')
					);
$fdata = array(	'name'		=> 'allow_skip',
				'id'		=> 'allow_skip',
				'tabindex'	=> ++$tabindex,
				'value'		=> '1',
				);
if (!empty($tts_vars['allow_skip'])) {
	$fdata['checked'] = "checked";
}
if (!empty($tts_vars['allow_control'])) {
	$fdata['disabled'] = "disabled";
}
$table->add_row($label, form_checkbox($fdata));

echo $table->generate() . $table->clear();
/************************  END OF SETTINGS  **********************/

?>
