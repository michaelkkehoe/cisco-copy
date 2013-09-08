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
 * Bug fix for CheckCopyStats() 
 * Removing functions _____ and _____
 * Changing of function name ____ to ____
 * Changing of function name ____ to ____
 * Allowing function ____ and ___ to handle all file types
 * Slight refactor of example code

*/

class Config {
	public	$Error;
	public	$debug = TRUE;
	private	$Host;
	private	$Community;
	private $RandomNumber;

	private $ConfigCopyOID = array(
		'ccCopyProtocol' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.2',
		'ccCopySourceFileType' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.3',
		'ccCopyDestFileType' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.4',
		'ccCopyServerAddress' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.5',
		'ccCopyFileName' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.6',
		'ccCopyUserName' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.7',
		'ccCopyUserPassword' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.8',
		'ccCopyNotificationOnCompletion' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.9',
		'ccCopyState' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.10',
		'ccCopyTimeStarted' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.11',
		'ccCopyTimeCompleted' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.12',
		'ccCopyFailCause' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.13',
		'ccCopyEntryRowStatus' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.14',
		'ccCopyServerAddressType' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.15',
		'ccCopyServerAddressRev1' => '.1.3.6.1.4.1.9.9.96.1.1.1.1.16'
	);

	private	$CopyProtocol = array(
		'tftp' => 1,
		'ftp' => 2,
		'rcp' => 3,
		'scp' => 4,
		'sftp' => 5 
	);

	private	$ConfigFileType = array( 
		'networkFile' => 1, 
		'iosFile' => 2, 
		'startupConfig' => 3, 
		'runningConfig' => 4, 
		'terminal' => 5,
		'fabricStartupConfig' => 6 
	);

	private $RowStatus = array(
		'active' => 1,
		'notInService' => 2,
		'notReady' => 3,
		'createAndGo' => 4,
		'createAndWait' => 5,
		'destroy' => 6
	);

	private	$ConfigCopyState = array(
		1 => 'waiting',
		2 => 'running',
		3 => 'successful',
		4 => 'failed'
	);

	private	$ConfigCopyFailCause = array(
		1 => 'unknown',
		2 => 'badFileName',
		3 => 'timeout',
		4 => 'noMem',
		5 => 'noConfig',
		6 => 'unsupportedProtocol',
		7 => 'someConfigApplyFailed',
		8 => 'systemNotReady',
		9 => 'requestAborted' 
	);

	function __construct( $Host, $Community ) {
		//check snmp module
		if( !@extension_loaded( 'snmp' ) ) {
			if( !@dl( 'snmp.so' ) ) {
				die( 'Unable to Load PHP SNMP Module' );
			}
		}
		$this->Host		= $Host;
		$this->Community	= $Community;
		$this->RandomNumber	= rand( 1, 65535 );
		#snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
	}

	function __destruct() {
	}
	private function SetCopyEntryRowStatus( $RowStatus ) {
		$Host = $this->Host;
		$Community = $this->Community;

		if( $this->debug ) {
			printf( "\nSetting Config Copy Entry:\n" );
			printf( "CISCO-CONFIG-COPY-MIB::ccCopyEntryRowStatus.%d to %s ...\n", 
				$this->RandomNumber, 
				array_search( $RowStatus, $this->RowStatus ) );
		}

		//Setting Copy Entry
		$oid = $this->ConfigCopyOID['ccCopyEntryRowStatus'] . 
			'.' . $this->RandomNumber;

		if( snmpset( $Host, $Community, $oid, 'i', $RowStatus ) )
		{
			if( $this->debug ) {
				printf( "CISCO-CONFIG-COPY-MIB::" );
				printf( "ccCopyEntryRowStatus.%d ... Response OK\n", 
					$this->RandomNumber ); 
			}
			return TRUE;
		} else {
			$this->Error = "CISCO-CONFIG-COPY-MIB::ccCopyEntryRowStatus No Response or Response Error";
			return FALSE;
		}
	}

