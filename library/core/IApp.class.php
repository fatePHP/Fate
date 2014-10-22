<?php  defined('IN_FATE') or die('Access denied!');
       
        /**
         * @brief 应用类
         * @param $debug            是否开启调试模式
         * @param $config           配置文件数组
         * @param $module   	     应用加载对应模块
         * @param $control          默认的控制器
         * @param $action           默认的方法
         * @param $timezone         时间戳
         * @param $charset   	     字符集
         * @param $language  	     语言包
         * @param $errorLevel       错误级别
         * @param $configPath       应用配置文件目录
         * @param $cachePath        应用缓存文件目录
         * @param $controlPath      应用控制器路径
         * @param $modelPath        应用模型路径
         * @param $extensionPath    应用扩展路径
         * @param $themePath        应用模板主题路径
         * @param $globalPath       应用全局路径数组
         **/
	 
         class IApp extends IComponentManager{
				
            protected  $debug=true;
                        
            protected  $timeZone ='Asia/Shanghai';
            protected  $charset = 'utf-8';
            protected  $language = 'zh_cn';
            protected  $errorLevel = E_ALL;	
            protected  $module='';
            protected  $control='home';
            protected  $action='index'; 
            protected  $config  = array(); 
            
            private    $configPath;
            protected  $cachePath;
            protected  $controlPath ;
            protected  $modelPath;
            protected  $extensionPath;
            protected  $themePath;
            protected  $globalPath;
	 	 		  
            /**
             * @brief 初始化函数
             **/
            public function __construct(){
                
                $this->initEnvironment();
                $this->initConfig();
                $this->initHandlers();
                $this->initTimeZone();
                $this->initGlobalPath();
                $this->initComponent();
            }
            
            /**
             * @brief 初始化应用环境
             */
            private function initEnvironment(){
                
                $app_path = str_replace('\\','/',dirname($_SERVER['SCRIPT_FILENAME']));
                
                $this->configPath = $app_path.'/config/';
                $this->cachePath = $app_path.'/cache/';
                $this->controlPath = $app_path.'/controllers/';
                $this->modelPath = $app_path.'/models/';
                $this->extensionPath = $app_path.'/extensions/';
                $this->themePath = $app_path.'/themes/';
                $this->globalPath = 
                             array( 
                                'app_model'=>$this->modelPath,
                                'app_control'=>$this->controlPath,
                                'app_ext'=>$this->extensionPath
                              );
            }
					
            /**
             * @brief 初始化所有组件的配置文件
             **/				
            private function initConfig(){

                $mainConfig = $this->configPath.'main.php';
                $componentConfig = $this->configPath.'component.php';

                if(!is_file($mainConfig)|| !is_file($componentConfig))
                    die('Configuration file not found !');

                $this->config['main']= require $mainConfig;
                $this->config['component'] = require $componentConfig;
                
                foreach($this->config['main'] as $key=>$value){
                    if(is_array($this->$key)){
                          if(is_array($value))
                            $this->$key = array_merge($this->$key,$value);
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
                
                 Fate::setGlobalPath($this->globalPath);
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
             * @brief 执行
             **/
            public function run(){
               $this->execRequest();
               $action  = $this->action; 
               $controlFile = $this->controlPath.$this->module.'/'.$this->control.'Control.class.php';
               if(is_file($controlFile) && ($control = $this->control($this->control)) && method_exists($control,$action))               {              
                      $control->beginAction();
                      call_user_func(array($control,$action));
                      $control->endAction(); 	 	
               }else{
                    if(!$this->debug)
                      throw new IHttpException('404 not found!',404);
               }   
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
             * @param $name   模型名称
             * @param $module 模块
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
             * @param $name 控制器名称
             * @param $module 模块名称
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
            
            /**
             * @brief 返回时区
             * @return type
             */
            public function getTimeZone(){
                
                  return $this->timeZone;
            }
            
            /**
             * @brief 设置时区
             * @param $value 时区
             * @return type
             */
            public function setTimeZone($value){
                
                  $this->timeZone = $value;
            }
            /**
             * @brief 返回模板主题路径
             */
            public function getThemePath(){
                 
                   return $this->themePath;
            }
            
            /**
             * @brief 返回缓存路径
             */
            public function getCachePath(){
                
                    return $this->cachePath; 
            }
            
            /**
             * @brief 判断是否为项目模块 存在问题
             * @param $moduleName 模块名称
             **/		 
            private function isModule($moduleName){
                
                 return !empty($moduleName) && is_dir($this->controlPath.$moduleName);
            }
            
            /**
             * @brief 返回当前模块名称
             * @return type
             */
            public function getModule(){
                
                return $this->module;
            }
            
            /**
             * @brief 返回当前控制器名称
             * @return type
             */
            public function getControl(){
                    
                 return  $this->control;
            }
            
            /**
             * @brief 返回当前执行操作名称
             * @return type
             */
            public function getAction(){
                
                return $this->action;
            }
         				 
   }

?>