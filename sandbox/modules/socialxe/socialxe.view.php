<?php

    class socialxeView extends socialxe {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        // 로그인 화면(oauth 시작)
        function dispSocialxeLogin(){
            // 크롤러면 실행하지 않는다...
            // 소셜XE 서버에 쓸데없는 요청이 들어올까봐...
            if (isCrawler()) return;

            $use_js = Context::get('js'); // JS 사용 여부
            $widget_skin = Context::get('skin'); // 위젯의 스킨명

            // 아무 것도 없는 레이아웃 적용
            $template_path = sprintf("%stpl/",$this->module_path);
            $this->setLayoutPath($template_path);
            $this->setLayoutFile("popup_layout");

            // JS 사용 여부 확인
            if ($use_js){
                // JS 사용 여부를 세션에 저장한다.
                $this->session->setSession('js', $use_js);
                $this->session->setSession('widget_skin', $widget_skin);

                // 로그인 안내 페이지 표시후 진행할 URL
                $url = getNotEncodedUrl('js', '', 'skin', '');
                Context::set('url', $url);

                // 로그인 안내 페이지 표시
                $this->setTemplatePath($template_path);
                $this->setTemplateFile('login');
                return;
            }

            $callback_query = Context::get('query'); // 인증 후 돌아갈 페이지 쿼리
            $provider = Context::get('provider'); // 서비스

            $this->session->setSession('callback_query', $callback_query);

            $output = $this->communicator->getLoginUrl($provider);
            if (!$output->toBool()) return $output;
            $url = $output->get('url');

            // 리다이렉트
            header('Location: ' .$url);
            Context::close();
            exit;
        }

        // 콜백 처리
        function dispSocialxeCallback(){
            $provider = Context::get('provider'); // 서비스
            $verifier = Context::get('verifier');
            $oSocialxeModel = &getModel('socialxe');

            // verifier가 없으면 원래 페이지로 돌아간다.
            if (!$verifier){
                $this->returnPage();
                return;
            }

            // 처리
            $output = $this->communicator->callback($provider, $verifier);
            if (!$output->toBool()) return $output;

            $this->returnPage();
        }

        // 로그아웃
        function dispSocialxeLogout(){
            $use_js = Context::get('js'); // JS 사용 여부
            $widget_skin = Context::get('skin'); // 위젯의 스킨명
            $query = urldecode(Context::get('query')); // 로그아웃 후 돌아갈 페이지 쿼리
            $provider = Context::get('provider'); // 서비스
            $oSocialxeController = &getController('socialxe');

            $output = $this->providerManager->doLogout($provider);
            $this->communicator->sendSession();
            if (!$output->toBool()) return $output;

            // JS 사용이면 XMLRPC 응답
            if ($use_js){
                Context::setRequestMethod('XMLRPC');

                // 입력창 컴파일
                $output = $oSocialxeController->_compileInput();
                $this->add('skin', $widget_skin);
                $this->add('output', $output);
            }

            // JS 사용이 아니면 돌아간다.
            else{
                $this->returnPage($query);
            }
        }

        // 원래 페이지로 돌아간다.
        function returnPage($query = null){
            // JS 사용이면 창을 닫는다.
            if ($this->session->getSession('js')){
                Context::set('skin', $this->session->getSession('widget_skin'));
                $this->session->clearSession('js');
                $this->session->clearSession('widget_skin');
                $template_path = sprintf("%stpl/",$this->module_path);
                $this->setTemplatePath($template_path);
                $this->setTemplateFile('completeLogin');
                return;
            }

            // 쿼리가 파라미터로 넘어왔으면 사용하고 아니면 세션을 사용
            if (empty($query)){
                $query = $this->session->getSession('callback_query');
                $this->session->clearSession('callback_query');
            }

            // XE주소
            $url = Context::getRequestUri();

            // SSL 항상 사용이 아니면 https를 http로 변경.
            // if(Context::get('_use_ssl') != 'always') {
                // $url = str_replace('https', 'http', $url);
            // }

            // 쿼리가 있으면 붙인다.
            if ($query)
                $url .= '?' . $query;

            header('Location: ' . $url);
            Context::close();
            exit;
        }

    }
?>
