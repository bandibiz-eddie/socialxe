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

		// 클라이언트 추가/수정
		function procSocialxeserverAdminInsertClient(){
			$client_srl = Context::get('client_srl');
			$domain = Context::get('domain');

			// 도메인 확인
			$oSocialxeserverModel = &getModel('socialxeserver');
			$domain_array = explode(',', $domain);
			foreach($domain_array as &$val){
				$val = trim(str_replace(array('http://', 'www.'), '', $val));
				$output = $oSocialxeserverModel->isExsistDomain($val, $client_srl);
				if (!$output->toBool()) return $output;
				if ($output->get('result')) return $this->stop(Context::getLang('msg_exsist_domain') . '(' . $val . ')');
			}

			// 수정
			if ($client_srl){
				$args->client_srl = $client_srl;
				$args->domain = $domain;
				$output = executeQuery('socialxeserver.updateClient', $args);
			}

			// 추가
			else{
				// 클라이언트 토큰
				$token = md5($domain);

				// DB 입력
				$args->domain = $domain;
				$args->client_token = $token;
				$output = executeQuery('socialxeserver.insertClient', $args);
			}
			return $output;
		}

		// 클라이언트 선택 삭제
		function procSocialxeserverAdminDeleteCheckedClient(){
			// 선택된 글이 없으면 오류 표시
			$cart = Context::get('cart');
			if(!$cart) return $this->stop('msg_invalid_request');
			$client_srl_list= explode('|@|', $cart);
			$client_count = count($client_srl_list);
			if(!$client_count) return $this->stop('msg_invalid_request');

			$args->client_srls = implode(',', $client_srl_list);
			return executeQuery('socialxeserver.deleteClient', $args);
		}

    }
?>
