<?php
	if(!defined("__ZBXE__")) exit();

	if($called_position != 'before_module_init') return;

	// 데이터 준비
	$mids = explode(',', $addon_info->mids);
	foreach($mids as $no => $val){
		$mids[$no] = trim($val);
	}

	$forward_mids = explode(',', $addon_info->forward_mids);
	foreach($forward_mids as $no => $val){
		$forward_mids[$no] = trim($val);
	}

	// 현재 mid 검사
	$index = array_search(Context::get('mid'), $mids);
	if ($index === false) return;

	// mid 변경
	$forward_mid = $forward_mids[$index];
	if (!$forward_mid) return;

	$url = getNotEncodedUrl('', 'mid', $forward_mid);
	Header("Location: $url");
	exit;
?>
