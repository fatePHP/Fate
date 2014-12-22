<?php
    /*
     * @brief URL组件
     * @param $rules  路由规则 
     * @param $ruleMetas  路由规则元数据
     * @param $format URL格式
     * @param $tags   路径标签数组
     * @param $partterns 路由模式数组
     * @param $routes    路径数组 
     */
    
    class IUrl extends IComponent{
        
        
           public $rules=array();
           
           public $ruleMetas = array();
           
           public $format='normal';
           
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
    

                    $p = rtrim($parttern,'*'); 
                    $append = ($p!==$parttern);                 
                    $temp_p =preg_replace('/<(\w+):?.*?>/','<$1>',$p); 
                    $parttern='/^'.strtr($temp_p,$temp); 
                    $parttern.=$append ?'/u':'$/u';	
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
                     
                     foreach($this->partterns as $i=>$parttern)
                     {      
                          $pathInfo = empty($matchAll)? '':$matchAll['1'];
                          $meta =  isset($this->ruleMetas[$i])? $this->ruleMetas[$i]:array();
                          $caseSentive = isset($meta['caseSentive'])? $meta['caseSentive']:false;	
                          $params = !empty($meta['params'])? $meta['params']:array();                               
                          $suffix = isset($meta['suffix'])?$meta['suffix']:'';

                          $pathInfo  = empty($suffix)? $pathInfo : substr($pathInfo,0,-strlen($suffix));
                          $case = $caseSentive? 'i':'';  
                          $parttern = $parttern.$case;
                          if(preg_match($parttern,$pathInfo,$matches))
                          {     
                                
                                $_GET = array_merge($params,$_GET);
                                $_REQUEST = array_merge($params,$_REQUEST);
                                $temp=array();
 
                                foreach($matches as $key=>$value)   
                                {    $temp["<".$key.">"] = $value;
                                     $_REQUEST[$key]=$_GET[$key]=$value;
                                }
                                
                                if(rtrim($pathInfo,'/')!==rtrim($matches[0],'/')){
                                    
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