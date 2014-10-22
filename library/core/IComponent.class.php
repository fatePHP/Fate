<?php
    
    /**
     * @brief 组件基础类
     */
    class IComponent extends IBase {
            
            /**
             * @brief 构造函数
             * @param type $config 组件配置文件
             */
            public function __construct($config=array()){
                
                    if(!empty($config)){
                    
                        foreach($config as $key=>$value){

                               $this->$key = $value; 
                        }     
                    }
                  $this->init();
            }
            
            /**
             * @brief 初始化组件
             */
            protected function init(){
                

            }
    }


?>