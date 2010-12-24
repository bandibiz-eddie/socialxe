<?php

    class socialxeAdminController extends socialxe {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 설정
         **/
        function procSocialxeAdminInsertConfig() {
            // 기본 정보를 받음
            $args = Context::getRequestVars();

            if ($args->use_ssl != "Y") $args->use_ssl = "N";
            
            // module Controller 객체 생성하여 입력
            $oModuleController = &getController('module');
            $output = $oModuleController->insertModuleConfig('socialxe',$args);
            return $output;
        }
        
    }
?>
