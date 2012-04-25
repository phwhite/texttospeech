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

/*********************  DIALPLAN SETTINGS  *********************/
echo heading(_('Dialplan Settings') . '<hr class="texttospeech-hr">', 4);

/****  Form Field: Wait Before ****/
$label = fpbx_label(	_('Wait Before'),
						_('Wait time in second before playback of the message. Useful '
						. 'for adding a short pause to avoid non-stop talking.')
	);
$fdata = array(	'name'		=> 'wait_before',
				'tabindex'	=> ++$tabindex,
				'size'		=> '2',
				'maxlength'	=> '2',
				'value'		=> $tts_vars['wait_before']
	);
$table->add_row($label, form_input($fdata));

/****  Form Field: Wait After ****/
$label = fpbx_label(	_('Wait After'),
						_('Wait time in second after playback of the message. Useful '
						. 'for adding a short pause to avoid non-stop talking.')
	);
$fdata = array(	'name'		=> 'wait_after',
				'tabindex'	=> ++$tabindex,
				'size'		=> '2',
				'maxlength'	=> '2',
				'value'		=> $tts_vars['wait_after']
	);
$table->add_row($label, form_input($fdata));

/****  Form Field: Don't Answer ****/
$label = fpbx_label(	_("Don't Answer"),
						_('Check this to keep the channel from explicitly being answered. '
						. 'When checked, the message will be played and if the channel is '
						. 'not already answered it will be delivered as early media if '
						. 'the channel supports that. When not checked, the channel is '
						. 'answered followed by a 1 second delay. When using an '
						. 'annoucement from an IVR or other sources that have already '
						. 'answered the channel, that 1 second delay may not be desired.')
	);
$fdata = array(	'name'		=> 'no_answer',
				'tabindex'	=> ++$tabindex,
				'value'		=> '1'
			);
if (!empty($tts_vars['no_answer'])) {
	$fdata['checked'] = "checked";
}
$table->add_row($label, form_checkbox($fdata));

echo $table->generate() . $table->clear() . "<br><br>";
/************************  END OF OPTIONS  **********************/


/***************************  DESTINATION  *************************/
echo heading(_('Destination') . '<hr class="texttospeech-hr">', 4);

/****  Form Field: Return to IVR ****/
$label = fpbx_label(	_('Return To IVR'),
						_('If this entry came from an IVR and this box is checked, the '
						. 'destination below will be ignored and instead it will return '
						. 'to the calling IVR.  (If you are using Text To Speech to say '
						. 'the options for the menu then do not use this option because '
						. 'it will go directly to the IVR to await a key press without '
						. 'saying the menu options.  Instead uncheck this option and '
						. 'select a Destination to the Text To Speech entry that says '
						. 'the menu options.')
				);
$fdata = array(	'name'		=> 'return_ivr',
				'id'		=> 'return_ivr',
				'tabindex'	=> ++$tabindex,
				'value'		=> '1',
				'onChange'	=> 'ret_ivr_change()'
			);
if (!empty($tts_vars['return_ivr'])) {
	$fdata['checked'] = "checked";
}
$table->add_row($label, form_checkbox($fdata));

echo $table->generate() . $table->clear();

/****  Form Field: Destination ****/

if (!empty($tts_vars['return_ivr'])) {
	$dstyle = 'display: none;';
}
else {
	$dstyle = '';
}
echo '<div id="tts_destination" style="' . $dstyle . '">' . "\n";

$label = fpbx_label(	_('Destination'),
			_('')
	);
$dest_html = '<table>';
$dest_html .= drawselects(empty($tts_vars['destination']) ?
											null : $tts_vars['destination'], 0);
$dest_html .= '</table>';
$table->add_row($label, $dest_html);


echo $table->generate() . $table->clear();

echo '</div>';

?>
