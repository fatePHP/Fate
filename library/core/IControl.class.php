<?php defined('IN_FATE') or die('Access denied');

			/**
			 * @brief 所有控制器的基类
			 * @param $view 视图对象
			 * @param $layout 视图模板
			 **/
			 
			class IControl {
				    
						private $view;
						private $layout;
						
				    /**
				     * @brief 初始化函数
				     **/
						public function __construct(){
								 
								 $this->view = IApp::object('IView');
						}    
						
						
						public function beginAction(){
							
						}
						
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
						* @brief 获取model类
						**/
						public function model($model){
							
							    return IApp::object($model.'Model');
						}
						
			}
			
?>