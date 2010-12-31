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

            // 사용할 서비스 설정
            $provider_list = $this->providerManager->getFullProviderList();
            foreach($provider_list as $provider){
                $tmp = 'select_service_' . $provider;
                if ($args->{$tmp} == 'Y'){
                    $args->select_service[$provider] = 'Y';
                }else{
                    $args->select_service[$provider] = 'N';
                }
                unset($args->{$tmp});
            }

            // module Controller 객체 생성하여 입력
            $oModuleController = &getController('module');
            $output = $oModuleController->insertModuleConfig('socialxe',$args);
            return $output;
        }

    }
?>