	private function SetSourceFileType( $ConfigFileType ) {
		$Host = $this->Host;
		$Community = $this->Community;

		if( $this->debug ) {
			printf( "\nSetting Source File Type:\n" );
			printf( "CISCO-CONFIG-COPY-MIB::ccCopySourceFileType.%d to %s ...\n", 
				$this->RandomNumber, 
				array_search( $ConfigFileType, $this->ConfigFileType ) );
		}

		// Set Source File Type Value to running-config
		$oid = $this->ConfigCopyOID['ccCopySourceFileType'] . 
			'.' . $this->RandomNumber;
		echo "ConfigFileType: $ConfigFileType\n";
		if( snmpset( $Host, $Community, $oid, 'i', $ConfigFileType ) )
		{
			if( $this->debug ) {
				printf( "CISCO-CONFIG-COPY-MIB::" );
				printf( "ccCopySourceFileType.%d ... Response OK\n", 
					$this->RandomNumber );
			}
			return TRUE;
		} else {
			$this->Error = "CISCO-CONFIG-COPY-MIB::ccCopySourceFileType No Response or Response Error";
			return FALSE;
		}
	}

	private function SetDestFileType( $ConfigFileType ) {
		$Host = $this->Host;
		$Community = $this->Community;

		if( $this->debug ) {
			printf( "\nSetting Destination File Type:\n" );
			printf( "CISCO-CONFIG-COPY-MIB::ccCopyDestFileType.%d to %s ...\n", 
				$this->RandomNumber, 
				array_search( $ConfigFileType, $this->ConfigFileType ) );
		}

		// Set Destination File Type Value to networkFile
		$oid = $this->ConfigCopyOID['ccCopyDestFileType'] . 
			'.' . $this->RandomNumber;
		if( snmpset( $Host, $Community, $oid, 'i', $ConfigFileType ) )
		{
			if( $this->debug ) {
				printf( "CISCO-CONFIG-COPY-MIB::" );
				printf( "ccCopyDestFileType.%d ... Response OK\n", 
					$this->RandomNumber );
			}
			return TRUE;
		} else {
			$this->Error = "CISCO-CONFIG-COPY-MIB::ccCopyDestFileType No Response or Response Error";
			return FALSE;
		}
	}

	private function SetCopyProtocol( $Protocol ) {
		$Host = $this->Host;
		$Community = $this->Community;

		if( $this->debug ) {
			printf( "\nSetting Copy Procotol:\n" );
			printf( "CISCO-CONFIG-COPY-MIB::ccCopyProtocol.%d to %s ...\n", 
				$this->RandomNumber, 
				array_search( $Protocol , $this->CopyProtocol ) );
		}

		// Set Copy Protocol
		$oid = $this->ConfigCopyOID['ccCopyProtocol'] . 
			'.' . $this->RandomNumber;
		if( @snmpset( $Host, $Community, $oid, 'i', $Protocol ) ) {
			if( $this->debug ) {
				printf( "CISCO-CONFIG-COPY-MIB::" );
				printf( "ccCopyProtocol.%d ... Response OK\n", 
					$this->RandomNumber ); 
			}
			return TRUE;
		} else {
			$this->Error = "unsupported Protocol";
			return FALSE;
		}
	}

	private function SetCopyServerAddress( $Address ) {
		$Host = $this->Host;
		$Community = $this->Community;

		if( $this->debug ) {
			printf( "\nSetting Server IP Address:\n" );
			printf( "CISCO-CONFIG-COPY-MIB::ccCopyServerAddress.%d to %s ...\n", 
				$this->RandomNumber, 
				$Address );
		}

		// Set Server IP Address
		$oid = $this->ConfigCopyOID['ccCopyServerAddress'] . 
			'.' . $this->RandomNumber;
		if( snmpset( $Host, $Community, $oid, 'a', $Address ) ) {
			if( $this->debug ) {
				printf( "CISCO-CONFIG-COPY-MIB::" );
				printf( "ccCopyServerAddress.%d ... Response OK\n", 
					$this->RandomNumber ); 
			}
			return TRUE;
		} else {
			$this->Error = "CISCO-CONFIG-COPY-MIB::ccCopyServerAddress No Response or Response Error";
			return FALSE;
		}
	}

