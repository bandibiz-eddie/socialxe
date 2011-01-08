<?php

    require_once(_XE_PATH_.'modules/socialxe/sessionManager.php');
    require_once(_XE_PATH_.'modules/socialxe/providerManager.php');
    require_once(_XE_PATH_.'modules/socialxe/communicator.php');
    require_once(_XE_PATH_.'modules/socialxe/provider.class.php');
    require_once(_XE_PATH_.'modules/socialxe/provider.xe.php');
    require_once(_XE_PATH_.'modules/socialxe/provider.twitter.php');
    require_once(_XE_PATH_.'modules/socialxe/provider.me2day.php');
    require_once(_XE_PATH_.'modules/socialxe/provider.facebook.php');
    require_once(_XE_PATH_.'modules/socialxe/provider.yozm.php');
    require_once(_XE_PATH_.'modules/socialxe/socialcomment.item.php');

    class socialxe extends ModuleObject {

        var $hostname = 'socialxe.net';
        var $query = '/?module=socialxeserver&act=procSocialxeserverAPI';

        var $action_forwards = array(
            array('socialxe', 'view', 'dispSocialxeTextyleTool')
        );

        var $add_triggers = array(
            array('comment.deleteComment', 'socialxe', 'controller', 'triggerDeleteComment', 'after'),
            array('textyle.getTextyleCustomMenu', 'socialxe', 'controller', 'triggerGetTextyleCustomMenu', 'after')
        );

        var $add_column = array(
            array('socialxe', 'social_nick_name', 'varchar', 255)
        );

        function socialxe(){
            // 세션 관리자
            $this->session = &socialxeSessionManager::getInstance();

            // 서비스 관리 클래스
            $this->providerManager = &socialxeProviderManager::getInstance($this->session);

            // 환경 설정
            $this->config = $this->getConfig();

            // 환경 설정값을 서비스 관리 클래스에 세팅
            $this->providerManager->setConfig($this->config);

            // 커뮤니케이터
            $this->communicator = &socialxeCommunicator::getInstance($this->session, $this->providerManager, $this->config);
        }

        /**
         * @brief 설치시 추가 작업이 필요할시 구현
         **/
        function moduleInstall() {
            $oModuleController = &getController('module');

            // aciton forward 일괄 추가
            foreach($this->action_forwards as $item) {
                $oModuleController->insertActionForward($item[0], $item[1], $item[2]);
            }

            // $this->add_triggers 트리거 일괄 추가
            foreach($this->add_triggers as $trigger) {
                $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
            }
        }

        /**
         * @brief 설치가 이상이 없는지 체크하는 method
         **/
        function checkUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');

            // action forward 일괄 체크
            foreach($this->action_forwards as $item) {
                if(!$oModuleModel->getActionForward($item[2])) return true;
            }

            // $this->add_triggers 트리거 일괄 검사
            foreach($this->add_triggers as $trigger) {
                if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) return true;
            }

            // $this->add_column 컴럼 일괄 검사
            foreach($this->add_column as $column){
                if(!$oDB->isColumnExists($column[0], $column[1])) return true;
            }

            return false;
        }

        /**
         * @brief 업데이트 실행
         **/
        function moduleUpdate() {
            $oDB = &DB::getInstance();
            $oModuleModel = &getModel('module');
            $oModuleController = &getController('module');

            // action forward 일괄 업데이트
            foreach($this->action_forwards as $item) {
                if(!$oModuleModel->getActionForward($item[2])) {
                    $oModuleController->insertActionForward($item[0], $item[1], $item[2]);
                }
            }

            // $this->add_triggers 트리거 일괄 업데이트
            foreach($this->add_triggers as $trigger) {
                if(!$oModuleModel->getTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4])) {
                    $oModuleController->insertTrigger($trigger[0], $trigger[1], $trigger[2], $trigger[3], $trigger[4]);
                }
            }

            // $this->add_column 컬럼 일괄 업데이트
            foreach($this->add_column as $column){
                if(!$oDB->isColumnExists($column[0], $column[1])) $oDB->addColumn($column[0], $column[1], $column[2], $column[3]);
            }

            return new Object(0, 'success_updated');
        }

        /**
         * @brief 캐시 파일 재생성
         **/
        function recompileCache() {
        }

        // 환경설정
        function getConfig(){
			// 전역 설정에 있으면 그걸 리턴~
			if ($GLOBALS['socialxe_config']) return $GLOBALS['socialxe_config'];

            // 설정 정보를 받아옴 (module model 객체를 이용)
            $oModuleModel = &getModel('module');

            // document_srl이 있으면 해당 글의 모듈 정보를...
            $document_srl = Context::get('document_srl');
			$module_info = null;
            if ($document_srl){
                $module_info = $oModuleModel->getModuleInfoByDocumentSrl($document_srl);
				$oModuleModel->syncModuleToSite($module_info);
			}

			// module_info를 못 얻었으면
			if (!$module_info){
				$module_info = Context::get('site_module_info');
			}

			// 우선 기본 사이트 설정을 얻는다.
            $default_config = $oModuleModel->getModuleConfig('socialxe');

            if (!$default_config->server_hostname) $default_config->server_hostname = $this->hostname;
            if (!$default_config->server_query) $default_config->server_query = $this->query;
            if (!$default_config->use_ssl) $default_config->use_ssl = 'Y';
            if (!$default_config->hashtag) $default_config->hashtag = 'socialxe';

            $provider_list = $this->providerManager->getFullProviderList();
            foreach($provider_list as $provider){
                if (!$default_config->select_service[$provider])
                    $default_config->select_service[$provider] = 'Y';
            }

			// site_srl이 없으면 기본 설정을 사용.
			if (!$module_info->site_srl){
				$config = $default_config;
			}

			// site_srl이 있으면 해당 사이트 설정을 사용
			else{
				$config = $oModuleModel->getModulePartConfig('socialxe', $module_info->site_srl);

				if (!$config->server_hostname) $config->server_hostname = $this->hostname;
				if (!$config->server_query) $config->server_query = $this->query;
				if (!$config->use_ssl) $config->use_ssl = 'Y';
				if (!$config->hashtag) $config->hashtag = 'socialxe';

				foreach($provider_list as $provider){
					if (!$config->select_service[$provider])
						$config->select_service[$provider] = 'Y';
				}

				// 별도 도메인을 사용하지 않으면 서버 관련 설정은 기본 사이트 설정을 따른다.
				if (strpos($module_info->domain, '.') === false){
					$config->server_hostname = $default_config->server_hostname;
					$config->server_query = $default_config->server_query;
					$config->use_ssl = $default_config->use_ssl;
					$config->use_default = true;
				}
			}

			$GLOBALS['socialxe_config'] = $config;

            return $config;
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
