<?php  defined('IN_FATE') or exit('access denied');
			/**
			 * @brief 文件系统工具类
			 * @param $resource 文件句柄
			 */

		  class IFile {
		  	
		  	    private $resource ;
		  	    
		  	    /**
		  	     * @brief 初始化打开文件句柄
		  	     * @param $file 要打开的文件
		  	     * @param $mod  打开方式
		  	     */
		  			public function __construct($file,$mode='r')
						{
							$name = self::name($file);
							$dir  = self::dir($file);
							self::mkdir($dir);
							$this->resource = fopen($file,$mode.'b');
							if($this->resource)
							{
								flock($this->resource,LOCK_EX);
							}
						}
						
						/**
						 * @brief  向打开的文件写入内容
						 * @param  $content 要写入的文件
						 * @return $total 写入的字数 
						 */
						public function write($content)
						{
							$total = fwrite($this->resource,$content);
							return is_bool($total) ? false : $total;
						}
						
						/**
						 * @brief 释放锁
						 */
						public function save()
						{
							flock($this->resource,LOCK_UN);
						}
						
					  /**
						 * @brief 析构函数，释放文件连接句柄
						 */
						public function __destruct()
						{
							if(is_resource($this->resource))
							{
								fclose($this->resource);
							}
						}
						
		  			/**
		  			 * @brief 获取路径文件名 若指定ext 则不返回扩展名
		  			*/
		  			public static function name($file,$ext=''){
		  					
		  					return empty($ext)? basename($file):basename($file,$ext); //pathinfo($file,PHPINFO_EXTENSION)
		  			}
		  			
		  			/**
		  			 * @brief 获取路径目录 
		  			*/
		  			public static function dir($file){
		  				
		  					return dirname($file); //pathinfo($file,PHPINFO_DIRNAME)
		  			}
		  			
		  			/**
		  			 * @brief 获取扩展名
		  			*/
		  			public static function suffix($file){
		  				
		  					return pathinfo($file,PHPINFO_EXTENSION);
		  			}
		  			
		  			/**
		  			 * @brief 检查并创建
		  			 */
						public static function mkdir($path,$chmod=0777)
						{	
								return is_dir($path) || (self::mkdir(dirname($path),$chmod) && mkdir($path,$chmod));
						}
		  			
		  			/**
		  			 * @brief socket 通信
		  			*/
		  			public static function socket($url, $limit = 0, $post = '', $cookie = '', $ip = '', $timeout = 20, $block = TRUE)
						{
							$return = '';
							$matches = parse_url($url);
							!isset($matches['host']) && $matches['host'] = '';
							!isset($matches['path']) && $matches['path'] = '';
							!isset($matches['query']) && $matches['query'] = '';
							!isset($matches['port']) && $matches['port'] = '';
							$host = $matches['host'];
							$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
							$port = !empty($matches['port']) ? $matches['port'] : 80;
							if($post)
							{
								$out = "POST $path HTTP/1.0\r\n";
								$out .= "Accept: */*\r\n";
								$out .= "Accept-Language: zh-cn\r\n";
								$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
								$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
								$out .= "Host: $host\r\n";
								$out .= 'Content-Length: '.strlen($post)."\r\n";
								$out .= "Connection: Close\r\n";
								$out .= "Cache-Control: no-cache\r\n";
								$out .= "Cookie: $cookie\r\n\r\n";
								$out .= $post;
							}
							else
							{
								$out = "GET $path HTTP/1.0\r\n";
								$out .= "Accept: */*\r\n";
								$out .= "Accept-Language: zh-cn\r\n";
								$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
								$out .= "Host: $host\r\n";
								$out .= "Connection: Close\r\n";
								$out .= "Cookie: $cookie\r\n\r\n";
							}
							$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $timeout);
							if(!$fp)
							{
								return '';
							}
							else
							{
								stream_set_blocking($fp, $block);
								stream_set_timeout($fp, $timeout);
								@fwrite($fp, $out);
								$status = stream_get_meta_data($fp);
								if(!$status['timed_out'])
								{
									while (!feof($fp))
									{
										if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n"))break;
									}
									$stop = false;
									while(!feof($fp) && !$stop)
									{
										$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
										$return .= $data;
										if($limit)
										{
											$limit -= strlen($data);
											$stop = $limit <= 0;
										}
									}
								}
								@fclose($fp);
								return $return;
							}
						}
		  			
		  			/**
		  			 * @brief url抓取
		  			*/
		  			public static function crawl($type='curl',$method='get'){
		  				
		  				
		  			}
		  			
		  }
		  
		  /*
		   *	chgrp($file,$grp) 改变文件所属组 $grp 组名或组ID
		   *  chmod($file,0755) 改变文件权限
		   *  chown($file,$user)改变文件所有者 $user用户名或用户ID
		   *  is_dir()          判断是否为目录
		   *  is_file()         判断是否为文件
		   *  file_exists
		   */

?>