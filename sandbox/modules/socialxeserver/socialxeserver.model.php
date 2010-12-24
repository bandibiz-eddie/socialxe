<?php

    class socialxeserverModel extends socialxeserver {

        /**
         * @brief 초기화
         **/
        function init() {
        }
        
        // 환경설정
        function getConfig(){
            // 설정 정보를 받아옴 (module model 객체를 이용)
            $oModuleModel = &getModel('module');
            return $config = $oModuleModel->getModuleConfig('socialxeserver');
        }
    }
?>
