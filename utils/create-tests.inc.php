<?php

/****************************************************************
 * BEGIN TEST FOR: source [cmd], engine [swift]                 *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		cmd				=> '',
							dynamic			=> '0',
							fail_dest		=> '',
					);
$eng_conf =		Array(		voice			=> 'Dianne',
							arguments		=> '',
							dynamic			=> '',
					);
create_test("cmd-swift defaults", "cmd", $src_conf, "swift", $eng_conf, $tts_vars);


/****************************************************************
 * BEGIN TEST FOR: Dynamic source [cmd], engine [swift]         *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		cmd				=> '',
							dynamic			=> '1',
							fail_dest		=> 'app-blackhole,congestion,1',
					);
$eng_conf =		Array(		voice			=> 'Dianne',
							arguments		=> '',
							dynamic			=> '',
					);
create_test("Dynamic cmd-swift defaults", "cmd", $src_conf, "swift", $eng_conf, $tts_vars);


/****************************************************************
 * BEGIN TEST FOR: source [file], engine [swift]                *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		file			=> '',
							dynamic			=> '0',
							fail_dest		=> '',
					);
$eng_conf =		Array(		voice			=> 'Dianne',
							arguments		=> '',
							dynamic			=> '',
					);
create_test("file-swift defaults", "file", $src_conf, "swift", $eng_conf, $tts_vars);


/****************************************************************
 * BEGIN TEST FOR: Dynamic source [file], engine [swift]        *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		file			=> '',
							dynamic			=> '1',
							fail_dest		=> 'app-blackhole,congestion,1',
					);
$eng_conf =		Array(		voice			=> 'Dianne',
							arguments		=> '',
							dynamic			=> '',
					);
create_test("Dynamic file-swift defaults", "file", $src_conf, "swift", $eng_conf, $tts_vars);


/****************************************************************
 * BEGIN TEST FOR: source [text], engine [swift]                *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		text			=> 'test 1 2 3 4 5.',
							fail_dest		=> '',
					);
$eng_conf =		Array(		voice			=> 'Dianne',
							arguments		=> '',
							dynamic			=> '',
					);
create_test("text-swift defaults", "text", $src_conf, "swift", $eng_conf, $tts_vars);


/****************************************************************
 * BEGIN TEST FOR: source [url], engine [swift]                 *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		url				=> '',
							dynamic			=> '0',
							strip_tags		=> '1',
							timeout			=> '5',
							fail_dest		=> '',
					);
$eng_conf =		Array(		voice			=> 'Dianne',
							arguments		=> '',
							dynamic			=> '',
					);
create_test("url-swift defaults", "url", $src_conf, "swift", $eng_conf, $tts_vars);


/****************************************************************
 * BEGIN TEST FOR: Dynamic source [url], engine [swift]         *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		url				=> '',
							dynamic			=> '1',
							strip_tags		=> '1',
							timeout			=> '5',
							fail_dest		=> 'app-blackhole,congestion,1',
					);
$eng_conf =		Array(		voice			=> 'Dianne',
							arguments		=> '',
							dynamic			=> '',
					);
create_test("Dynamic url-swift defaults", "url", $src_conf, "swift", $eng_conf, $tts_vars);




/****************************************************************
 * BEGIN TEST FOR: engine [espeak], source [text]               *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		text			=> 'test 1 2 3 4 5.',
							fail_dest		=> '',
					);
$eng_conf =		Array(		voice			=> 'default',
							arguments		=> '',
					);
create_test("espeak-text defaults", "text", $src_conf, "espeak", $eng_conf, $tts_vars);


/****************************************************************
 * BEGIN TEST FOR: engine [flite], source [text]                *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		text			=> 'test 1 2 3 4 5.',
							fail_dest		=> '',
					);
$eng_conf =		Array(		arguments		=> '',
					);
create_test("flite-text defaults", "text", $src_conf, "flite", $eng_conf, $tts_vars);


/****************************************************************
 * BEGIN TEST FOR: engine [google], source [text]               *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		text			=> 'test 1 2 3 4 5.',
							fail_dest		=> '',
					);
$eng_conf =		Array(		language		=> 'en',
							speed			=> '1.2',
					);
create_test("google-text defaults", "text", $src_conf, "google", $eng_conf, $tts_vars);


/****************************************************************
 * BEGIN TEST FOR: engine [swift], source [text]                *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		text			=> 'test 1 2 3 4 5.',
							fail_dest		=> '',
					);
$eng_conf =		Array(		voice			=> 'Dianne',
							arguments		=> '',
							dynamic			=> '',
					);
create_test("swift-text defaults", "text", $src_conf, "swift", $eng_conf, $tts_vars);


/****************************************************************
 * BEGIN TEST FOR:Dynamic  engine [swift], source [text]        *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		text			=> 'test 1 2 3 4 5.',
							fail_dest		=> '',
					);
$eng_conf =		Array(		voice			=> 'Dianne',
							arguments		=> '',
							dynamic			=> '1',
							fail_dest		=> 'app-blackhole,congestion,1',
					);
create_test("Dynamic swift-text defaults", "text", $src_conf, "swift", $eng_conf, $tts_vars);


/****************************************************************
 * BEGIN TEST FOR: engine [text2wave], source [text]            *
 ****************************************************************/
$tts_vars =		Array(		wait_before		=> '1',
							wait_after		=> '0',
							allow_skip		=> '',
							allow_control	=> '',
							no_answer		=> '',
							return_ivr		=> '',
							destination		=> '',
							control_skipms	=> '3000',
							control_esc		=> '#',
							control_rew		=> '1',
							control_fwd		=> '3',
							control_pause	=> '2',
					);
$src_conf =		Array(		text			=> 'test 1 2 3 4 5.',
							fail_dest		=> '',
					);
$eng_conf =		Array(		arguments		=> '',
					);
create_test("text2wave-text defaults", "text", $src_conf, "text2wave", $eng_conf, $tts_vars);



?>
