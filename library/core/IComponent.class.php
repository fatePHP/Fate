<?php

    class IComponent extends IBase {

            public function __construct($config=array()){
                
                    if(!empty($config)){
                    
                        foreach($config as $key=>$value){

                               $this->$key = $value; 
                        }     
                    }
                  $this->init();
            }
            
            protected function init(){
                

            }
    }


?>