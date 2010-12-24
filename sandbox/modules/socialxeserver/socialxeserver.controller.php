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
    }
?>
