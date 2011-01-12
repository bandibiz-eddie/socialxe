<?php

    class socialxeAdminView extends socialxe {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 설정
         **/
        function dispSocialxeAdminConfig() {
            // 설정 정보를 받아옴
            Context::set('config',$this->config);

            // 서비스 목록
            $provider_list = $this->providerManager->getFullProviderList();
            Context::set('provider_list', $provider_list);

			// 스킨 리스트
			$oModuleModel = &getModel('module');
			$skin_list = $oModuleModel->getSkins($this->module_path);
			Context::set('skin_list', $skin_list);

            // 템플릿 파일 지정
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('index');
        }


    }
?>
