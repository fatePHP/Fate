<?php
   /**
    * @brief 基础类 封装PHP魔术方法 
    */
   class IBase {
        
        /**
         * @brief 判断是否可以设置属性
         * @param type $name 属性名
         * @return string $method 方法名 | boolean false 不可设置
         */
        public function canSet($name){
            
            $method = 'set'.$name;
            if(method_exists($this,$method))
                    return $method;
            return false;
        }
        
        /**
         * @brief 判断是否可以获取属性
         * @param type $name 属性名
         * @return string $method 方法名 | boolean false 不可获取
         */
        public  function canGet($name){
            
            $method = 'get'.$name;
            if(method_exists($this,$method))
                    return $method;
            return false;
        }
        
        public function  __set($name,$value){
            
            if($method = $this->canSet($name)){
                $this->$method($value);
            }else{
                throw new IException("Property ".$name." doesn't exit in class ".get_class($this));
            }
        }
        
        public function __get($name){

              if($method = $this->canGet($name)){
                  return $this->$method();
              }else{
                  throw new IException("Property ".$name." doesn't exit in class ".get_class($this));
              }

        }
        
        public function __call($method,$params){
            
                throw new IException ('Method '.$method." doesn't exit in class".get_class($this) );
        }
        
        static public function __callStatic($name, $arguments) {
            
                throw new IException ('Static Method '.$method." doesn't exit in class".get_class($this) );
        }
            
 }


?>