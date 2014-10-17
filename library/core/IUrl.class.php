<?php
    /*
     * @brief URL组件
     */
    
    class IUrl extends IComponent{
        
        
           public $rules=array();
           
           public $format='normal';
           
           public $suffix='';
           
           private  $tags = array();
           
           private  $partterns = array();
           
           private  $routes = array();
           
           /**
            * @brief 解析RULES
            */
           public function parseUrlRules(){
               
                $i = 0;

                foreach($this->rules as $parttern=>$route){
       
                    if(preg_match_all('/<(\w+)>/',$route,$routeMatches)){
                            foreach($routeMatches[1] as $tagName){
                                $this->tags[$i][$tagName] = "<$tagName>";
                            }
                    }
                    
                   $temp  = array('/'=>'\\/');

                   if(preg_match_all('/<(\w+):?(.*?)?>/',$parttern,$partternMatches)){
                       
                         $params =array_combine($partternMatches[1],$partternMatches[2]);
                     
                         foreach($params as $name=>$value) 
                         {
                                if($value===''){
                                    $value='[^\/]+';
                                }

                                $temp["<$name>"]="(?P<$name>$value)";
                          }
                    }
    

                    $p = rtrim($parttern,'*');   //如果正则式以*结尾则不是完全匹配 
                    $append = ($p!==$parttern); //是否在结尾追加
                    $p=trim($p,'/');
                    
                    $temp_p =preg_replace('/<(\w+):?.*?>/','<$1>',$p); //把正则替换成标签 <key>

                    $parttern='/^'.strtr($temp_p,$temp).'\/'; //把正则的<key> 解析成上方的(命名子模式)
                    
                    $parttern.=$append ?'/u':'$/u'; //确定是完全匹配还是模糊匹配		
                    $this->partterns[$i] = $parttern;
                    $this->routes[$i] = $route;
                    $i++;
                } 
                
                return $this;
           }
           
           /**
            * @brief 解析URL
            */
           public function parseUrl(){
               
              switch($this->format){

                case 'normal':  	 //原生模式

                    $route = '';
                    $route.= !empty($_GET['m'])?'/'.$_GET['m']:'';
                    $route.= !empty($_GET['c'])?'/'.$_GET['c']:'';
                    $route.= !empty($_GET['a'])?'/'.$_GET['a']:'';

                 return $route;
                    
                 break;

                case 'pathinfo':	 //PATHINFO模式

                    $uri = $this->getRealSelf();
                    preg_match('/\.php\/(.*)/',$uri,$matchAll);
                    $pathInfo = $matchAll[1]; 
                     foreach($this->partterns as $i=>$parttern)
                     {      
                          //定义配置
                          //$caseSentive = !empty($this->routes[$i]['caseSentive'])? $this->routes[$i]['caseSentive']:false;	
                          //$defaultParams = !empty($this->routes[$i]['defaultParams'])? $this->routes[$i]['defaultParams']:array();                               
                          //修正pathInfo
                          $pathInfo  = empty($suffix)? $pathInfo : substr($pathInfo,0,-strlen($suffix));
                          $pathInfo = rtrim($pathInfo,'/').'/';
                          //修正$pattern
                          $case = true ?'i':'';  
                          $parttern = $parttern.$case;

                          if(preg_match($parttern,$pathInfo,$matches))
                          {     
                                //把默认参数添加到$_GET $_REQUEST
                                //$_GET = array_merge($defaultParams,$_GET);
                                //$_REQUEST = array_merge($defaultParams,$_REQUEST);								
                                $temp=array();
                                //把匹配的参数添加到$_GET $_REQUEST
                                foreach($matches as $key=>$value)   
                                {    $temp["<".$key.">"] = $value;
                                     $_REQUEST[$key]=$_GET[$key]=$value;
                                }
                                //如果不是完全匹配 则继续解析pathInfo 到$_GET $_REQUEST
                                if($pathInfo!==$matches[0]){			 
                                       $this->pathinfoToArray(ltrim(substr($pathInfo,strlen($matches[0])),'/'));
                                }

                                 $pathInfo = strtr($this->routes[$i],$temp);
                    
                                 break;
                            }

                        }

                        return $pathInfo;

                    break;

                    case 'diy': 

                        return null; 	
                    break;

                  }
           }
           
           /**
            * @brief  获取RULES
            * @return type
            */
           public function getRules(){
               
                return $this->rules;
           }
           
           /**
            * @brief  获取FORMAT
            * @return type
            */
           public function getFormat(){
                
                return $this->format;
           }
           
           /**
            * @brief 返回站点目录
            * @return string
            */
           public function getWebDir(){
               
                 $relativePath = str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']));
                 
                 if($relativePath != '/')
                     $relativePath .= '/';
                 
                 return $relativePath;
           }    
           
           /**
            *@brief 返回HOST 
            */
           public function getHost($protocol='http'){
               
                $port = $_SERVER['SERVER_PORT']==80 ? '':':'.$_SERVER['SERVER_PORT'];
                $domain = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
                return $protocol.'://'.$domain.$port;
           }
           
           /**
            * @brief 返回URI
            */
           public function getUri(){
               
                if( !isset($_SERVER['REQUEST_URI']) ||  $_SERVER['REQUEST_URI'] == "" )
                {
                    // IIS
                    if (isset($_SERVER['HTTP_X_ORIGINAL_URL']))
                    {
                            $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_ORIGINAL_URL'];
                    }
                    else if (isset($_SERVER['HTTP_X_REWRITE_URL']))
                    {
                            $_SERVER['REQUEST_URI'] = $_SERVER['HTTP_X_REWRITE_URL'];
                    }
                    else
                    {
                            //pathinfo
                            if ( !isset($_SERVER['PATH_INFO']) && isset($_SERVER['ORIG_PATH_INFO'])){
                                    $_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
                            }

                            if ( isset($_SERVER['PATH_INFO']) ) {
                                    if ( $_SERVER['PATH_INFO'] == $_SERVER['SCRIPT_NAME'] ){

                                            $_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];
                                    }else{

                                            $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'] . $_SERVER['PATH_INFO'];
                                    }
                            }

                            //queryString
                            if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']))
                            {
                                    $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
                            }

                      }
                }
                return $_SERVER['REQUEST_URI'];
           }
           
            /**
             * @brief 返回URL
             */
            public function getUrl(){
                
                return $this->getHost().$this->getUri();
            }
            
            /*
             * @brief 返回脚本名
             */
            public function getRealSelf(){
                if(isset($_SERVER['PHP_SELF'])){
                    $real = $_SERVER['PHP_SELF'];
                }else if(isset($_SERVER['PATHINFO'])){
                    $real = $_SERVER['SCRIPT_NAME'].$_SERVER['PATH_INFO'];	
                }else if(isset($_SERVER['ORIG_PATH_INFO'])){
                    $real = $_SERVER['SCRIPT_NAME'].$_SERVER['ORIG_PATH_INFO'];	
                }else{
                    $real= null;	
                }	
                return $real;
            }
            
            public  function pathinfoToArray($url){

                $data = array();
                preg_match("!^(.*?)?(\\?[^#]*?)?(#.*)?$!",$url,$data);
                $rewrite_url_arr = array();

                if(isset($data[1]) && trim($data[1],"/"))
                {
                        $pathArr = explode("/",trim($data[1],"/"));
                        $key = null;
                        foreach($pathArr as $value)
                        {
                           if($key === null)
                           {
                                   $key = $value;
                                   $re[$key]="";
                           }
                           else
                           {
                                   $re[$key] = $value;
                                   $key = null;
                           }
                        }
                }

                $_GET = array_merge($_GET,$re);
                $_REQUEST = array_merge($_REQUEST,$re);

             }
           
           
    }

?>