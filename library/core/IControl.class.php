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
             * @brief 执行action之前进行的操作
             */
            public function beginAction(){

            }
            
            /**
             * @brief 执行action之后进行的操作
             */
            public function endAction(){

            }

            /**
             * @brief 初始化函数
             **/
            public function __construct(){
                  
                 $this->view = Fate::app()->view;
                 $this->model = $this->model();
                 parent::__construct();
        
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
             * @param $path    视图路径
             * @param $value   渲染视图的数据
             * @param $layout  布局文件
             **/			
             public function render($value=array(),$path='',$layout=true){

                    if(is_array($value) && !empty($value)){

                        foreach($value as $k=>$v){
                             $this->view->assign($k,$v);
                        }	
                    }
                    if(empty($path))
                        $path = Fate::app()->module.'/'.Fate::app()->control.'/'.Fate::app()->action;
                    $this->view->display($path,$layout);
             }
             
             /**
              * @brief 视图布局文件
              */
             public function getLayout(){
                 
                  return $this->view->layout;
             }
             
             /**
              * @brief 设置视图布局
              * @param $value 视图名称
              */
             public function setLayout($value){
                 
                  $this->view->layout = $value;
             }
             
             /**
              * @brief 返回模型 
              * @param type $name   模型名称
              * @param type $module 模块名称
              */
             public function model($name='',$module=''){

                 if(empty($name))
                    $name = Fate::app()->control;
                 
                 return Fate::app()->model($name,$module);
             }
             
             /**
              * @brief 返回当前控制器对应的模型
              */
             public function getModel(){
                 
                  return $this->model;
             }

  }
			
?>