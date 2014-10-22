<?php
    
    /**
     * @brief 组件控制台
     * @param $component  组件数组集合
     */

    class  IComponentManager extends IBase  {
            
            private $component = array('url'=>array(),'view'=>array());
            
            /**
             * @brief 初始化应用组件
             */
            protected function initComponent(){
                
                    $this->component = array_merge($this->component,$this->config('component'));
            }
            
            /**
             * @brief 加载组件 若没有 获取属性值
             * @param type $component
             * @return \IComponent
             */
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