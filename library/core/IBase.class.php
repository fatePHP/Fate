<?php

   class IBase {
            
        public function canSet($name){
            
            $method = 'set'.$name;
            if(method_exists($this,$method))
                    return $method;
            return false;
        }
        
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
            
 }


?>