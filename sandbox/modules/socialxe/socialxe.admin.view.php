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
            $oSocialxeModel = &getModel('socialxe');
            $config = $oSocialxeModel->getConfig();
            Context::set('config',$config);

            // 템플릿 파일 지정
            $this->setTemplatePath($this->module_path.'tpl');
            $this->setTemplateFile('index');
        }


    }
?>
