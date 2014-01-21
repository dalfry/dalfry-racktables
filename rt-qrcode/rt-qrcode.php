<?php
/*
 * QR code printer plugin for Racktables Dalfry (aka VaibhaV Sharma <vaibhav@vaibhavsharma.com> )
 *
 * @author VaibhaV Sharma <vaibhav@vaibhavsharma.com>
 * @author Franck <franck@linuxpourtous.com>
 */

require_once ('rt-qrcode/phpqrcode.php');

// QR Code Tab
$tabhandler['object']['QRCode']	= 'printQRCode';
$tab['object']['QRCode']		= 'QR Code';
$trigger['object']['QRCode']	= 'checkifirun';

function printQRCode()
{
	$object_id		= (int) $_REQUEST['object_id'];
	$level_infos	= (isset($_REQUEST['level_infos']) && !empty($_REQUEST['level_infos'])) ? (int) $_REQUEST['level_infos'] : 1;

	// Gather Object main page
	$parse_url = parse_url($_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	$objecturl = $parse_url['scheme'] . '://' . $parse_url['host'] . $parse_url['path'] . '?page=object&tab=default&object_id=' . $object_id;

	assertUIntArg('object_id', __FUNCTION__);
	$object				= spotEntity('object', $object_id);
	$ports_links		= getObjectPortsAndLinks($object_id);
	$IPv4Allocations	= getObjectIPv4Allocations($object_id);
	$attributes			= getAttrValues($object_id, true);
	$array_infos		= build_level_infos($level_infos, array($object, $attributes, $ports_links, $IPv4Allocations));

	// Format gathred info for QR code
	$text_qrcode = 'URL : ' . $objecturl;

	$chk_level_infos1	= ($level_infos == 1) ? 'checked' : '';
	$chk_level_infos5	= ($level_infos == 5) ? 'checked' : '';
	$chk_level_infos10	= ($level_infos == 10) ? 'checked' : '';

	// Print gathered info
	echo '<div align="center">';
	echo '<h1>Printing QR Code contents</h1>';

	echo '<form id="qrcode" name="qrcode" action="'.$_SERVER['REQUEST_URI'].'" method="POST">';
	echo 'Level Infos : ';
	echo '<input type="radio" id="level_infos1" name="level_infos" value="1" onClick="this.form.submit();" '.$chk_level_infos1.'> <label for="level_infos1">Mini</label>';
	echo '<input type="radio" id="level_infos5" name="level_infos" value="5" onClick="this.form.submit();" '.$chk_level_infos5.'> <label for="level_infos5">Medium</label>';
	echo '<input type="radio" id="level_infos10" name="level_infos" value="10" onClick="this.form.submit();" '.$chk_level_infos10.'> <label for="level_infos10">Full</label>';

	echo '<br /><br />';
	echo '<li>Object URL : <a href="'.$objecturl.'">'.$objecturl.'</a></li>';

	if (!empty($array_infos))
	{
		foreach ($array_infos as $key => $value)
		{
			echo "<li>$key : $value</li>";
			$text_qrcode .= "\n" . $key .' : '. $value;
		}
	}

	ob_start();
	QRcode::png(trim($text_qrcode), '');
	$image_png = ob_get_clean();

	echo '<img src="data:image/png;base64,'.base64_encode($image_png).'" alt="'.$objecturl.'">';
	echo '</div></form>';
}

function checkifirun()
{
	$object_id	= (int) $_REQUEST['object_id'];
	assertUIntArg('object_id', __FUNCTION__);
	$object		= spotEntity('object', $object_id);
	$record		= getAttrValues($object_id, true);

	return (($object['objtype_id'] == 4 || $object['objtype_id'] == 5 || $object['objtype_id'] == 6)) ? 1 : '';
}

/**
 * Build an array with all object informations
 *
 * level 1 = mini
 * level 5 = medium
 * level 10 = full
 *
 * @param $level int Level
 * @param $all_infos Array with all informations
 * @return $array_infos Array with formatted informations
 */
function build_level_infos($level, $all_infos)
{
	$level			= (int) $level;
	$array_infos	= array();

	if ($level > 10)
		return false;

	$object				= $all_infos[0];
	$attributes			= $all_infos[1];
	$ports_links		= $all_infos[2];
	$IPv4Allocations	= $all_infos[3];

	if ($level == 1)
	{
		$array_infos['name'] = $object['name'];
		$array_infos['label']		= $object['label'];
		$array_infos['asset_no']	= $object['asset_no'];
	}

	if ($level == 5)
	{
		$array_infos['name']		= $object['name'];
		$array_infos['label']		= $object['label'];
		$array_infos['asset_no']	= $object['asset_no'];
		$array_infos['FQDN']		= $attributes[3]['a_value'];
		$array_infos['SN']			= $attributes[1]['a_value'];

		if (!empty($ports_links))
			$array_infos += build_ports_links($ports_links);

		if (!empty($IPv4Allocations))
			$array_infos += build_IPv4Allocations($IPv4Allocations);
	}

	if ($level == 10)
	{
		$array_infos['name']					= $object['name'];
		$array_infos['label']					= $object['label'];
		$array_infos['asset_no']				= $object['asset_no'];
		$array_infos['container_name']			= $object['container_name'];
		$array_infos['has_problems']			= $object['has_problems'];
		$array_infos['contact']					= $attributes[14]['a_value'];
		$array_infos['FQDN']					= $attributes[3]['a_value'];
		$array_infos['HW type']					= $attributes[2]['a_value'];
		$array_infos['HW warranty expiration']	= $attributes[22]['a_value'];
		$array_infos['Hypervisor']				= $attributes[26]['a_value'];
		$array_infos['SN']						= $attributes[1]['a_value'];
		$array_infos['SW type']					= $attributes[4]['a_value'];
		$array_infos['UUID']					= $attributes[25]['a_value'];

		if (!empty($ports_links))
			$array_infos += build_ports_links($ports_links);

		if (!empty($IPv4Allocations))
			$array_infos += build_IPv4Allocations($IPv4Allocations);
	}

	return $array_infos;
}

function build_ports_links($ports_links)
{
	$array_infos = array();

	if (!empty($ports_links))
	{
		foreach ($ports_links as $key => $value)
		{
			$ports_links	= (object) $value;
			$tmp			= '['.$ports_links->oif_name.'] '.$ports_links->remote_object_name.'->'.$ports_links->remote_name;

			if (!empty($ports_links->l2address))
				$tmp .= ' ('.$ports_links->l2address.')';

			$array_infos['Port '.$ports_links->name] = $tmp;
		}
	}

	return $array_infos;
}

function build_IPv4Allocations($IPv4Allocations)
{
	$array_infos = array();

	if (!empty($IPv4Allocations))
	{
		foreach ($IPv4Allocations as $key => $value)
		{
			$IPv4Allocation = (object) $value;
			$array_infos['IP '.$IPv4Allocation->osif] = $IPv4Allocation->addrinfo['ip'];
		}
	}

	return $array_infos;
}
?>
