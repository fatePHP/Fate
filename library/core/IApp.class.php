<?php  defined('IN_FATE') or die('Access denied!');

        defined('APP_PATH') or define('APP_PATH',str_replace('\\','/',dirname($_SERVER['SCRIPT_FILENAME'])));
        defined('MODEL_PATH') or define('MODEL_PATH',APP_PATH.'/models/');
        defined('CONTROL_PATH') or define('CONTROL_PATH',APP_PATH.'/controllers/');
       
        /**
         * @brief 应用类
         * @param $debug            是否开启调试模式
         * @param $config           配置文件数组
         * @param $extensionPath    应用扩展类路径
         * @param $module   	     应用加载对应模块
         * @param $control          默认的控制器
         * @param $action           默认的方法
         * @param $timezone         时间戳
         * @param $charset   	     字符集
         * @param $language  	     语言包
         * @param $errorLevel       错误级别
         **/
	 
         class IApp extends IComponentManager{
				
            protected  $debug=true;
            protected  $extensionPath=array('app'=>APP_PATH,'app_model'=>MODEL_PATH,'app_control'=>CONTROL_PATH,'app_ext'=>'app.extensions');
            protected  $timeZone ='Asia/Shanghai';
            protected  $charset = 'utf-8';
            protected  $language = 'zh_cn';
            protected  $errorLevel = E_ALL;	
            protected  $module='';
            protected  $control='home';
            protected  $action='index'; 
            protected  $controlPath = CONTROL_PATH;
            protected  $modelPath = MODEL_PATH;
            protected  $config  = array(); 
	 	 		  
            /**
             * @brief 应用初始化函数
             **/
            public function __construct(){
                
                $this->initConfig();
                $this->initHandlers();
                $this->initTimeZone();
                $this->initGlobalPath();
                $this->initComponent();
            }
					
            /**
             * @brief 初始化所有组件的配置文件
             **/				
            private function initConfig(){

                $mainConfig = APP_PATH.'/config/main.php';
                $componentConfig = APP_PATH.'/config/component.php';

                if(!is_file($mainConfig)|| !is_file($componentConfig))
                    die('Configuration file not found !');

                $this->config['main']= require $mainConfig;
                $this->config['component'] = require $componentConfig;
                foreach($this->config['main'] as $key=>$value){
                    if(is_array($this->$key)){
                          if(is_array($value)){
                            $this->$key = array_merge($this->$key,$value);
                          }
                    }else{
                          $this->$key = $value;
                    }
                }	
            }
					            
            /**
             * @brief 初始化句柄
             **/
            private function initHandlers(){

               set_error_handler(array($this,'errorHandler'),$this->errorLevel); 
               set_exception_handler(array($this,'exceptionHandler'));
            }
					
            /** 
             * @brief 错误处理句柄
             **/
            public  function errorHandler($level,$message,$file,$line,$content){

                if($this->debug)
                     IError::display($level,$message,$file,$line,$content);
            }
					 
            /**
             * @brief 异常处理句柄
             **/
            public function exceptionHandler($e){

                    if($this->debug || $e instanceof IHttpException ){
                      $e->display();
                    }
            }
            
            /**
             * @brief 初始化时间
             **/
            private function initTimeZone(){

                date_default_timezone_set($this->timeZone);
            }
            
	    /**
             * @brief 添加项目应用的环境路径  
             */
            private function initGlobalPath(){
                
                 Fate::setGlobalPath($this->extensionPath);
            }
            
            /** 
             * @brief 处理http请求
             **/
            private function execRequest(){
                                                          
                $url_route = $this->url->parseUrlRules()->parseUrl();
                $info = explode('/',trim($url_route,'/'));
                if(!empty($info[0]) && !empty($url_route)){
                    if($this->isModule($info[0])){
                        $this->module  = $info[0];
                        $this->control = !empty($info[1])? $info[1]:$this->control;
                        $this->action  = !empty($info[2])? $info[2]:$this->action; 
                    }else{
                        
                        $this->control = $info[0];
                        $this->action  = !empty($info[1])? $info[1]:$this->action; 
                    }
                }
            }
					
            /**
             * @brief 执行应用
             **/
            public function run(){
               $this->execRequest();
               $action  = $this->action; 
               $controlFile = $this->controlPath.$this->module.'/'.$this->control.'Control.class.php';
               if(is_file($controlFile) && ($control = $this->control($this->control)) && method_exists($control,$action)){              
                      $control->beginAction();
                      call_user_func(array($control,$action));
                      $control->endAction(); 	 	
               }else{
                      throw new IHttpException('404 not found!',404);
               }   
            }
					
            /**
             * @brief 判断是否为项目模块
             **/		 
            private function isModule($moduleName){
                
                 return !empty($moduleName) && is_dir($this->controlPath.$moduleName);
            }
				  
            /**
             * @brief 获取应用配置文件
             * @param $name 配置文件索引
             **/
            public function config($name=''){

                $name = strtolower($name);
                $config = array();

                if(empty($name)){
                       $config = $this->config;
                }else{
                   $arr = explode('.',$name);

                   if(count($arr)==2 && isset($this->config[$arr[0]][$arr[1]])){
                        $config = $this->config[$arr[0]][$arr[1]];
                   }
                   if(count($arr)==1 && isset($this->config[$arr[0]])){
                        $config = $this->config[$arr[0]];
                   }
                }

                return $config;
            }
            
            /**
             * @brief 获取应用中的模型 
             **/
            public function model($name,$module=''){
                   
                   $name = $name.'Model';
                   
                   if($module === false){
                        $flag = Fate::import( $this->modelPath.$name,false);
                   }else{
                   
                        if(!empty($module)){
                            if(!$this->isModule($module))
                                throw new IException("$module is not an module in this application !");
                        }else{
                            $module = $this->module;
                        }
                        $flag = Fate::import( $this->modelPath.$module.'/'.$name,false);
                   }

                    return $flag? new $name:false;
            }
            
            /**
             * @brief 获取应用中的控制器
             **/
            public function control($name,$module=''){
                
                   $name = $name.'Control';
                   if($module === false){
                       
                        Fate::import( $this->controlPath.$name,true);
                   }else{
                        if(!empty($module)){
                            if(!$this->isModule($module))
                                throw new IException("$module is not an module in this application !");
                        }else{
                            $module = $this->module;
                        }
                            
                        Fate::import( $this->controlPath.$module.'/'.$name,true);
                   }
                   
                   return new $name;
            }
            
            public function getTimeZone(){
                
                  return $this->timeZone;
            }
            
            public function setTimeZone($value){
                
                  $this->timeZone = $value;
            }
            
            public function getControl(){
                    
                 return  $this->control;
            }
            
            public function getAction(){
                
                return $this->action;
            }
            
            public function getModule(){
                
                return $this->module;
            }
				 

   }

?>