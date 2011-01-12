// 서비스 로그인
function infoProviderLogin(url, skin){
	// JS 사용을 알린다.
	url += '&js=1';

	// skin
	if (!skin) skin = 'default';
	url += '&skin=' + skin + '&info=1';

	// 윈도우 오픈
	window.open(url,'socialxeLogin','top=0, left=0, width=800, height=500');
}

// 로그인 후
function completeSocialxeInfoLogin(skin){
	var params = new Array();
	params['skin'] = skin;
	var response_tags = new Array('error','message','output');
	exec_xml('socialxe', 'procCompileInfo', params, replaceInfo, response_tags);
}

// 로그아웃
function infoProviderLogout(provider, skin){
	var params = new Array();
	params['js'] = 1;
	params['provider'] = provider;
	params['skin'] = skin;
	params['info'] = 1;
	var response_tags = new Array('error','message','output');
	exec_xml('socialxe', 'dispSocialxeLogout', params, replaceInfo, response_tags);
}

// 소셜 설정 초기화
function resetInfo(skin){
	var params = new Array();
	params['skin'] = skin;
	var response_tags = new Array('error','message','output');
	exec_xml('socialxe', 'procResetSocialInfo', params, replaceInfo, response_tags);
}

// 갱신
function replaceInfo(ret_obj){
	if (!ret_obj['output']) return;

	jQuery('.socialxe_info').html(ret_obj['output']);

	var params = new Array();
	params['skin'] = socialxe_skin;
	params['document_srl'] = socialxe_document_srl;
	params['list_count'] = socialxe_list_count;
	params['content_link'] = socialxe_content_link;
	params['comment_srl'] = socialxe_comment_srl;
	var response_tags = new Array('error','message','output');
	exec_xml('socialxe', 'procCompileList', params, null, response_tags);
}

// 대표계정 변경
function changeInfoMaster(provider, skin){
	var params = new Array();
	params['provider'] = provider;
	params['skin'] = skin;
	params['info'] = 1;
	var response_tags = new Array('error','message','output');
	exec_xml('socialxe', 'procSocialxeChangeMaster', params, replaceInfo, response_tags);
}

// 자동 로그인 키 얻기
function getInfoAutoLoginKey(url, skin){
	jQuery.getJSON(url + '&callback=?', function(json){
		var params = new Array();
		params['auto_login_key'] = json.auto_login_key;
		params['skin'] = skin;
		params['info'] = 1;
		var response_tags = new Array('error','message','output');
		exec_xml('socialxe', 'procSocialxeSetAutoLoginKey', params, replaceInfo, response_tags);
	});
}