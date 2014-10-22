<?php defined('IN_FATE') or die('Access denied');
			
    /**
     * @brief 数据库代理类
     * @param $type 数据库类型
     * @param $db   数据库对象
     **/

    class IDb extends IComponent{
         
         private   $db = '';
         private   $maps = array();
         private   $type;
        
         public function __construct($config){
             $this->type = array_shift(array_keys($config));
             $this->maps = $config;
             $this->proxy();
         }
                           
         public function proxy($type=''){
             
              if(empty($type)){
                  
                  if(!empty($this->db))
                       return $this->db;
                  
                  $type = $this->type;
              }
              
              $name  ='I'.ucfirst($type);
              Fate::import('sys_db.'.$name);
              
              if(!empty($this->maps[$type])){
                $db = new $name($this->maps[$type]);
                $this->db = $db;
              }else{
                  throw new IException("$type config is NULL");
              }
         }
                  
         public function __call($method,$params){
             
                return call_user_func_array(array($this->db,$method),$params);
         }
         
         public function __get($name){
             
                return  $this->db->$name;
         }
         
         public function __set($name,$value){
             
                $this->db->$name = $value;
         }
        

    }
?>