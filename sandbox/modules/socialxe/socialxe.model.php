<?php

    class socialxeModel extends socialxe {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        // 제공 서비스
        function getProviderList(){
            return $this->providerManager->getProviderList();
        }

        // 로그인한 서비스 목록
        function loggedProviderList(){
            return $this->providerManager->getLoggedProviderList();
        }

        // 서비스 로그인 확인
        function isLogged($provider){
            return $this->providerManager->isLogged($provider);
        }

        // 해당 서비스의 로그인 아이디
        function getProviderID($provider){
            return $this->providerManager->getProviderID($provider);
        }

        // 대표 계정의 아이디
        function getID(){
            return $this->providerManager->getMasterProviderId();
        }

        // 대표 계정의 닉네임
        function getNickName(){
            return $this->providerManager->getMasterProviderNickName();
        }

        // 대표 계정의 프로필 이미지
        function getProfileImage(){
            return $this->providerManager->getMasterProviderProfileImage();
        }

        // 대표 계정
        function getMasterProvider(){
            return $this->providerManager->getMasterProvider();
        }

        // 소셜 서비스 페이지 링크
        function getAuthorLink($provider, $id){
            return $this->providerManager->getAuthorLink($provider, $id);
        }

        // 아이디를 받아 소셜 서비스의 리플 형식으로 반환
        function getReplyPrefix($provider, $id, $nick_name){
            return $this->providerManager->getReplyPrefix($provider, $id, $nick_name);
        }

        // 자동 로그인 키
        function getAutoLoginKey(){
            return $this->session->getSession('auto_login_key');
        }

        // 자동 로그인 키 요청 URL
        function getAutoLoginKeyUrl(){
            return $this->communicator->getAutoLoginKeyUrl();
        }

        // 댓글을 가져온다.
        function getCommentList($document_srl, $last_comment_srl = 0, $count = 10) {
            if (!$count) $count = 10;

            $result = new Object();

            $args->document_srl = $document_srl;
            $args->list_count = $count;

            // 전체 개수
            if (!$last_comment_srl){
                $output = executeQuery('socialxe.getCommentCount', $args);
                if (!$output->toBool()){
                    $result->add('list', array());
                    $result->add('total', 0);
                    return $result;
                }
                $total = $output->data->count;
            }

            // 정해진 수에 따라 목록을 구해옴
            if ($last_comment_srl){
                $args->last_comment_srl = $last_comment_srl;
            }
            $output = executeQueryArray('socialxe.getCommentPageList', $args);

            // 쿼리 결과에서 오류가 생기면 그냥 return
            if(!$output->toBool()){
                $result->add('list', array());
                $result->add('total', 0);
                return $result;
            }

            $comment_list = $output->data;
            if (!$comment_list) $comment_list = array();
            if (!is_array($comment_list)) $comment_list = array($comment_list);

            // 소셜 댓글 아이템을 생성
            $socialCommentList = array();
            foreach($comment_list as $comment){
                if (!$comment->comment_srl) continue;
                unset($oSocialComment);
                $oSocialComment = new socialCommentItem();
                $oSocialComment->setAttribute($comment);
                if($is_admin) $oSocialComment->setGrant();

                $socialCommentList[$comment->comment_srl] = $oSocialComment;
            }

            $result->add('list', $socialCommentList);
            $result->add('total', $total);

            return $result;
        }

        // 대댓글을 가져온다.
        function getSubCommentList($parent_srl, $page = 0, $count = 20) {
            if (!$count) $count = 20;

            $result = new Object();

            $args->parent_srl = $parent_srl;

            // 페이지가 없으면 제일 뒤 페이지를 구한다.
            if (!$page){
                $output = executeQuery('socialxe.getSubCommentCount', $args);
                if (!$output->toBool()){
                    $result->add('list', array());
                    $result->add('page_navigation', new PageHandler(0, 0, 0));
                    return $result;
                }
                $comment_count = $output->data->count;
                $page = (int)( ($comment_count-1) / $count) + 1;
            }

            // 정해진 수에 따라 목록을 구해옴
            $args->page = $page;
            $args->list_count = $count;
            $output = executeQueryArray('socialxe.getSubCommentPageList', $args);

            // 쿼리 결과에서 오류가 생기면 그냥 return
            if(!$output->toBool()) $output;

            $comment_list = $output->data;
            if (!$comment_list) $comment_list = array();
            if (!is_array($comment_list)) $comment_list = array($comment_list);

            // 소셜 댓글 아이템을 생성
            $socialCommentList = array();
            foreach($comment_list as $comment){
                if (!$comment->comment_srl) continue;
                unset($oSocialComment);
                $oSocialComment = new socialCommentItem();
                $oSocialComment->setAttribute($comment);
                if($is_admin) $oSocialComment->setGrant();

                $socialCommentList[$comment->comment_srl] = $oSocialComment;
            }

            // 대댓글 개수 세팅을 위해
            // 부모 댓글의 정보를 가져온다.
            unset($args);
            $args->comment_srl = $parent_srl;
            $output2 = executeQuery('socialxe.getSocialxe', $args);

            // 소셜 정보가 없으면 기존 댓글이다. 소셜 정보를 추가해준다.
            if (!$output2->data){
                executeQuery('socialxe.insertSocialxe', $args);
            }

            // 대댓글 개수가 다르면 업데이트한다.
            else if ($output2->data && $output2->data->sub_comment_count != $output->total_count){
                $args->sub_comment_count = $output->total_count;
                executeQuery('socialxe.updateSubCommentCount', $args);
            }


            $result->add('list', $socialCommentList);
            $result->add('page_navigation', $output->page_navigation);
            return $result;
        }

        // 하나의 특정 댓글을 가져온다.
        function getComment($document_srl, $comment_srl) {
            $args->comment_srl = $comment_srl;
            $args->document_srl = $document_srl;

            $result = new Object();

            // 전체 개수
            $output = executeQuery('socialxe.getCommentCount', $args);
            if (!$output->toBool()){
                $result->add('list', array());
                $result->add('total', 0);
                return $result;
            }
            $total = $output->data->count;

            // 댓글을 가져온다
            $output = executeQueryArray('socialxe.getComment', $args);

            // 쿼리 결과에서 오류가 생기면 그냥 return
            if (!$output->toBool()){
                $result->add('list', array());
                $result->add('total', 0);
                return $result;
            }

            $comment_list = $output->data;
            if (!$comment_list) $comment_list = array();
            if (!is_array($comment_list)) $comment_list = array($comment_list);

            // 소셜 댓글 아이템을 생성
            $socialCommentList = array();
            foreach($comment_list as $comment){
                if (!$comment->comment_srl) continue;
                unset($oSocialComment);
                $oSocialComment = new socialCommentItem();
                $oSocialComment->setAttribute($comment);
                if($is_admin) $oSocialComment->setGrant();

                $socialCommentList[$comment->comment_srl] = $oSocialComment;
            }

            $result->add('total', $total);
            $result->add('use_comment_srl', true);

            // 결과가 없으면 그냥 전체 댓글 리스트를 반환한다.
            if (!count($socialCommentList))
                return $this->getCommentList($document_srl);
            else{
                $result->add('list', $socialCommentList);
                return $result;
            }
        }
    }
?>
