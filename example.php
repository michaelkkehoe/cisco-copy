<?php
/* This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Cisco Copy Config  Copyright (C) 2004  Max Schmind

Original code by Max Schmind
Original code can be found at: http://www.phpclasses.org/package/1575-PHP-Backup-Cisco-router-configuration-using-SNMP.html

Modified code by Michael Kehoe
Code can be found at: https://github.com/michael-kehoe/cisco-copy

Changes to this code include: 
 * Slight refactor of examples
*/

require_once('CiscoConfig.php');

$CiscoDevice = '192.168.0.3';
$SnmpRwCommunity = 'goaway';
$TargetHost = '192.168.0.108';

$Config = new Config($CiscoDevice, $SnmpRwCommunity);

// Write Running Config to NVRAM
if( $Config->WriteMemory()) {
	printf("Error Message: %s\n", $Config->Error );
}	

// Backup/ Write Running Config to tftp Server
if( $Config->CopyToNetwork('runningConfig', 'filename','tftp', $TargetHost ) ) {
	printf("Error Message: %s\n", $Config->Error );
}

// Backup/ Write Startup Config to tftp Server
if( $Config->CopyToNetwork('startupConfig', 'filename', 'tftp', $TargetHost ) ) {
	printf("Error Message: %s\n", $Config->Error );
}

// Backup/ Write Running Config file to ftp Server
if( $Config->CopyToNetwork('startupConfig', 'filename', 'ftp', $TargetHost, "username", "password" ) ) {
        printf("Error Message: %s\n", $Config->Error );
}

// Backup/ Write Running Config file to sftp Server
if( $Config->CopyToNetwork('startupConfig', 'filename', 'sftp', $TargetHost, "username", "password" ) ) {
        printf("Error Message: %s\n", $Config->Error );
}

// Copy from network to running config via tftp
if( $Config->CopyToDevice('runningConfig', 'filename','tftp', $TargetHost ) ) {
        printf("Error Message: %s\n", $Config->Error );
}

// Copy from network to startup config via tftp
if( $Config->CopyToDevice('startupConfig', 'filename','tftp', $TargetHost ) ) {
        printf("Error Message: %s\n", $Config->Error );
}

// Copy from network to iosfile via ftp
if( $Config->CopyToDevice('iosFile', 'filename','ftp', $TargetHost ) ) {
        printf("Error Message: %s\n", $Config->Error );
}

?>
