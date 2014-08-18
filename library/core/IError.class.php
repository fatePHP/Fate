<?php defined('IN_FATE') or die('Access denied!');
			
			/**
			 * @brief 错误处理类
			 **/
			 
			 class IError {
			 			
			 			/**
			 			 * @brief 输出错误信息
			 			 **/
					 	public static function display($level,$message,$file,$line,$content){
				  		
					  				$re = "\n\n\n<div style='width:100%;border:1px #000 solid;'>\n<pre>";
					  				$re.= sprintf("<b>&nbsp;&nbsp;ErrorNo</b>: %s\n",$level);
										$re.= sprintf("<b>&nbsp;&nbsp;ErrorMessage</b>: %s\n",$message);
										$re.= sprintf("<b>&nbsp;&nbsp;ErrorFile</b>: %s\n",$file);
										$re.= sprintf("<b>&nbsp;&nbsp;ErrorLine</b>: %s\n",$line);
										$re.= sprintf("<b>&nbsp;&nbsp;出错时变量的值</b>: %s",var_export($content,true));
					  	 			$re.= "</pre></div>";
										exit($re);
					  }	
			 }

?>