	private function SetCopyFileName( $DestFileName ) {
		$Host = $this->Host;
		$Community = $this->Community;

		if( $this->debug ) {
			printf( "\nSetting Destination FileName:\n" );
			printf( "CISCO-CONFIG-COPY-MIB::ccCopyFileName.%d to %s ...\n", 
				$this->RandomNumber, 
				$DestFileName );
		}

		// Set Destination File Name
		$oid = $this->ConfigCopyOID['ccCopyFileName'] . 
			'.' . $this->RandomNumber;
		if( snmpset( $Host, $Community, $oid, 's', $DestFileName ) ) {
			if( $this->debug ) {
				printf( "CISCO-CONFIG-COPY-MIB::" );
				printf( "ccCopyFileName.%d ... Response OK\n", 
					$this->RandomNumber ); 
			}
			return TRUE;
		} else {
			$this->Error = "CISCO-CONFIG-COPY-MIB::ccCopyFileName No Response or Response Error";
			return FALSE;
		}
		
	}

	private function SetCopyUserName( $UserName ) {
		$Host = $this->Host;
		$Community = $this->Community;

		if( $this->debug ) {
			printf( "\nSetting Login UserName:\n" );
			printf( "CISCO-CONFIG-COPY-MIB::ccCopyUserName.%d to %s ...\n", 
				$this->RandomNumber, 
				$UserName );
		}

		// Set Login User Name
		$oid = $this->ConfigCopyOID['ccCopyUserName'] . 
			'.' . $this->RandomNumber;
		if( snmpset( $Host, $Community, $oid, 's', $UserName ) ) {
			if( $this->debug ) {
				printf( "CISCO-CONFIG-COPY-MIB::" );
				printf( "ccCopyUserName.%d ... Response OK\n", 
					$this->RandomNumber ); 
			}
			return TRUE;
		} else {
			$this->Error = "CISCO-CONFIG-COPY-MIB::ccCopyUserName No Response or Response Error";
			return FALSE;
		}
		
	}

	private function SetCopyUserPassword( $UserPassword ) {
		$Host = $this->Host;
		$Community = $this->Community;

		if( $this->debug ) {
			printf( "\nSetting Login Password:\n" );
			printf( "CISCO-CONFIG-COPY-MIB::ccCopyUserPassword.%d to %s ...\n", 
				$this->RandomNumber, 
				$UserPassword );
		}

		// Set Login Password
		$oid = $this->ConfigCopyOID['ccCopyUserPassword'] . 
			'.' . $this->RandomNumber;
		if( snmpset( $Host, $Community, $oid, 's', $UserPassword ) ) {
			if( $this->debug ) {
				printf( "CISCO-CONFIG-COPY-MIB::" );
				printf( "ccCopyUserPassword.%d ... Response OK\n", 
					$this->RandomNumber ); 
			}
			return TRUE;
		} else {
			$this->Error = "CISCO-CONFIG-COPY-MIB::ccCopyUserPassword No Response or Response Error";
			return FALSE;
		}
		
	}

	private function CheckCopyStats() {
		$Host = $this->Host;
		$Community = $this->Community;

		if( $this->debug ) {
			printf( "\nChecking Config Copy State:\n" );
		}

		$oid = $this->ConfigCopyOID['ccCopyState'] . 
			'.' . $this->RandomNumber;
		do {
			$State = snmpget( $Host, $Community, $oid );
			#printf("tryState --> %s \n", $tryState);
			#$State  = eregi_replace("INTEGER:","",$tryState);
			#printf("State --> %s \n", $State);
			$State = eregi_replace("INTEGER: ","",$State);
			if($this->debug) {
				printf( "CISCO-CONFIG-COPY-MIB::" );
				printf( "ccCopyState.%d ... Response %s\n", 
					$this->RandomNumber, 
					$this->ConfigCopyState[$State] );
			}

			if( $State == 3 )
				return TRUE;

			if( $State == 4 ) {
				$Error = snmpget( $Host, $Community,
					$this->ConfigCopyOID['ccCopyFailCause']
					. '.' . $this->RandomNumber );
				$this->Error = $this->ConfigCopyFailCause[$Error];
				return FALSE;
			}
			sleep( 1 );
		} while( 1 );
	}

	private function GetCopyFailCause() {
		$Host = $this->Host;
		$Community = $this->Community;

		$Error = snmpget( $Host, $Community,
			$this->ConfigCopyOID['ccCopyFailCause']
			. '.' . $this->RandomNumber );
		return $this->ConfigCopyFailCause[$Error];
	}

