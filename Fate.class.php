<?php defined('IN_FATE') or die('Access denied!');
		
			define('SYS_PATH',str_replace("\\",'/',dirname(__FILE__)));  //系统路径
			define('SYS_CORE_PATH',SYS_PATH.'/library/core');						 //系统核心类路径
			define('SYS_EXT_PATH',SYS_PATH.'/library/extension');				 //系统扩展类路径   
			define('DB_PATH',SYS_PATH.'/library/core/db');						   //系统数据库操作类路径
			define('CACHE_PATH',SYS_PATH.'/library/core/cache');				 //系统缓存类路径
	
			/**
		   * @brief 核心类
		   * @param $self 系统自身对象
		   * @param $app  应用对象
		   * @param $globalPath autoLoad时 自动检索的路径
		   * @param $globalClass 已经导入的类
		   **/		
   
			class Fate {
						
					private static $self = null;
					private static $app  = null;
					private static $globalPath = array('sys_core'=>SYS_CORE_PATH,'sys_ext'=>SYS_EXT_PATH,'sys_db'=>DB_PATH,'sys_cache'=>CACHE_PATH);
					private static $globalClass = array();
					
					/**
					 * @brief 系统初始化函数
					 **/
					public static function init(){
						
							if(is_null(self::$self)){
									self::$self = new Fate();
							}else{
								  throw new IException('System has been worked!');
							}
					}
					
					/**
					 * @brief 系统初始化函数
					 **/
					private function __construct(){
						
						$this->regAutoLoad();
						$this->run();
					}
					
					/**
					 * @brief 注册autoLoad 函数
					 **/
					public function regAutoLoad(){
						
						spl_autoload_register(array('self', 'autoLoad'));
					}	
					
					/**
					 * @brief 自动加载文件函数
					 * @param $className 类名
					 **/
				  public static function autoLoad($className){
				  	

								$classFile = '';
								
							  if(array_key_exists($className,self::$globalClass)){ //搜索全局类
					 
					  					$classFile = self::$globalClass[$className];
					  				
					  		}else{																							 //搜索全局路径
					  		
						  			foreach(self::$globalPath as $path){
						  				
											$classFile = $path.'/'.$className.'.class.php';

											if(is_file($classFile)){

												 self::$globalClass[$className] = $classFile;
												 break;	
											}else{
												$classFile =null;	
											}
										}
					  		}

					  		if(!empty($classFile)){
					  				require $classFile;
					  				return  $classFile;
					  		}else{
					  			  throw new IException($className.' not found');
					  		}					  	
				  }
				  
				  /**
				   * @brief 系统运行函数
				   **/
				  private function run(){
				  	
			  			$app = new IApp();
			  			$app->run();
				  }
				  
				  /** 
				   * @brief 设置系统加载文件路径
				   * @param $path 路径
				   **/
				  public static function setGlobalPath($path,$alias=''){
				  	
				  	 if(is_string($path)){
				  	 	
				  			if(strpos($path,'.')){
				  				
				  				 $pathArr = explode('.',$path);
				  				 foreach($pathArr as $k=>$v){
					  				 	if(isset(self::$globalPath[$v])){
					  				 		  $pathArr[$k] = self::$globalPath[$v]; 
					  				 	}
				  				 }
				  				 $path = implode('/',$pathArr);
				  			}
				  			
				  			if(is_dir($path)){
				  				array_unshift(self::$globalPath,$path);
				  			}
				  			
				  			if(!empty($alias)){
				  				 self::$globalPath[$alias] = self::$globalPath[0];
				  				 unset(self::$globalPath[0]);
				  			}
				  			
				  		}else if(is_array($path)){
				  			 
				  			 foreach($path as $k=>$v ){
										$alias = is_string($k)?$k:'';
				  					self::setGlobalPath($v,$alias);
				  			 }
				  		}
				  }
				  
				  /**
				   * @brief 返回includePath 数组
				   **/
				  public static function getGlobalPath(){
				  	
				  		return self::$golobalPath;
				  }
				
			}
			
?>