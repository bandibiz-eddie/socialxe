<?php

// 서비스를 위한 클래스
class socialxeServerProvider {
	var $name;

	// 생성자
	function socialxeServerProvider($name, &$sessionManager){
		$this->name = $name;
		$this->session = $sessionManager;

		$this->lang->comment = array(
			'en' => 'comment',
			'ko' => '이곳의 댓글'
		);

		$this->lang->notify = array(
			'en' => 'comment',
			'ko' => '댓글이 달렸습니다'
		);
	}

	// 로그인 url을 얻는다(구현은 상속 클래스에서)
	function getLoginUrl(){
		return new Object();
	}

	// 콜백 처리(구현은 상속 클래스에서)
	function callback(){
		return new Object();
	}

	// 댓글 전송(구현은 상속 클래스에서)
	function send($comment, $access, $lang = 'en', $use_socialxe = false){
		return;
	}

	function getNotEncodedFullUrl() {
		$num_args = func_num_args();
		$args_list = func_get_args();
		$request_uri = Context::getRequestUri();
		if(!$num_args) return $request_uri;

		$url = Context::getUrl($num_args, $args_list, null, false);
		if(!preg_match('/^http/i',$url)){
			preg_match('/^(http|https):\/\/([^\/]+)\//',$request_uri,$match);
			$url = Context::getUrl($num_args, $args_list, null, false);
			return substr($match[0],0,-1).$url;
		}
		return $url;
	}

	function stop($msg){
		$result = new Object();
		$result->setError(-1);
		$result->setMessage($msg);
		return $result;
	}
}

?>
