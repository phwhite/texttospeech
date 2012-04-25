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
global $db;
global $amp_conf;
global $asterisk_conf;

$sound_dir			= 'texttospeech/';
$cache_dir = $asterisk_conf['astvarlibdir'] . '/sounds/' . $sound_dir;

$autoincrement = (($amp_conf["AMPDBENGINE"] == "sqlite") ||
			($amp_conf["AMPDBENGINE"] == "sqlite3")) ? "AUTOINCREMENT":"AUTO_INCREMENT";

outn(_("Creating SQL table if it doesn't exist yet...."));
$sql = "CREATE TABLE IF NOT EXISTS texttospeech (
		id INT NOT NULL $autoincrement PRIMARY KEY,
		name VARCHAR(100) NOT NULL,
		engine VARCHAR(50) NOT NULL,
		engine_conf MEDIUMBLOB,
		source VARCHAR(50) NOT NULL,
		source_conf MEDIUMBLOB,
		wait_before TINYINT UNSIGNED DEFAULT '1',
		wait_after TINYINT UNSIGNED DEFAULT '0',
		allow_skip BOOL DEFAULT false,
		allow_control BOOL DEFAULT false,
		control_skipms INT UNSIGNED DEFAULT '3000',
		control_esc VARCHAR(12) DEFAULT '#',
		control_rew VARCHAR(1) DEFAULT '1',
		control_fwd VARCHAR(1) DEFAULT '3',
		control_pause VARCHAR(1) DEFAULT '2',
		no_answer BOOL DEFAULT false,
		return_ivr BOOL DEFAULT false,
		destination VARCHAR(50)
	)";

$result = $db->query($sql);
if(DB::IsError($result)) {
	die_freepbx(__FILE__ . " - " . __FUNCTION__ . "() - " . $result->getMessage() . " - " . $sql);
}
out(_("done"));

/***********************************************************************
 *
 * Version 2.0.0.0 changes:
 *		Changed 'arguments' to 'engine_conf' as mediumblob
 *		Changed 'direct_dial' to 'allow_control' as bool default false
 *		  Added 'source' as varchar(50) not null
 *		  Added 'source_conf as mediumblob
 *		  Added 'control_skipms' as int unsigned default 3000
 *		  Added 'control_esc' as varchar(12) default '#'
 *		  Added 'control_rew' as varchar(1) default '1'
 *		  Added 'control_fwd' as varchar(1) default '3'
 *		  Added 'control_pause' as varchar(1) default '2'
 *
 **********************************************************************/
outn(_("Checking if database is pre v2.0.0.0..."));

$sql = "SELECT engine_conf FROM texttospeech";
$result = $db->getRow($sql, DB_FETCHMODE_ASSOC);
if(DB::IsError($result)) {
	outn(_("Yes it is, migrating..."));

	// Change 'arguments' to 'engine_conf'
    $sql = "ALTER TABLE texttospeech CHANGE arguments engine_conf MEDIUMBLOB;";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx(__FILE__." - ".__FUNCTION__."() - " . $result->getMessage() . " - " . $sql);
	}

	// Change 'direct_dial' to 'allow_control'
    $sql = "ALTER TABLE texttospeech CHANGE direct_dial allow_control BOOL DEFAULT false;";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx(__FILE__." - ".__FUNCTION__."() - " . $result->getMessage() . " - " . $sql);
	}

	// Add new columns
	$sql = "ALTER TABLE texttospeech
			ADD COLUMN source VARCHAR(50) NOT NULL,
			ADD COLUMN source_conf MEDIUMBLOB,
			ADD COLUMN control_skipms INT UNSIGNED DEFAULT '3000',
			ADD COLUMN control_esc VARCHAR(12) DEFAULT '#',
			ADD COLUMN control_rew VARCHAR(1) DEFAULT '1',
			ADD COLUMN control_fwd VARCHAR(1) DEFAULT '3',
			ADD COLUMN control_pause VARCHAR(1) DEFAULT '2';";
	$result = $db->query($sql);
	if(DB::IsError($result)) {
		die_freepbx(__FILE__." - ".__FUNCTION__."() - " . $result->getMessage() . " - " . $sql);
	}

	// Convert old 'arguments' values to new engine_conf format
	outn(_("Converting config values..."));
	$sql = "SELECT id, name, engine_conf FROM texttospeech;";

	$rows = $db->getAll($sql, DB_FETCHMODE_ASSOC);
	if(DB::IsError($rows)) {
		die_freepbx(__FILE__." - ".__FUNCTION__."() - " . $rows->getMessage() . " - " . $sql);
	}

	foreach($rows as $tts) {
		if (isset($tts['engine_conf']) && !empty($tts['engine_conf'])) {
			$econf = array('arguments' => $tts['engine_conf']);
			$engine_conf = serialize($econf);
		}
		else {
			$engine_conf = "";
		}

		$source = "text";
		$text_file = $cache_dir . $tts['name'] . ".txt";
		if (file_exists($text_file)) {
				$sconf = array();
				$sconf['text'] = file_get_contents($text_file);
				$source_conf = serialize($sconf);
		}

		$sql = "UPDATE texttospeech SET
					source = ".sql_formattext($source).",
					source_conf = ".sql_formattext($source_conf).",
					engine_conf = ".sql_formattext($engine_conf)."
				WHERE id = ".sql_formattext($tts['id']).";";
		$result = $db->query($sql);
		if(DB::IsError($result)) {
			die_freepbx(__FILE__." - ".__FUNCTION__."() - "
														. $result->getMessage() . " - " . $sql);
		}
	}
	out(_("migration complete"));
}
else {
	out(_("Database format is up-to-date"));
}

?>
