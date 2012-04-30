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

function tts_js_var($var, $val = '') {
	$ret = '';

	if (is_array($var)) {
		foreach($var as $vn => $vv) {
			$ret .= "\r\tvar " . $vn . ' = "' . $vv . '";' . "\n";
		}
	}
	else {
		$ret .= "\r\tvar " . $var . ' = "' . $val . '";' . "\n";
	}

	return $ret;
}

// Output source's javascript
foreach($tts->sources as $sname => $sinfo) {
	$src = $sinfo['ptr'];

	$src->javascript();
}

// Output engine's javascript
foreach($tts->engines as $ename => $einfo) {
	$eng = $einfo['ptr'];

	$eng->javascript();
}

?>
<script language="javascript">
<!--

function tts_isnum(input) {
	return (input - 0) == input && input.length > 0;
}

function tts_isurl(value) {
	    return /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);
}

function tts_submit_check(frm) {
	<?php echo tts_js_var(Array(
		'msgInvalidName' 	=>
		_('Please enter a valid name.  Only the following characters are allowed: letters (a-z, A-Z), numbers (0-9), underscore (_), and dash (-).'),
	
		'msgInvalidMS'		=>
			_('Please enter a valid value in milliseconds for jumping forwards and backwards when controlling playback.  Minimum value is 250.'),
	
		'msgInvalidESC'		=>
		_('Please enter a valid digit for the Escape Control key, or blank for none.  Valid digits are 0-9, #, and *.'),
	
		'msgInvalidREW'		=>
		_('Please enter a valid digit for the Rewind Control key.  Valid digits are 0-9, #, and *.'),
	
		'msgInvalidPAUSE'	=>
		_('Please enter a valid digit for the Pause Control key.  Valid digits are 0-9, #, and *.'),
	
		'msgInvalidFWD'		=>
		_('Please enter a valid digit for the Fast-Forward Control key.  Valid digits are 0-9, #, and *.'),
	
		'msgDupCTRL'		=>
		_('You have specified the same digit for multiple control keys.  Please configure a unique digit for each control key.'),
	)); ?>

	if (document.texttospeech.name.value.search("[^a-z0-9_-]+", "i") != -1 || document.texttospeech.name.value == "" ) {
		return warnInvalid( document.texttospeech.name, msgInvalidName );
	}

	if (document.texttospeech.allow_control.checked) {
		if (document.texttospeech.control_skipms.value == "" || document.texttospeech.control_skipms.value < 250) {
			return warnInvalid( document.texttospeech.control_skipms, msgInvalidMS );
		}
		if (document.texttospeech.control_esc.value != "" && document.texttospeech.control_esc.value.search("[^0-9*#]", "i") != -1) {
			return warnInvalid( document.texttospeech.control_esc, msgInvalidESC );
		}
		if (document.texttospeech.control_rew.value == "" || document.texttospeech.control_rew.value.search("[^0-9*#]", "i") != -1) {
			return warnInvalid( document.texttospeech.control_rew, msgInvalidREW );
		}
		if (document.texttospeech.control_pause.value == "" || document.texttospeech.control_pause.value.search("[^0-9*#]", "i") != -1) {
			return warnInvalid( document.texttospeech.control_pause, msgInvalidPAUSE );
		}
		if (document.texttospeech.control_fwd.value == "" || document.texttospeech.control_fwd.value.search("[^0-9*#]", "i") != -1) {
			return warnInvalid( document.texttospeech.control_fwd, msgInvalidFWD );
		}

		// Dup check: rew <=> pause
		if (document.texttospeech.control_rew.value == document.texttospeech.control_pause.value) {
			return warnInvalid( document.texttospeech.control_pause, msgDupCTRL );
		}
		// Dup check: rew <=> fwd
		if (document.texttospeech.control_rew.value == document.texttospeech.control_fwd.value) {
			return warnInvalid( document.texttospeech.control_fwd, msgDupCTRL );
		}
		// Dup check: rew <=> esc
		if (document.texttospeech.control_rew.value == document.texttospeech.control_esc.value) {
			return warnInvalid( document.texttospeech.control_rew, msgDupCTRL );
		}
		// Dup check: pause <=> fwd
		if (document.texttospeech.control_pause.value == document.texttospeech.control_fwd.value) {
			return warnInvalid( document.texttospeech.control_fwd, msgDupCTRL );
		}
		// Dup check: pause <=> esc
		if (document.texttospeech.control_pause.value == document.texttospeech.control_esc.value) {
			return warnInvalid( document.texttospeech.control_pause, msgDupCTRL );
		}
		// Dup check: fwd <=> esc
		if (document.texttospeech.control_fwd.value == document.texttospeech.control_esc.value) {
			return warnInvalid( document.texttospeech.control_fwd, msgDupCTRL );
		}
	}

	<?php foreach($tts->sources as $sname => $sinfo) { $src = $sinfo['ptr']; ?>
		ret = $("#source option[value='<?php echo $sname?>']:selected");
		if (ret.length) {
			ret = <?php echo $src->js_func('submit_check')?>(frm);
			if (ret != true) {
				return ret;
			}
		}
	<?php } // End of foreach() ?>

	<?php foreach($tts->engines as $ename => $einfo) { $eng = $einfo['ptr']; ?>
		ret = $("#engine option[value='<?php echo $ename?>']:selected");
		if (ret.length) {
			ret = <?php echo $eng->js_func('submit_check')?>(frm);
			if (ret != true) {
				return ret;
			}
		}
	<?php } // End of foreach() ?>

	// Setup destinations
	setDestinations(frm, <?php echo $destidx ?>);

	return tts_submit_progress();
}

