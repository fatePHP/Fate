<?php defined('IN_FATE') or die('Access denied!');
	
    /**
     * @brief HTTP异常状态类
     **/

    class IHttpException extends IException {

              public function display(){

                $httpCode  = $this->getCode();
                $errorData = $this->getMessage();
                $action = '_'.$httpCode;
                $this->$action();
              }

              public function _404(){
                      echo "404 NOT FOUND";
              }

    }

?>