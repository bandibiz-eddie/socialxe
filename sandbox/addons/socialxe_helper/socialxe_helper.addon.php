<?php
	if(!defined("__ZBXE__")) exit();

	// 비로그인 사용자면 중지
	$logged_info = Context::get('logged_info');
	if(!$logged_info) return;

	Context::loadLang('./addons/socialxe_helper/lang');

	// 팝업 및 회원정보 보기에 소셜 메뉴 추가.
	if($called_position == 'before_module_init' && $this->module != 'member'){
		// 회원 로그인 정보에 소셜 메뉴 추가
		$oMemberController = &getController('member');
		$oMemberController->addMemberMenu('dispSocialxeSocialInfo', 'cmd_config_social');
	}

	// 사용자 이름 클릭 시 팝업메뉴에 메뉴 추가.
	elseif($called_position == 'before_module_proc' && $this->act == 'getMemberMenu'){
		$oMemberController = &getController('member');
		$member_srl = Context::get('target_srl');
		$mid = Context::get('cur_mid');

		// 자신이라면 설정 추가
		if ($logged_info->member_srl == $member_srl){
			$oMemberController->addMemberPopupMenu(getUrl('', 'mid', $mid, 'act', 'dispSocialxeSocialInfo'), 'cmd_config_social', './modules/member/tpl/images/icon_view_info.gif', 'self');
		}

		// 다른 사람이면 보기 추가
		else{
			$oMemberController->addMemberPopupMenu(getUrl('', 'mid', $mid, 'act', 'dispSocialxeSocialInfo', 'member_srl', $member_srl), 'cmd_view_social_info', './modules/member/tpl/images/icon_view_info.gif', 'self');
		}
	}
?>
