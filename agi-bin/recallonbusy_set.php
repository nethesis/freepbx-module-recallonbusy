#!/usr/bin/env php
<?php
//Copyright (C) nethesis srl. (info@nethesis.it)
//
//This program is free software; you can redistribute it and/or
//modify it under the terms of the GNU General Public License
//as published by the Free Software Foundation; either version 2
//of the License, or (at your option) any later version.
//
//This program is distributed in the hope that it will be useful,
//but WITHOUT ANY WARRANTY; without even the implied warranty of
//MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//GNU General Public License for more details.

include_once '/etc/freepbx.conf';

define("AGIBIN_DIR", "/var/lib/asterisk/agi-bin");
include(AGIBIN_DIR."/phpagi.php");
$agi = new AGI();

// Get caller
$callerid = $agi->request['agi_callerid'];
//TODO get mainextension for callerid
$agi->verbose("Caller ID = $callerid");

// Check if extension has recall on busy enabled
$status = \FreePBX::Recallonbusy()->getConfig("enabled",$callerid);
$default_status = \FreePBX::Recallonbusy()->getConfig("default");
$agi->verbose("status: ". $status);
$agi->verbose("default status: ". $default_status);
if ($status == "disabled" || ( empty($status) && $default_status == 'disabled' )) {
    $agi->verbose("Recall On Busy DISABLED for extension ".$callerid);
    exit(0);
}
$agi->verbose("Recall On Busy ENABLED for extension ".$callerid);

// Get called id
$calledid = $agi->request['agi_extension'];

//TODO get mainextension for called id
//TODO play press 5 key to recall message
//TODO check pressed digit

//Create db entry for wating queue
$recall_string = trim($astman->database_get("ROB",$calledid));
$extensions_waiting = empty($recall_string) ? array() : explode('&',$recall_string);

if (in_array($callerid,$extensions_waiting)) {
    $agi->verbose("Extension ".$callerid." already waiting for ".$calledid." to become available");
    exit(0);
}
$extensions_waiting[] = $callerid;
$astman->database_put("ROB", $calledid, implode('&',$extensions_waiting));