	public function WriteMemory() {
		$RowStatus = $this->RowStatus;
		$ConfigFileType = $this->ConfigFileType;

		//Create Copy Entry and Wait
		if( !$this->SetCopyEntryRowStatus( $RowStatus['createAndWait']) ) {
			return FALSE;
		}

		// Set Source File Type Value to running-config
		if( !$this->SetSourceFileType( $ConfigFileType['runningConfig'] ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $RowStatus['destroy'] );
			return FALSE;
		}

		// Set Destination File Type Value to startup-config
		if( !$this->SetDestFileType( $ConfigFileType['startupConfig'] ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $RowStatus['destroy'] );
			return FALSE;
		}

		//Do it
		if( !$this->SetCopyEntryRowStatus( $RowStatus['active'] ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $RowStatus['destroy'] );
			return FALSE;
		}

		if( !$this->CheckCopyStats() ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $RowStatus['destroy'] );
			return FALSE;
		}

		// Destroy Config Copy Entry
		$this->SetCopyEntryRowStatus( $RowStatus['destroy'] );
		return TRUE;
	}

	public function CopyToNetwork($ConfigType, $FileName, $Protocol, 
		$Server, $Login = NULL, $Password = NULL ) {

		// Check Protocol
		if( !array_key_exists( $Protocol, $this->CopyProtocol ) ) {
			$this->Error = "not support protocol";
			return FALSE;
		}

		//Create Copy Entry and Wait
		if( !$this->SetCopyEntryRowStatus( $this->RowStatus['createAndWait']) ) {
			return FALSE;
		}

		// Set Source File Type Value to startupConfig
		if( !$this->SetSourceFileType( $this->ConfigFileType[$ConfigType] ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}

		// Set Destination File Type Value to networkFile
		if( !$this->SetDestFileType( $this->ConfigFileType['networkFile'] ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}

		// Set Copy Protocol
		if( !$this->SetCopyProtocol( $this->CopyProtocol[$Protocol] ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}
		
		// Set Server IP Address
		if( !$this->SetCopyServerAddress( $Server ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}

		// Set File Name
		if( !$this->SetCopyFileName( $FileName ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}

		if( $Protocol != 'tftp' ) {
			if( !$this->SetCopyUserName( $Login ) ) {
				// Destroy Config Copy Entry
				$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
				return FALSE;
			}

			if( !$this->SetCopyUserPassword( $Password ) ) {
				// Destroy Config Copy Entry
				$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
				return FALSE;
			}
		}

		//Do it
		if( !$this->SetCopyEntryRowStatus( $this->RowStatus['active']) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}

		if( !$this->CheckCopyStats() ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}
		
		// Destroy Config Copy Entry
		$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
		return TRUE;
	}

	public function CopyToDevice($FileType, $FileName, $Protocol, 
		$Server, $Login = NULL, $Password = NULL ) {

		// Check Protocol
		if( !array_key_exists( $Protocol, $this->CopyProtocol ) ) {
			$this->Error = "not support protocol";
			return FALSE;
		}


		// Set Copy Protocol
		if( !$this->SetCopyProtocol( $this->CopyProtocol[$Protocol] ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}
		
		// Set Source File Type Value to networkfile
		if( !$this->SetSourceFileType( $this->ConfigFileType['networkFile'] ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}

		// Set Destination File Type Value to ConfigType
		if( !$this->SetDestFileType( $this->ConfigFileType[$FileType] ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}

		// Set Server IP Address
		if( !$this->SetCopyServerAddress( $Server ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}

		// Set Source File Name
		if( !$this->SetCopyFileName( $FileName ) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}

		if( $Protocol != 'tftp' ) {
			if( !$this->SetCopyUserName( $Login ) ) {
				// Destroy Config Copy Entry
				$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
				return FALSE;
			}

			if( !$this->SetCopyUserPassword( $Password ) ) {
				// Destroy Config Copy Entry
				$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
				return FALSE;
			}
		}

		//Do it
		if( !$this->SetCopyEntryRowStatus( $this->RowStatus['active']) ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}

		if( !$this->CheckCopyStats() ) {
			// Destroy Config Copy Entry
			$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
			return FALSE;
		}
		
		// Destroy Config Copy Entry
		$this->SetCopyEntryRowStatus( $this->RowStatus['destroy'] );
		return TRUE;
	}

}
?>



