<?php

	class socialxeserverController extends socialxeserver {

		/**
		* @brief 초기화
		**/
		function init() {
		}

		// API 요청 처리
		function procSocialxeserverAPI(){
			// 모드에 따라 처리
			$mode = Context::get('mode');
			$output = $this->communicator->procAPI($mode);
			//if (!$output->toBool()) return $output;

			$this->setError($output->getError());
			$this->setMessage($output->getMessage());
			$this->adds($output->getObjectVars());
			return $this;
		}

		// 콜백 처리
		function procSocialxeserverCallback(){
			$output = $this->communicator->procAPI('callback');
			//if (!$output->toBool()) return $output;

			$this->setError($output->getError());
			$this->setMessage($output->getMessage());
			$this->adds($output->getObjectVars());
			return $this;
		}

		// 클라이언트 추가/수정
		function procSocialxeserverInsertClient(){
			if(!$this->grant->register) return $this->stop('msg_not_permitted');

			$logged_info = Context::get('logged_info');
			if (!$logged_info->member_srl) return $this->stop('msg_not_permitted');

			return $this->_insertClient($logged_info->member_srl);
		}

		function _insertClient($member_srl = null){
			$client_srl = Context::get('client_srl');
			$domain = Context::get('domain');

			// 도메인 확인
			$oSocialxeserverModel = &getModel('socialxeserver');
			$domain_array = explode(',', $domain);
			foreach($domain_array as $name => $val){
				$domain_array[$name] = trim(str_replace(array('http://', 'www.'), '', $val));
				$output = $oSocialxeserverModel->isExsistDomain($domain_array[$name], $client_srl);
				if (!$output->toBool()) return $output;
				if ($output->get('result')) return $this->stop(Context::getLang('msg_exsist_domain') . '(' . $domain_array[$name] . ')');
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
				$args->member_srl = $member_srl;
				$output = executeQuery('socialxeserver.insertClient', $args);
			}
			return $output;
		}

		// 클라이언트 삭제
		function procSocialxeserverDeleteCheckedClient(){
			if(!$this->grant->register) return $this->stop('msg_not_permitted');

			// 선택된 글이 없으면 오류 표시
			$cart = Context::get('cart');
			if(!$cart) return $this->stop('msg_invalid_request');
			$client_srl_list= explode('|@|', $cart);
			$client_count = count($client_srl_list);
			if(!$client_count) return $this->stop('msg_invalid_request');

			// 선택된 클라이언트가 본인의 클라이언트인지 한번 더 확인
			$logged_info = Context::get('logged_info');
			$args->client_srls = $client_srl_list;
			$output = executeQueryArray('socialxeserver.getClients', $args);
			debugPrint($output);
			foreach($output->data as $client){
				if ($client->member_srl != $logged_info->member_srl)	return $this->stop('msg_not_permitted');
			}

			$args->client_srls = implode(',', $client_srl_list);
			return executeQuery('socialxeserver.deleteClient', $args);
		}

		// 회원 탈퇴 트리거
		function triggerDeleteMember(&$member_info){
			// 회원과 연결된 클라이언트를 삭제한다.
			$args->member_srl = $member_info->member_srl;
			$output = executeQuery('socialxeserver.deleteClientByMemberSrl', $args);
			return $output;
		}
	}
?>
