<?php defined('IN_FATE') or die('Access denied!');
		
			/**
			 * @brief 应用类
			 * @param $debug            是否开启调试模式
			 * @param $config        		所有组件的配置文件数组
			 * @param $extensionPath 		应用扩展类路径
			 * @param $module   				应用加载对应模块
			 * @param $defaultControl   默认的控制器
			 * @param $defaultAction    默认的方法
			 * @param $timezone  				时间戳
			 * @param $charset   				字符集
			 * @param $language  				语言包
			 * @param $errorLevel       错误级别
			 * @param $objects          应用的所有对象集合
			 **/
	 
			class IApp {
				
					private  $debug=true;
					private  $extensionPath=array('app'=>APP_PATH,'appExt'=>'app.extension');
					private  $defaultControl ='home';
					private  $defaultAction  = 'index';
					private  $timeZone ='Asia/Shanghai';
					private  $charset = 'utf-8';
					private  $language = 'zh_cn';
					private  $errorLevel = E_ALL;
					
					private static $module='';
					private static $control;
					private static $action; 
					private static $controlPath;
					private static $modelPath;
					
					private static $config  = array();
				  private static $objects = array();
				  private static $imports = array();
				 	 
				  
					/**
					 * @brief 初始化函数
					 **/
					public function __construct(){
		
						 $this->loadConfig();
						 $this->initHandlers();
						 $this->initTimeZone();
						 Fate::setGlobalPath($this->extensionPath);
					}
					
					/**
					 * @brief 加载配置文件
					 **/				
					private function loadConfig(){
						
						$configFile = APP_PATH.'/config/config.php';
						
						if(is_file($configFile)){
							self::$config = require $configFile;
							foreach(self::$config['app'] as $key=>$value){
								if(property_exists($this,$key))
									$this->$key = $value;
							}	
							unset(self::$config['app']);
							self::$controlPath = APP_PATH.'/controllers/'; 
							self::$modelPath = APP_PATH.'/models/'; 
						}else{
							throw new IException('Configuration file not found！');
						}
						
					}
					
					/**
					 * @brief 初始化时间
					 **/
					private function initTimeZone(){
						
						 date_default_timezone_set($this->timeZone);
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
					public function errorHandler($level,$message,$file,$line,$content){
						
							if($this->debug){
								IError::display($level,$message,$file,$line,$content);
							}
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
					 * @brief 处理http请求
					 **/
					private function execRequest(){
						
						$url_config = self::config('url');
						$route = IUrl::init($url_config);
						$info = explode('/',trim($route,'/'));
						if(!empty($info)&&!empty($route)){
					
							if($this->isModule($info[0])){
								self::$module  = $info[0].'/';
								self::$control = !empty($info[1])? $info[1]:$this->defaultControl;
								self::$action  = !empty($info[2])? $info[2]:$this->defaultAction; 
							}else{
								self::$module  = '';
								self::$control = $info[0];
								self::$action  = !empty($info[1])? $info[1]:$this->defaultControl; 
							}
						}else{
							  self::$control = $this->defaultControl;
							  self::$action  = $this->defaultAction;
						}
					}
					
					/**
					 * @brief 执行应用
					 **/
					public function run(){
								
						 $this->execRequest();
						 
						 $controlName =  self::$control.'Control';
						 $action  = self::$action; 
						 $controlFile = self::$controlPath.self::$module.$controlName.'.class.php';
						 
					   if(is_file($controlFile) && ($control = self::object($controlName)) && method_exists($control,$action)){
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
						
						 return is_dir(self::$controlPath.$moduleName);
				  }
				  
				  /**
				   * @brief 获取应用配置文件
				   * @param $name 配置文件索引
				   **/
				  public static function config($name=''){
				  			
							$name = strtolower($name);
							$config = array();
							
						  if(empty($name)){
						  	 $config = self::$config;
						  }else{
						     $arr = explode('.',$name);
						     
						     if(count($arr)==2 && isset(self::$config[$arr[0]][$arr[1]])){
						     		 $config = self::$config[$arr[0]][$arr[1]];
						     }
						     if(count($arr)==1 && isset(self::$config[$arr[0]])){
						     		 $config = self::$config[$arr[0]];
						     }
						  }
						  								 
							return $config;
				  }
				  
				  /**
				   * @brief 获取应用对象
				   * @param $config 获取的对象配置
				   * @param $params 对象的初始化参数
				   **/
				  public static function object($config,$params=array()){

					  	//定义类名
							if(is_string($config)){
									$className = $config;
									$config = array();
							}else if(is_array){	
									$className = $config['class'];
							}
							
							//判断该类的对象是否已经存在该应用中
							if(isset(self::$objects[$className])){
								return self::$objects[$className];	
							}
							
							//判断是否是控制器
							$module = !empty($config['module'])? $config['module'].'/':'';
							if(substr($className,-7)=='Control'){
									$file = self::$controlPath.$module.$className.'.class.php';
							}
							
							//判断是否是模型类
							if(substr($className,-5)=='Model'){
									$file = self::$modelPath.$module.$className.'.class.php';
							}
							
							//判断文件是否存在
							if(isset($file) && is_file($file)){
									require $file;
							}
			
						  //调用autoLoad								
							if(class_exists($className)){
								$class=new ReflectionClass($className);
								$object=call_user_func(array($class,'newInstance'),$params);
								return self::$objects[$className] = $object;
							}
							
							return null;
				  }
				  
				  /**
				   * @brief 导入文件
				   **/
				  public static function import($name,$path='',$force=false){
				  	
				  		$className = explode('.',$name);
				  		
				  		if(isset(self::$imports[$className]) && $force==false){
				  			
				  				require self::$imports[$className];	
				  		}
		
							if(strpos($path,'.')){
								
									$globalPath = Fate::getGlobalPath();
									$pathArr = explode('.',$path);	
									
									foreach($pathArr as $k=>$v){
										$pathArr[$k] = isset($globalPath[$v])? $globalPath[$v]:$v; 
									}
									$path =  implode('/',$pathArr);
							}
							
							if(is_file($path.'/'.$name)){
								  self::$imports[$className] = $path.'/'.$name;
									require $path.'/'.$name;
							}else{
									$classFile = Fate::autoLoad($className);	
									self::$imports[$className] = $classFile;
							}
							
				  }								
			}

?>