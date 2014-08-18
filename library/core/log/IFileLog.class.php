<?php defined('IN_FATE') or die('Access denied');
	
	 		/**
	 		 * @brief 文件日志类
	 		 * @param $maxFileSize 日志文件大小 单位为KB 默认最大为1M
	 		 * @param $maxFileNum  日志文件个数限制      默认为5个
	 		 * @param $filePath 	 日志文件路径
	 		 * @param $fileName    日志文件名称
	 		 **/
	 		 
	 		 class IFileLog extends ILog{
	 		 				
	 		 				private $maxFileSize = '1024';
	 		 				private $maxFileNum  = '5';
	 		 				private $logFilePath = '';
	 		 				private $logFile = '';
	 		 				
	 		 				/**
	 		 				 * @brief 写入日志文件
	 		 				 * @param $message 日志信息
	 		 				 * @param $file    日志文件
	 		 				 **/
	 		 				public function write($message,$file){
	 		 					
 		 							$this->logFile = $this->getLogPath().'/'.$file;
 		 							
									if(@filesize($this->logFile)>$this->getMaxFileSize()*1024){
											$this->distribution();
									}
									
									$fp=@fopen($this->logFile,'a');
									
									@flock($fp,LOCK_EX);
									if(is_array($message)){
										foreach($message as $m)
										@fwrite($fp,date('Y-m-d H:i:s')." $m\n");
									}else{
										@fwrite($fp,date('Y-m-d H:i:s')." $message\n");
									}
									@flock($fp,LOCK_UN);
									@fclose($fp);
	 		 				}
	 		 				
	 		 				/**
	 		 				 * @brief 读取日志文件内容
	 		 				 * @param $file  日志文件
	 		 				 **/
	 		 				public function read($file,$length=''){
	 		 					
	 		 						$logFile = $this->getLogPath().'/'.$file;
	 		 						
	 		 						$fp = @fopen($logFile,'r');
	 		 						//$content = fgets($fp,$length); 按行读取
	 		 						$content = empty($length)? fread($fp,filesize($logFile)):fread($fp,$length);
	 		 						@fclose($fp);
	 		 						echo $content;
	 		 				}
	 		 				
	 		 				/**
	 		 				 * @brief 分配日志文件
	 		 				 **/
	 		 				public function distribution(){
	 		 							
										$max=$this->getMaxLogFiles();
										
										for($i=$max;$i>0;--$i)
										{
											$tempFile=$file.'_'.$i;
											if(is_file($tempFile))
											{
												if($i===$max)
													@unlink($file.'_'.$i);
												else
													@rename($tempFile,$file.'_'.($i+1));
											}
										}
										
										if(is_file($file))
											@rename($file,$file.'_1'); 
	 		 				}
	 		 				
	 		 				/**
	 		 				 * @brief 获取日志文件限制个数
	 		 				 **/
	 		 				public function getMaxFileNum(){
	 		 							
	 		 							return $this->maxFileNum;
	 		 				}
	 		 				
	 		 				/**
	 		 				 * @brief 设置日志文件限制个数
	 		 				 **/
	 		 				 
	 		 				public function setMaxFileNum($num){
	 		 					
 		 								$num = intval($num);
 		 						    $num = $num>0 ? $num:1;
 		 								$this->maxFileNum = $num;
 		 								return $this;
	 		 				}
	 		 				 
	 		 				/**
	 		 				 * @brief 获取日志文件限制大小
	 		 				 **/
	 		 				public function getMaxFileSize(){
	 		 					
	 		 							return $this->maxFileSize;
	 		 				}
	 		 				
	 		 				/**
	 		 				 * @brief 设置日志文件限制大小
	 		 				 **/
	 		 				public function setMaxFileSize($size){
	 		 					
	 		 							$size = intval($size);
	 		 						  $size = $size>0 ? $size:1;
	 		 					    $this->maxFileSize = $size;
	 		 					    return $this;
	 		 				}
	 		 				
	 		 				/**
	 		 				 * @brief 获取日志文件路径
	 		 				 **/
	 		 				public function getLogPath(){
	 		 					
	 		 							 return $this->logFilePath;
	 		 				}
	 		 				
	 		 				/**
	 		 				 * @brief 设置日志文件路径
	 		 				 **/
	 		 				public function setLogPath($path){
	 		 					
	 		 							 $path = realPath($path);
	 		 							 if(is_dir($path) && is_writeable($path)){
	 		 						  	 $this->logFilePath = $path;
	 		 						 	 }
	 		 						   return $this;
	 		 				}
	 		 	
	 		 }


?>