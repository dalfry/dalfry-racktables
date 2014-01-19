<?php

/*

QR code printer for Racktables
Dalfry (aka VaibhaV Sharma <vaibhav@vaibhavsharma.com> )

INSTALL Instructions
- Copy rt-qrcode.php in the plugins directory
- Copy rt-qrcode directory in the plugins directory

- 1/18/14 -
-- Initial code to print QR code that contains Serial number

*/


//QR Code Tab
$tabhandler['object']['QRCode'] = 'printQRCode';
$tab['object']['QRCode'] = 'QR Code';
$trigger['object']['QRCode'] = 'checkifserver';

// Racktables Installation Base URI
//$rt-baseuri = "/racktables";

function printQRCode()
{
	assertUIntArg ('object_id', __FUNCTION__);
	$object = spotEntity ('object', $_REQUEST['object_id']);

	$attributes = getAttrValues ($object['id'], TRUE);
	$oem_sn_1 = $attributes[1][value];

	echo "<p>Printing QR Code contents -<p><p>Serial Number: ".$oem_sn_1."<br>";
	$text = 'Serial Number: '.$oem_sn_1;
	echo '<img src="plugins/rt-qrcode/rt-printqrcode.php?text='.$text.'" alt="abcd">';
}

function checkifserver()
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
