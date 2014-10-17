<?php defined('IN_FATE') or die('Access denied');

    /**
     * @brief 所有控制器的基类
     * @param $view   视图对象
     * @param $model  模型对象
     **/
			 
   class IControl extends IComponent{

            private  $view;
            private  $model;

            /**
             * @brief 初始化函数
             **/
            public function __construct(){
                  
                 $this->view = Fate::app()->view;
                 $this->model = $this->model();
                 parent::__construct();
        
            }    
            
            /*
             * @brief 执行action之前进行的操作
             */
            public function beginAction(){

            }
            
            /*
             * @brief 执行action之后进行的操作
             */
            public function endAction(){

            }

            /**
             * @brief 模板变量赋值
             * @param $k 键名
             * @param $v 键值
             **/
             public function setVal($k,$v){

                  $this->view->assign($k,$v);
             }

            /**
             * @brief 输出模板 
             **/			
             public function render($path,$value=array(),$layout=true){

                    if(is_array($value) && !empty($value)){

                        foreach($value as $k=>$v){
                             $this->view->assign($k,$v);
                        }	
                    }
                    
                    $this->view->display($path,$layout);
             }
             
             /**
              * @brief 设置视图布局
              * @param type $value
              */
             public function setLayout($value){
                 
                  $this->view->layout = $value;
             }
             
             /**
              * @brief 视图布局文件
              * @return type
              */
             public function getLayout(){
                 
                  return $this->view->layout;
             }
             
             /**
              * @brief 加载模型 
              * @param type $name
              * @param type $module
              * @return type
              */
             public function model($name='',$module=''){

                 if(empty($name))
                    $name = Fate::app()->control;
                 
                 return Fate::app()->model($name,$module);
             }
             
             /**
              * @brief 获取当前控制器对应的 模型类
              */
             public function getModel(){
                  return $this->model;
             }

  }
			
?>