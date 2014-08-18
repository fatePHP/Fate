<?php defined('IN_FATE') or die('Access denied!');
	
			/**
			 * @brief 异常类 
			 **/
			 
			class IException  extends Exception{
					
					/**
					 * @brief 初始化函数
					 * @param $message 异常信息
					 * @param $code 级别
					 **/
					public function __construct($message='',$code=''){
							
							$debugData = debug_backtrace();
							$info = reset($debugData);
							if($info!==false){
									$this->message = $message;
									$this->code = $code;
									$this->file = $info['file'];
									$this->line = $info['line'];
							}
					}
									
					/**
					 * @brief 显示异常信息
					 **/
					public function display(){
						
								$debugData = $this->getTrace();
								$re = "\n\n\n<div style='width:100%;border:1px #000 solid;'>\n<pre>";
								$re .= sprintf("\t<b>Mess</b>: %s\n",$this->getMessage());
								$re .= sprintf("\t<b>Line</b>: %s\n",$this->getLine());
								$re .= sprintf("\t<b>File</b>: %s\n",$this->getFile());
								$re .= sprintf("\t<b>##Debug_backtrace:##</b>\n");
								foreach($debugData as $value)
								{
				
									$re .= sprintf("\t<b>File</b>:%-55s\t<b>Line</b>:%-5s\t<b>Type</b>:%-5s\t<b>Class</b>:%-15s\t<b>Func</b>:%s\n" ,
										isset($value['file'])?$value['file']: "" ,
										isset($value['line'])?$value['line']:"",
										isset($value['type'])?$value['type']:"",
										isset($value['class'])?$value['class']:"",
										$value['function'] );
								}
								$re .= "</pre></div>";
								exit($re);
					}
			}
	
?>