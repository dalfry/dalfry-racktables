<?php

/*
QR code printer plugin for Racktables
Dalfry (aka VaibhaV Sharma <vaibhav@vaibhavsharma.com> )
*/

//QR Code Tab
$tabhandler['object']['QRCode'] = 'printQRCode';
$tab['object']['QRCode'] = 'QR Code';
$trigger['object']['QRCode'] = 'checkifirun';


function printQRCode()
{

// Racktables Installation Base URI
// **** SET THIS or the generated URL will be incorrect
// This will be dynamically generated in the future
$rtbaseuri = '/racktables';

	assertUIntArg ('object_id', __FUNCTION__);
	$object = spotEntity ('object', $_REQUEST['object_id']);

	// Gather Serial Number
	$attributes = getAttrValues ($object['id'], TRUE);
	$oem_sn_1 = $attributes[1][value];

	$utlproto = 'https://';
	// Called URL protocol
	if (empty($_SERVER['HTTPS']))
	{
		$urlproto = 'http://';
	}

	// Gather Object main page
	$objecturl = $urlproto.$_SERVER['HTTP_HOST'].$rtbaseuri.'/index.php?page=object&tab=default&object_id='.$_REQUEST['object_id'];

	// Print gathered info
	echo "<center>";
	echo "<p>Printing QR Code contents -<p>";
	echo "<p>Serial Number: ".$oem_sn_1."<br>";
	echo 'Object URL: <a href="'.$objecturl.'">'.$objecturl.'</a><p>';

	// Format gathred info for QR code	
	$text = 'Serial Number: '.$oem_sn_1;
	$text = $text." URL : ".$objecturl;

	// Print QR Code image
	echo '<img src="plugins/rt-qrcode/rt-printqrcode.php?text='.$text.'" alt="abcd">';
	echo "</center>";
}

function checkifirun()
{
        assertUIntArg ('object_id', __FUNCTION__);
        $object = spotEntity ('object', $_REQUEST['object_id']);
        $record = getAttrValues ($object['id'], TRUE);
        if (($object['objtype_id'] == 4 || $object['objtype_id'] == 5 || $object['objtype_id'] == 6))
                return 1;
        else
        {
                return '';
        }
}


?>
