<?php defined('IN_FATE') or die('Access denied');

		/**
		 * @brief 日志基类 
		 **/
		abstract class ILog {
				
					/**
					 * @breif 写入日志
					 * @param $message 日志信息
					 * @param $file    日志文件
					 **/
					abstract protected function write($message,$filePath);
					
					
					/**
					 * @breif 读取日志
					 * @param $file 日志文件
					 **/
					abstract protected function read($file);
			
		}


?>