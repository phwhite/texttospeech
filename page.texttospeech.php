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

// Include the crypt helper for our audio player
require_once(dirname(__FILE__) . '/helpers/crypt.helper.php');

// Reset our page tab order
$tabindex = 0;

// Flatten some variables from request
$display = $_REQUEST['display'];

if (isset($_REQUEST['delete'])) {
	$action = 'delete';
}

// Copy destination over so process_request finds it
if (isset($_REQUEST['goto0']) && isset($_REQUEST[$_REQUEST['goto0']."0"])) {
	$_REQUEST['destination'] = $_REQUEST[$_REQUEST['goto0']."0"];
}
else {
	$_REQUEST['destination'] = '';
}

// Process our form variables
$tts_vars = texttospeech_process_request($_REQUEST);

// Handle form actions
switch ($action) {

case 'add': 
	// Adding an entry
	texttospeech_add_entry($tts_vars);
	if (texttospeech_auto_reload() === false) {
		needreload();
	}
	redirect_standard('id');
	break;

case 'edit':
	// Modifying an entry
	texttospeech_modify_entry($tts_vars);

	/***** No longer need reload here since dialplan just calls AGI script *****
	if (texttospeech_auto_reload() === false) {
		needreload();
	}
	****************************************************************************/

	texttospeech_run_test($tts_vars);

	redirect_standard('id');
	break;

case 'delete':
	// Removing an entry
	texttospeech_remove_entry($tts_vars['id']);
	if (texttospeech_auto_reload() === false) {
		needreload();
	}
	redirect_standard('id');
	break;

default:
	// Viewing an entry (or new entry page)
	if (!empty($tts_vars['id'])) {
		// Load our config information from the database
		$tts_vars = texttospeech_load_entry($tts_vars['id']);
	}
	break;

}

$tts->set_tts_vars($tts_vars);

// Output rnav list
include_once(dirname(__FILE__) . '/views/rnav.php');

// Global table used multiple tiles
$table = new CI_Table;

// Start our configuration form
$fattrs = array(	'name'		=> 'texttospeech',
					'onsubmit'	=> 'return tts_submit_check(texttospeech);'
			);

echo form_open($_SERVER['REQUEST_URI'], $fattrs);
echo form_hidden('display', urlencode($display));
echo form_hidden('action', empty($tts_vars['id']) ? 'add' : 'edit');
echo form_hidden('id', $tts_vars['id']);

// Output top information
include_once(dirname(__FILE__) . '/views/top.php');

// Output General Settings section
include_once(dirname(__FILE__) . '/views/settings.php');

echo '<br><br>';

// Output source-specific settings
include_once(dirname(__FILE__) . '/views/source_settings.php');

echo '<br><br>';

// Output engine-specific settings
include_once(dirname(__FILE__) . '/views/engine_settings.php');

echo '<br><br>';

// Output dialplan settings
include_once(dirname(__FILE__) . '/views/dialplan_settings.php');

echo '<br>';

// Output submit/delete buttons
?>
<h6> 
<input name="submit" type="submit" value="<?php echo _("Submit Changes")?>" tabindex="<?php echo ++$tabindex;?>">
	
<?php if( isset( $tts_vars['id'] ) ) echo ( '&nbsp;<input name="delete" type="submit" value="'._("Delete").'" tabindex="++$tabindex">' ); ?>
</h6>
<?php

// Output Javascript
include_once(dirname(__FILE__) . '/views/javascript.php');

?>

</form>
