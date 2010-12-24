<?php

    class socialxeserverAdminView extends socialxeserver {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 설정
         **/
        function dispSocialxeserverAdminConfig() {
            // 설정 정보를 받아옴 (module model 객체를 이용)
            $oModuleModel = &getModel('module');
            $config = $oModuleModel->getModuleConfig('socialxeserver');
            Context::set('config',$config);

            // 템플릿 파일 지정
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('index');
        }

        // 요즘 액세스 토큰 얻기
        function dispSocialxeserverAdminGetYozmAccessToken(){
            // 세션 세팅
            $this->session->setSession('yozmgetaccess', true);

            // 로그인 URL을 얻는다.
            unset($output);
            $output = $this->communicator->providerManager->getLoginUrl('yozm');
            if (!$output->toBool()) return $output;
            $url = $output->get('url');

            // 리다이렉트
            header('Location: ' . $url);
            Context::close();
            exit;
        }

        // 콜백
        function dispSocialxeserverAdminCallback(){
            $output = $this->communicator->access();
            Context::set('access_token', $output->get('access_token'));

            // 템플릿 파일 지정
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('yozmgetaccess');

            // HTML 형식
            Context::setRequestMethod('HTML');
        }
    }
?>
