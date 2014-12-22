<?php defined('IN_FATE') or die('Access denied!');
			
    /**
     * @brief 核心类
     * @param $inited        初始化标识
     * @param $self          系统自身对象
     * @param $app           应用对象
     * @param $globalPath    自动检索的路径
     * @param $globalClass   全局类
     * @param $globalPointer 全局文件指针
     * @param $globalObject  全局对象
     **/		
   
    class Fate {
            
            private static $inited = false;
            private static $self = null;
            private static $app  = null;
            private static $globalPath  = array();
            private static $globalClass = array();
            private static $globalPointer = array();
            private static $globalObject = array();

            /**
             * @brief 初始化函数
             **/
            public static function init(){
                
                   if(self::$inited)
                      throw new IException('System has been worked!');
                   self::regGlobalPath();
                   self::regAutoLoad();
                   self::run();
            }
            
            /**
             * @brief 注册核心类路径
             */
            private static function regGlobalPath(){
                
                  $sys_path = str_replace("\\",'/',dirname(__FILE__));
                  $globalPathArr = array(
                            'sys_core'=>$sys_path.'/library/core',
                            'sys_ext'=>$sys_path.'/library/extension',
                            'sys_db'=>$sys_path.'/library/core/db',
                            'sys_cache'=>$sys_path.'/library/core/cache'
                   );
                   self::setGlobalPath($globalPathArr);
            }
            
            /**
             * @brief 注册autoLoad 函数
             **/
            private static function regAutoLoad(){

                 spl_autoload_register(array('self', 'autoLoad'));
            }
            
            /**
             * @brief 运行函数
             **/
            private static function run(){

                  self::$app = new IApp();
                  self::$app->run();
            }
            
            /**
             * @brief 自动加载文件函数
             * @param $className 类名
             **/
            private static function autoLoad($className){
                
          
                $classFile = '';

                if(array_key_exists($className,self::$globalClass)){ //搜索全局类

                       $classFile = self::$globalClass[$className];
                       
                 }else{
                        
                       if(substr($className,-7)=='Control' && $className !='IControl'){
                           self::app()->control(substr($className,0,-7),'',false);
                           return ;
                       }
                       
                       if(substr($className,-5)=='Model' && $className != 'IModel'){
                           self::app()->model(substr($className,0,-5),'',false);
                           return ;
                       }
                       
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
                }else{
                          throw new IException($className.' not found');
                }					  	
           }

            /** 
             * @brief 设置系统加载文件路径
             * @param $path  路径
             * @param $alias 别名
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

                          if(!is_dir($path))
                               throw new IException($path.' is not dir');
                                 
                          array_unshift(self::$globalPath,$path);

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
             * @alias 指针地址
             **/
            public static function getGlobalPath($alias){

                       return isset(self::$golobalPath[$alias])?self::$gloabalPath[$alias]:false;
            }
            
            /**
             * @brief  获取应用对象 
             **/
            public static function app(){
                
                    return self::$app;
            }
            
            /**
             * @brief 引入文件
             * @param $pointer 指针索引
             * @param $force   立即导入
             **/
            public  static function import($pointer,$force=false){
                  
                  $pointer = str_replace(array('\\','/'),'.',$pointer);
       
                  if(isset(self::$globalPointer[$pointer])){
                       if($force)
                         require self::$globalPointer[$pointer];	
                       return true;
                  }
                  
                  if(class_exists($pointer,false))
                       return true ;
         
                  if(strpos($pointer,'.')!==false){
                    
                        $pathArr = explode('.',$pointer);	
                        
                        $class = array_pop($pathArr);

                        foreach($pathArr as $k=>$v){
                                $pathArr[$k] = isset($globalPath[$v])? $globalPath[$v]:$v; 
                        }

                        $path =  implode('/',$pathArr);

                        if($class!='*'){
           
                            $file = $path.'/'.$class.'.class.php';
                 
                            if(!is_file($file))
                                 return false;
                            
                            self::$globalPointer[$pointer] = $file;
                            self::$globalClass[$class] = $file;

                            if($force){
                            		require $file;
                            }
      
                            return true ;
                        }
                  
                        if(!is_dir($path))
                             return false;
                        
                        self::setGlobalPath($path,$pointer);
                        return true;
                  }
                  
                  try{
                      self::autoLoad($pointer);
                      self::$globalPointer[$pointer] = self::$globalClass[$pointer];
                  }catch(IException $e){
                      return false;
                  }
            }
            
            /**
             * @brief 获取全局对象
             * @param $config 获取的对象配置
             * @param $params 对象的初始化参数
             * @param $cache  是否把初始化的对象放入全局
             * @param $new    是否重新创建对象
             **/
            public static function object($config,$params=array(),$cache=false,$new=false){

                    if(is_string($config)){
                        
                            $className = $config;
                            
                    }else if(is_array($config)){
                        
                            $className = $config['class'];
                            isset($config['params']) && $params = $config['params'];
                            isset($config['cache'])  && $cache = $config['cache'];
                            isset($config['new']) && $new = $config['new'];
                    }

                    if(isset(self::$globalObject[$className]) && $new==false){
                            return self::$globalObject[$className];	
                    } 								
                    if(class_exists($className)){
                            $class=new ReflectionClass($className);
                            $object=call_user_func_array(array($class,'newInstance'),$params);
                            $cache && self::$globalObject[$className] = $object;
                            return $object;
                    }

                    return false;
            }

    }
			
?>