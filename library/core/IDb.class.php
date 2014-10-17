<?php defined('IN_FATE') or die('Access denied');
			
    /**
     * @brief 数据库驱动类
     * @param $type 数据库类型
     * @param $db   数据库对象
     **/

    class IDb extends IComponent{

         protected $type;
         protected $host;
         protected $name;
         protected $user;
         protected $pwd;
         protected $prefix;
         protected $pconnect;
         protected $showError;
         protected $charset;
         private   $driver;
         
         
         public  function __construct($config){
              parent::__construct($config);
              unset($config['type']);
              $this->drive($this->type,$config);
         }
         
         public function drive($type='',$config=array()){
               
              if(empty($type))
                  return $this->driver;
              
              $driverName  ='I'.ucfirst($type);
              Fate::import('sys_db.'.$driverName);
              $this->driver = new $driverName($config);
         }
         
         public function getPrefix(){
             
                return $this->prefix;
         }
         
         public function setPrefix($value){
             
               $this->prefix = $value;
         }

    }
?>