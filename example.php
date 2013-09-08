<?php
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
