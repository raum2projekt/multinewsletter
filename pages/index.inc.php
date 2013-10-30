<?php
	// Eventuell Datenbank aktualisieren
	require_once("index_database_updates.inc.php");

	require_once($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/functions/basics.inc.php');

	$REX['ADDON375']['postget'] = myrexvars_read_postget_parameters();

	if(empty($REX['ADDON375']['postget']['subpage']) || !file_exists($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/pages/'.$REX['ADDON375']['postget']['subpage'].'.inc.php')) {
    	$REX['ADDON375']['postget']['subpage'] = 'newsletter';
	}

	$REX['ADDON375']['thispage'] = '?page='.$REX['ADDON375']['addon_name'].'&amp;subpage='.$REX['ADDON375']['postget']['subpage'];
  
	include($REX['INCLUDE_PATH'].'/addons/'.$REX['ADDON375']['addon_name'].'/pages/'.$REX['ADDON375']['postget']['subpage'].'.inc.php');    
?>
