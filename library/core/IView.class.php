<?php defined('IN_FATE') or die('Access denied!');

			/**
			 * @brief 模板引擎
			 * @param $suffix        模板文件自定义后缀名
			 * @param $theme         主题名称
			 * @param $viewFile      模板文件
			 * @param $viewPath      模板文件目录
			 * @param $compilePath   编译文件目录
			 * @param $compileSuffix 编译文件后缀名
			 * @param $layout        布局文件名称
			 * @param $tags          自定义模板标签数组
			 * @param $data          模板变量
			 * @param $left          模板左限制符
			 * @param $right         模板右限制符
			 **/

			class IView {
				 
				  private $suffix;
				  private $theme;
				  private $viewFile;
				  private $viewPath;
				  private $compileSuffix;
				  private $compilePath;
				  public  $layout;
				  private $tags = array('\$','url','root','foreach','include','require','if','elseif','else','while','for','js','css','image','								upload','view','set');
				  private $data = array();
				  private $left  ;
				  private $right ; 
				  
				  /**
				   * @brief 初始化函数
				   **/ 			  
					public function __construct(){
						
						  $config = IApp::config('view');
						  $this->suffix = empty($config['suffix'])?'.html':$config['suffix'];
						  $this->theme = empty($config['theme'])?'default':$config['theme'];
							$this->viewPath = $this->getViewPath();
							$this->compileSuffix = empty($config['compileSuffix'])?'.compile':$config['compileSuffix'];
							$this->compilePath = $this->getcompilePath();
							$this->layout = empty($config['layout'])?'':$config['layout'];
						  $this->left = empty($config['left'])?'<{':$config['left'];
							$this->right = empty($config['right'])?'}>':$config['right'];
					}
					
			    /**
					 * @brief 设置视图文件目录
					 **/
					public function getViewPath(){
						
							return APP_PATH.'/themes/'.$this->theme.'/';
					}
					
					/**
					 * @brief 设置编译文件目录
					 */
					public function getCompilePath(){
						  
						  return APP_PATH.'/cache/compile/'.$this->theme.'/';
					}
					
					/**
					 * @brief 生成编译文件并返回文件路径
					 **/
					
				  public function getCompileFile($viewFile,$layout=true){
				  	
				  		$compileFile = $this->compilePath.$viewFile.'_'.md5($viewFile).$this->compileSuffix;
							$viewFile    = $this->viewPath.$viewFile.$this->suffix;

							$layoutFile  = '';
							
							if(is_string($layout)){
							  $layoutFile = $this->viewPath.'layouts/'.$layout.$this->suffix;
							}else if($layout===true && !empty($this->layout)){
								$layoutFile = $this->viewPath.'layouts/'.$this->layout.$this->suffix;
							}		
	
							if(!file_exists($compileFile) || (filemtime($viewFile) > filemtime($compileFile)) || (file_exists($layoutFile)&& (filemtime($layoutFile) > filemtime($compileFile))))
							{
							
								//获取view内容
								$viewContent = file_get_contents($viewFile);
			
								//处理layout
								if(file_exists($layoutFile)){
									$layoutContent = file_get_contents($layoutFile);
									$viewContent = str_replace('<{viewcontent}>',$viewContent,$layoutContent);
								}
								//解析模板标签
								$inputContent = $this->parseView($viewContent);
			
								//创建文件
								$fileObj  = new IFile($compileFile,'w+');
								$fileObj->write($inputContent);
								$fileObj->save();
							}
							return $compileFile;
					}
					
					/**
					 * @brief 解析模板内容
					 * @param $viewFile 需要解析的模板内容
					 **/			
					public function parseView($viewContent){
						 $tags = implode('|',$this->tags);
						 return preg_replace_callback('/'.$this->left.'(\/?)('.$tags.')\s*(:?)([^}]*)'.$this->right.'/i', array($this,'parseTag'), $viewContent);
					}
					
					/**
					 * @brief 解析标签
					 * @param $matches 正则匹配上的数据
					 **/
					public function parseTag($matches)
				  {
						if($matches[1]!=='/')
						{
							switch($matches[2].$matches[3])
							{
							 case '$':
				       { 
				          $str = trim($matches[4]);
				          $first = substr($str,0,1);
									if($first != '.' && $first != '(')
									{
										if(strpos($str,'(')===false){
											return '<?php echo isset($'.$str.')?$'.$str.':"";?>';
										}else{
											 return '<?php echo $'.$str.';?>';
										 }
									}else{
										 return $matches[0];
									}
				        }      
								case 'echo:': return '<?php echo '.rtrim($matches[4],';/').';?>';
				        case 'js:':   return '<?php echo "<script src=\"'.IUrl::getWebDir().'themes/'.$this->theme.'/skin/js/'.$matches[4].'\"></script>";?>';
				        case 'css:':  return '<?php echo "<link rel=\"stylesheet\" href=\"'.IUrl::getWebDir().'themes/'.$this->theme.'/skin/css/'.$matches[4].'\">";?>';
				        case 'image:': return '<?php echo IUrl::getWebDir()."themes/'.$this->theme.'/skin/image/'.$matches[4].'";?>';
				        case 'root:': return '<?php echo IUrl::getWebDir()."'.$matches[4].'";?>';
								case 'if:':   return '<?php if('.$matches[4].'){?>';
								case 'elseif:': return '<?php }elseif('.$matches[4].'){?>';
								case 'else:': return '<?php }else{'.$matches[4].'?>';
								case 'set:':
	              {
	                  return '<?php '.$matches[4].'?>';
	              }
								case 'while:': return '<?php while('.$matches[4].'){?>';
								case 'foreach:':
								{
									$attr = $this->parseAttrs($matches[4]);
									if(!isset($attr['items'])) $attr['items'] = '$items';
									else $attr['items'] = $attr['items'];
									if(!isset($attr['key'])) $attr['key'] = '$key';
									else $attr['key'] = $attr['key'];
									if(!isset($attr['item'])) $attr['item'] = '$item';
									else $attr['item'] = $attr['item'];
				
									return '<?php foreach('.$attr['items'].' as '.$attr['key'].' => '.$attr['item'].'){?>';
								}
								case 'for:':
								{
									$attr = $this->parseAttrs($matches[4]);
									if(!isset($attr['item'])) $attr['item'] = '$i';
									else $attr['item'] = $attr['item'];
									if(!isset($attr['from'])) $attr['from'] = 0;
				
				                    if(!isset($attr['upto']) && !isset($attr['downto'])) $attr['upto'] = 10;
				                    if(isset($attr['upto']))
				                    {
				                        $op = '<=';
				                        $end = $attr['upto'];
				                        if($attr['upto']<$attr['from']) $attr['upto'] = $attr['from'];
				                        if(!isset($attr['step'])) $attr['step'] = 1;
				                    }
				                    else
				                    {
				                        $op = '>=';
				                        $end = $attr['downto'];
				                        if($attr['downto']>$attr['from'])$attr['downto'] = $attr['from'];
				                        if(!isset($attr['step'])) $attr['step'] = -1;
				                    }
									return '<?php for('.$attr['item'].' = '.$attr['from'].' ; '.$attr['item'].$op.$end.' ; '.$attr['item'].' = '.$attr['item'].'+'.$attr['step'].'){?>';
								}
								case 'query:':
								{
									$endchart=substr(trim($matches[4]),-1);
									$attrs = $this->parseAttrs(rtrim($matches[4],'/'));
				                    if(!isset($attrs['id'])) $id = '$query';
				                    else $id = $attrs['id'];
				                    if(!isset($attrs['items'])) $items = '$items';
				                    else $items = $attrs['items'];
									$tem = "$id".' = new IQuery("'.$attrs['name'].'");';
									//实现属性中符号表达式的问题
									$old_char=array(' eq ',' l ',' g ',' le ',' ge ', 'neq');
									$new_char=array(' = ',' < ',' > ',' <= ',' >= ', ' != ');
									foreach($attrs as $k => $v)
									{
										if($k != 'name' && $k != 'id' && $k != 'items' && $k != 'item') $tem .= "{$id}->".$k.' = "'.str_replace($old_char,$new_char,$v).'";';
									}
									$tem .= $items.' = '.$id.'->find();';
									if(!isset($attrs['key'])) $attrs['key'] = '$key';
									else $attrs['key'] = $attrs['key'];
									if(!isset($attrs['item'])) $attrs['item'] = '$item';
									else $attrs['item'] = $attrs['item'];
									if($endchart=='/') return '<?php '.$tem.'?>';
									else return '<?php '.$tem.' foreach('.$items.' as '.$attrs['key'].' => '.$attrs['item'].'){?>';
								}
								case 'code:': return '<?php '.$matches[4];
								case 'view:':
								{
									$fileName=trim($matches[4]);
									$tempFile = $this->getCompileFile($fileName,$layout);
									return "<?php require('$tempFile')?>";;
								}
								case 'require:':
								case 'include:':
								{
									$fileName=trim($matches[4]);						
									return "<?php include('$fileName')?>";
								}
								default:
								{
									 return $matches[0];
								}
							}
						}
						else
						{
							return ($matches[2] =='code')?'?>':'<?php }?>';
						}
				 }
				 
				/**
		     * @brief 解析模板标签属性
		     * @param string $str
		     * @return array以数组的形式返回属性值
		     **/
				 public function parseAttrs($str)
				 {
						preg_match_all('/([a-zA-Z0-9_]+)\s*=([^=]+?)(?=(\S+\s*=)|$)/i', trim($str), $attrs);
						$attr = array();
						foreach($attrs[0] as $value)
						{
							$tem = explode('=',$value);
							$attr[trim($tem[0])] = trim($tem[1]);
						}
						return $attr;
				 }
								 
				/**
				 * @brief 分配变量
				 **/
				 public function assign($k,$v){
						$this->data[$k] = $v;
				 }
						
				/**
				 * @brief 输出模板
				 **/
			   public function display($viewFile,$layout=true){
						
			  		extract($this->data,EXTR_SKIP);
			  		$compileFile = $this->getCompileFile($viewFile,$layout);
	          require $compileFile;
			   }
			}
?>