<?php defined('IN_FATE') or exit('access denied');

	/**
	  * @brief 过滤库
	  */

class IFilter
{

	/**
	 * @brief 对字符串进行过滤处理
	 * @param  string $str      被过滤的字符串
	 * @param  string $type     过滤数据类型 值: int, float, string, text, bool
	 * @return string 被过滤后的字符串
	 * @note   默认执行的是string类型的过滤
	 */
	 
		public static function run($str,$type = 'string')
		{
			if(is_array($str))
			{
				foreach($str as $key => $val)
				{
					$resultStr[$key] = self::run($val, $type);
				}
				return $resultStr;
			}
			else
			{
				switch($type)
				{
					case "int":
						return intval($str);
					break;
	
					case "float":
						return floatval($str);
					break;
	
					case "text":
						return self::text($str);
					break;
	
					case "bool":
						return (bool)$str;
					break;
	
					default:
						return self::string($str);
					break;
				}
			}
		}

	/**
	 * @brief  对字符串进行严格的过滤处理
	 * @param  string  $str      被过滤的字符串
	 * @return string            被过滤后的字符串
	 * @note 过滤所有html标签和php标签以及部分特殊符号
	 */
	 
		public static function string($str)
		{
			$str = trim($str);
			$except = array('　');
			$str = str_replace($except,'',htmlspecialchars($str,ENT_QUOTES));
			return self::addSlash($str);
		}

	/**
	 * @brief 对字符串进行普通的过滤处理
	 * @param string $str      被过滤的字符串
	 * @return string 被过滤后的字符串
	 * @note 仅对于部分如:<script,<iframe等标签进行过滤
	 */
	 
		public static function text($str)
		{
			$str = trim($str);
			require_once(dirname(__FILE__)."/htmlpurifier-4.3.0/HTMLPurifier.standalone.php");
			$cache_dir=Think::$app->getRuntimePath()."htmlpurifier/";
			if(!file_exists($cache_dir))
			{
				IFile::mkdir($cache_dir);
			}
			$config = HTMLPurifier_Config::createDefault();
			
			//配置 允许flash
			$config->set('HTML.SafeEmbed',true);
			$config->set('HTML.SafeObject',true);
			$config->set('Output.FlashCompat',true);
			
			//配置 缓存目录
			$config->set('Cache.SerializerPath',$cache_dir); //设置cache目录
	
			//允许<a>的target属性
			$def = $config->getHTMLDefinition(true);
			$def->addAttribute('a', 'target', 'Enum#_blank,_self,_target,_top');
	
			$purifier = new HTMLPurifier($config);//过略掉所有<script>，<i?frame>标签的on事件,css的js-expression、import等js行为，a的js-href
			$str = $purifier->purify($str);
	
			return self::addSlash($str);
		}

	/**
	 * @brief 增加转义斜线
	 * @param string $str 要转义的字符串
	 * @return string 转义后的字符串
	 */
	 
		public static function addSlash($str)
		{
			if(is_array($str))
			{
				$resultStr = array();
				foreach($str as $key => $val)
				{
					$resultStr[$key] = self::addSlash($val);
				}
				return $resultStr;
			}
			else
			{
				return addslashes($str);
			}
		}

	/**
	 * @brief 增加转义斜线
	 * @param string $str 要转义的字符串
	 * @return string 转义后的字符串
	 */
	 
		public static function stripSlash($str)
		{
			if(is_array($str))
			{
				$resultStr = array();
				foreach($str as $key => $val)
				{
					$resultStr[$key] = self::stripSlash($val);
				}
				return $resultStr;
			}
			else
			{
				return stripslashes($str);
			}
		}


	/**
	 * @brief 删除非站内链接
	 * @param string $str 内容
	 * @param     array  $allow_urls  允许的超链接
	 * @return string 
	 */

		public static function clearLinks( $str, $allow_urls=array()  )
		{
			
			 $allow_urls = array_merge(array($_SERVER['HTTP_HOST']), $allow_urls);
			$host_rule = join('|', $allow_urls);
			$host_rule = preg_replace("#[\n\r]#", '', $host_rule);
			$host_rule = str_replace('.', "\\.", $host_rule);
			$host_rule = str_replace('/', "\\/", $host_rule);
			$arr = '';
			preg_match_all("#<a([^>]*)>(.*)<\/a>#iU", $str, $arr);
			if( is_array($arr[0]) )
			{
				$rparr = array();
				$tgarr = array();
				foreach($arr[0] as $i=>$v)
				{
					if( $host_rule != '' && preg_match('#'.$host_rule.'#i', $arr[1][$i]) )
					{
						continue;
					} else {
						$rparr[] = $v;
						$tgarr[] = $arr[2][$i];
					}
				}
				if( !empty($rparr) )
				{
					$str = str_replace($rparr, $tgarr, $str);
				}
			}
			$arr = $rparr = $tgarr = '';
			return $str;
		}

}
?>
