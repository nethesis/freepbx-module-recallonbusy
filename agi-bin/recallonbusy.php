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

$callfiles=glob("/var/spool/asterisk/outgoing/rob-*.call");
if (!empty($callfiles)) {
    exit(0);
}

$now=time();

$busy_extensions = $astman->database_show('ROB');

print_r($busy_extensions);

if (!empty($busy_extensions)) {
    foreach ($busy_extensions as $extension_string => $waiting_extensions_string) {
        $busy_extension = preg_replace('/\/ROB\/([0-9]*)$/','$1',$extension_string);
        if (is_free($busy_extension)) {
            $waiting_extensions = explode('&',$waiting_extensions_string);
            $caller = $waiting_extensions[0];
            if (is_free($caller)) {
                call($caller,$busy_extension)
                $astman->database_put("ROB", $busy_extension, implode('&',$waiting_extensions));
            }
        }
    }
}


function call($tocall,$caller,$timestamp) {
    global $astman;
    global $agi;

    $uname = $astman->database_get("AMPUSER/$tocall", "cidname");
    $agi->verbose("Tocall:$tocall $uname, Caller:$caller");

    $tmpfile="/var/spool/asterisk/tmp/rob-$caller-$tocall-$timestamp.call";
    $callfile="/var/spool/asterisk/outgoing/rob-$caller-$tocall-$timestamp.call";
    $fp = fopen($tmpfile, 'w');

    $tmp= "Channel: Local/$caller@from-internal\n";
    $tmp.= "MaxRetries: 2\n";
    $tmp.= "RetryTime: 60\n";
    $tmp.= "WaitTime: 20\n";
    $tmp.= "CallerID: \"Recall Occupato $uname ($tocall)\"\n";
    $tmp.= "Context: from-internal\n";
    $tmp.= "Priority: 1\n";
    $tmp.= "Extension: $tocall\n";

    fwrite($fp, $tmp);
    rename($tmpfile,$callfile);
    fclose($fp);
}

function is_free($tocall) {
    global $astman;
    $r = $astman->GetVar($this, "EXTENSION_STATE($tocall)" );
    if (isset($r['Response']) && $r['Response'] == 'Success' && isset($r['Value']) && $r['Value'] == 'NOT_INUSE') {
        return TRUE;
    }
    return FALSE;
}

