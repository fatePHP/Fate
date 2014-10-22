<?php	defined('IN_FATE') or die('Access denied!');

			/**
			 * @brief 缓存抽象类类
			 **/
			 
			abstract class ICache {
				
				
                                abstract public function set($key,$value,$expire=);

                                abstract public function get($key);

                                abstract public function delete($key);
					
				
			}

?>