<?php
    
    class  IComponentManager extends IBase  {
            
            private $component = array('url'=>array(),'view'=>array());
            
            protected function initComponent(){
                
                    $this->component = array_merge($this->component,$this->config('component'));
            }
        
            public function __get($component){

                if(isset($this->component[$component])){
                                           
                    $componentConfig  = $this->component[$component];
                    $realName = 'I'.ucfirst($component);

                    if(isset($componentConfig['realName'])){
                          $realName = $componentConfig['realName'];
                          unset($componentConfig['realName']);
                    }

                    if(isset($componentConfig['realPath'])){
                          $path = $componentConfig['realPath'];
                          Fate::import($path.$realName,true);
                          $componentObj  =  new $realName($componentConfig);
                          unset($componentConfig['realPath']);
                    }else{
                          $componentObj =  Fate::object(array('class'=>$realName,'cache'=>true,'params'=>array($componentConfig)));
                    }
                    
                    if($componentObj instanceof  IComponent){
                        return $componentObj;
                    }
                }
                return parent::__get($component);
            }
    }

?>