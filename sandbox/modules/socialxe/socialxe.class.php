<?php

    require_once(_XE_PATH_.'modules/socialxe/sessionManager.php');
    require_once(_XE_PATH_.'modules/socialxe/providerManager.php');
    require_once(_XE_PATH_.'modules/socialxe/communicator.php');
    require_once(_XE_PATH_.'modules/socialxe/provider.class.php');
    require_once(_XE_PATH_.'modules/socialxe/provider.twitter.php');
    require_once(_XE_PATH_.'modules/socialxe/provider.me2day.php');
    require_once(_XE_PATH_.'modules/socialxe/provider.facebook.php');
    require_once(_XE_PATH_.'modules/socialxe/provider.yozm.php');
    require_once(_XE_PATH_.'modules/socialxe/socialcomment.item.php');

    class socialxe extends ModuleObject {

        var $hostname = 'socialxe.net';
        var $query = '/?module=socialxeserver&act=procSocialxeserverAPI';

        var $add_triggers = array(
            array('comment.deleteComment', 'socialxe', 'controller', 'triggerDeleteComment', 'after')
        );

        function socialxe(){
            // 설정 정보를 받아옴 (module model 객체를 이용)
            $oModuleModel = &getModel('module');
            $config = $oModuleModel->getModuleConfig('socialxe');

            if (!$config->server_hostname) $config->server_hostname = $this->hostname;
            if (!$config->server_query) $config->server_query = $this->query;
            if (!$config->use_ssl) $config->use_ssl = 'Y';
            if (!$config->hashtag) $config->hashtag = 'socialxe';
            $this->config = $config;

            // 세션 관리자
            $this->session = &socialxeSessionManager::getInstance();

            // 서비스 관리 클래스
            $this->providerManager = &socialxeProviderManager::getInstance($this->session);

            // 커뮤니케이터
            $this->communicator = &socialxeCommunicator::getInstance($this->session, $this->providerManager, $this->config);
        }

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            $oModuleController = &getController('module');

            // $this->add_triggers 트리거 일괄 추가
            foreach($this->add_triggers as $trigger) {
                $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
            }
        }

        /**
         * @brief 설치가 이상이 없는지 체크하는 method
         **/
        function checkUpdate() {
            $oModuleModel = &getModel('module');

            // $this->add_triggers 트리거 일괄 검사
            foreach($this->add_triggers as $trigger) {
                if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) return true;
            }

            return false;
        }

        /**
         * @brief 업데이트 실행
         **/
        function moduleUpdate() {
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');

            // $this->add_triggers 트리거 일괄 업데이트
            foreach($this->add_triggers as $trigger) {
                if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
                    $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
                }
            }

            return new Object(0, 'success_updated');
        }

        /**
         * @brief 캐시 파일 재생성
         **/
        function recompileCache() {
        }

        function getNotEncodedFullUrl() {
            $num_args = func_num_args();
            $args_list = func_get_args();
            $request_uri = Context::getRequestUri();
            if(!$num_args) return $request_uri;

            $url = Context::getUrl($num_args, $args_list, null, false);
            if(!preg_match('/^http/i',$url)){
                preg_match('/^(http|https):\/\/([^\/]+)\//',$request_uri,$match);
                $url = Context::getUrl($num_args, $args_list, null, false);
                return substr($match[0],0,-1).$url;
            }
            return $url;
        }
    }
?>
