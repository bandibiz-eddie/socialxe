<?php

// 트위터 라이브러리 로드
require_once(_XE_PATH_.'modules/socialxeserver/twitteroauth/twitteroauth.php');
        
// 트위터를 위한 클래스
class socialxeServerProviderTwitter extends socialxeServerProvider{
    
    // 인스턴스
    function getInstance(&$sessionManager, $consumer_key, $consumer_key_secret){
        static $instance;
        if (!isset($instance)) $instance = new socialxeServerProviderTwitter($sessionManager, $consumer_key, $consumer_key_secret);
        return $instance;
    }
    
    // 생성자
    function socialxeServerProviderTwitter(&$sessionManager, $consumer_key, $consumer_key_secret){
        parent::socialxeServerProvider('twitter', $sessionManager);
        $this->consumer_key = $consumer_key;
        $this->consumer_key_secret = $consumer_key_secret;
        $this->oauth_callback = $this->getNotEncodedFullUrl('', 'module', 'socialxeserver', 'act', 'procSocialxeserverCallback', 'provider', 'twitter');
    }
    
    // 로그인 url을 얻는다.
    function getLoginUrl(){        
        // 트위터 oauth 객체 생성
        $connection = new TwitterOAuth($this->consumer_key, $this->consumer_key_secret);

        // 요청 토큰을 생성
        $request_token = $connection->getRequestToken($this->oauth_callback);

        // 요청 토큰을 세션에 저장한다.
        $session['oauth_token'] = $token = $request_token['oauth_token'];
        $session['oauth_token_secret'] = $request_token['oauth_token_secret'];
        $this->session->setSession('twitter', $session);
        
        
        // 요청 실패 확인
        if ($connection->http_code == 200){
            $url = $connection->getAuthorizeURL($token);
            
            $result = new Object();
            $result->add('url', $url);
            
            return $result;
        }else{
            return $this->stop('msg_error_twitter');
        }
    }
    
    // 콜백 처리
    function callback(){
        $oauth_token = Context::get('oauth_token');
        $oauth_verifier = Context::get('oauth_verifier');

        // 로그인 취소했으면 이전 페이지로 돌아간다.
        if (Context::get('denied')){
            $this->session->clearSession('twitter');
            return new Object();
        }
        
        // 세션
        $session = $this->session->getSession('twitter');
        
        // 이전 토큰과 일치하는지 확인
        if ($oauth_token != $session['oauth_token']){
            // 일치하지 않으면 세션 말소
            $this->session->clearSession('twitter');
            return $this->stop('msg_old_token');
        }
        
        // 트위터 oauth 객체 생성
        $connection = new TwitterOAuth($this->twitter_consumer_key, $this->twitter_consumer_key_secret, $session['oauth_token'], $session['oauth_token_secret']);
        
        // 액세스 토큰 요청
        $access_token = $connection->getAccessToken($oauth_verifier);
        
        // 요청 토큰은 이제 필요없다.
        $this->session->clearSession('twitter');
        
        // 요청 성공 체크
        if ($connection->http_code == 200){
            // 사용자 정보도 받아서 저장해 놓는다.
            $credentials = $connection->get('account/verify_credentials');
            
            // 액세스 토큰과 사용자 정보를 묶는다.
            $info['access'] = $access_token;
            $info['account'] = $credentials;
            
            $result = new Object();
            $result->add('info', $info);
            return $result;
        }else{
            return $this->stop('msg_error_twitter');
        }
    }
    
    // 댓글 전송
    function send($comment, $access){
        $result = new Object();
        
        // 트위터 oauth 객체 생성
        $connection = new TwitterOAuth($this->consumer_key, $this->consumer_key_secret, $access->oauth_token, $access->oauth_token_secret);
        
        // 내용 준비
        $content = ' ' . $comment->content_link . ' #' . $comment->hashtag;
        
        // 얼마만큼의 길이를 사용할 수 있는지 확인
        $max_length = 140 - mb_strlen($content, 'UTF-8');
        
        // 실제 내용을 준비
        $content2 = $comment->content;
        if ($comment->content_title){
            $content2 = '「' . $comment->content_title . '」' . $content2;
        }

        // 부모 댓글이 트위터면 멘션 처리
        if ($comment->parent && $comment->parent->provider == 'twitter'){
            $content2 = '@' . $comment->parent->id . ' ' . $content2;
            $reply_id = $comment->parent->comment_id;
            $result->add('reply', true);
        }
        
        // 내용 길이가 최대 길이를 넘는지 확인
        if (mb_strlen($content2, 'UTF-8') > $max_length){
            // 말줄임을 위한 3자를 남기고 내용을 자른다.
            $content = mb_substr($content2, 0, $max_length-3, 'UTF-8') . '...' . $content;
        }else{
            $content = $content2 . $content;
        }
        
        // 댓글 전송
        
        // 부모 댓글이 트위터면 멘션 처리
        if ($reply_id){
            $output = $connection->post('statuses/update', array('in_reply_to_status_id' => $reply_id, 'status' => $content));
        }else{
            $output = $connection->post('statuses/update', array('status' => $content));
        }
        
        $result->add('result', $output);
        return $result;
    }
}

?>