<?php

    class socialxeserverAdminController extends socialxeserver {

        /**
         * @brief 초기화
         **/
        function init() {
        }

        /**
         * @brief 설정
         **/
        function procSocialxeserverAdminInsertConfig() {
            // 기본 정보를 받음
            $args = Context::getRequestVars();

            // module Controller 객체 생성하여 입력
            $oModuleController = &getController('module');
            $output = $oModuleController->insertModuleConfig('socialxeserver',$args);
            return $output;
        }
        
    }
?>