function allow_control_change() {
	var	askip = document.getElementById('allow_skip');
	var actrl = document.getElementById('allow_control');

	if (actrl.checked) {
		$("#tts_control").show();
		askip.disabled = true;
		askip.checked = false;
	}
	else {
		$("#tts_control").hide();
		askip.disabled = false;
		askip.checked = <?php echo (!empty($tts_vars['allow_skip'])) ? 'true':'false' ?>;
	}

	<?php foreach($tts->engines as $ename => $einfo) { $eng = $einfo['ptr']; ?>
		var ret = document.getElementById('<?php echo $eng->config_form_id('dynamic')?>');
		if (ret) {
			if (actrl.checked) {
				ret.disabled = true;
				ret.checked = false;
			}
			else {
				ret.disabled = false;
				ret.checked = <?php echo (isset($eng->config['dynamic']) && !empty($eng->config['dynamic'])) ? 'true' : 'false' ?>;
			}
		}
	<?php } // End of foreach() ?>

	eng_dynamic_changed();
}


function ret_ivr_change() {
	var retivr = document.getElementById('return_ivr');
	if (retivr.checked) {
		$("#tts_destination").hide();
	}
	else {
		$("#tts_destination").show();
	}
}

<?php /* PW HERE */ ?>

function source_change() {
	<?php foreach($tts->sources as $sname => $sinfo) { $src = $sinfo['ptr']; ?>
		ret = $("#source option[value='<?php echo $sname?>']:selected");
		if (ret.length) {
			$("#texttospeech_src_<?php echo $sname?>").show();
		}
		else {
			$("#texttospeech_src_<?php echo $sname?>").hide();
		}
	<?php } // End of foreach() ?>
}

function src_dynamic_changed() {
	<?php foreach($tts->sources as $sname => $sinfo) { $src = $sinfo['ptr']; ?>
		var ret = document.getElementById('<?php echo $src->config_form_id('dynamic')?>');
		if (ret && ret.checked) {
			$("#<?php echo $src->config_form_id('dynamic_conf')?>").show();
		}
		else {
			$("#<?php echo $src->config_form_id('dynamic_conf')?>").hide();
		}
	<?php } // End of foreach() ?>
}

function engine_change() {
	<?php foreach($tts->engines as $ename => $einfo) { $eng = $einfo['ptr']; ?>
		ret = $("#engine option[value='<?php echo $ename?>']:selected");
		if (ret.length) {
			$("#texttospeech_eng_<?php echo $ename?>").show();
		}
		else {
			$("#texttospeech_eng_<?php echo $ename?>").hide();
		}
	<?php } // End of foreach() ?>
}

function eng_dynamic_changed() {
	<?php foreach($tts->engines as $ename => $einfo) { $eng = $einfo['ptr']; ?>
		var ret = document.getElementById('<?php echo $eng->config_form_id('dynamic')?>');
		if (ret && ret.checked) {
			$("#<?php echo $eng->config_form_id('dynamic_conf')?>").show();
		}
		else {
			$("#<?php echo $eng->config_form_id('dynamic_conf')?>").hide();
		}
	<?php } // End of foreach() ?>
}

function tts_submit_progress() {
	<?php echo tts_js_var(Array(
		'msgSaving' 	=>
		_('Saving configuration changes...'),
	
		'msgSaveGen'	=>
		_('Saving... Generating text & sound files...'),

		'msgAutoApply'	=>
		_('DEBUG: Saving and Auto Applying Config...'),
	)); ?>

	<?php if ($tts_debug_enabled == false || $tts_auto_reload == false) { ?>
		var dcnt = 0;

		<?php foreach($tts->sources as $sname => $sinfo) { $src = $sinfo['ptr']; ?>
			var ret = document.getElementById('<?php echo $src->config_form_id('dynamic')?>');
			if (ret && ret.checked)
				dcnt++;
		<?php } // End of foreach() ?>
	
		<?php foreach($tts->engines as $ename => $einfo) { $eng = $einfo['ptr']; ?>
			var ret = document.getElementById('<?php echo $eng->config_form_id('dynamic')?>');
			if (ret && ret.checked)
				dcnt++;
		<?php } // End of foreach() ?>
	
		if (dcnt > 0) {
			var prog_msg = String(msgSaving);
		}
		else {
			var prog_msg = String(msgSaveGen);
		}
	<?php } else { ?>
		var prog_msg = String(msgAutoApply);
	<?php } ?>

	return tts_submit_show_progress(prog_msg);
}

<?php if ($tts_legacy_fpbx == false) { ?>
	function tts_submit_show_progress(msg) {
		var prog_title = String(msg);
	
		$('<div></div>').progressbar({value: 100})
		var box = $('<div></div>')
			.html('<progress style="width: 100%">'
				+ 'Please wait...'
				+ '</progress>')
			.dialog({
				title: String(prog_title),
				resizable: false,
				modal: true,
				height: 50,
				position: ['center', 50],
				close: function (e) {
					$(e.target).dialog("destroy").remove();
				}
			});
	}
<?php } else { ?>
	function tts_submit_show_progress(msg) {
		var response_text = String(msg);
	}
<?php } ?>

-->
</script>

