<?php defined('IN_FATE') or die('Access denied!');

    /**
     *
     * @brief URL处理类
     * @param $suffix       模式全局URL后缀
     * @param $caseSentive  模式全局大小写设置
     * @param $rules     URL路由规则
     * @param $format    当前url模式
     * @param $references   标签数组
     * @param $patterns     路由模式数组 
     * @param $routes       路由配置
     *
     **/

     class IUrl extends IComponent {

            const NATIVE_FORMAT   = 'native';
            const PATHINFO_FORMAT = 'pathinfo';
            const DIY_FORMAT      = 'diy';      //未想好

            protected  $suffix      = '';
            protected  $caseSentive = false;
            protected  $rules    = array();
            protected  $format   = self::PATHINFO_FORMAT;
            protected  static $references  = array();
            protected  static $partterns  = array();
            protected  static $routes = array();
            protected  static $self = NULL;
                        
            /**
             * @brief 解析urlRules
             **/
            public  function parseUrlRules(){
                

                 

                if(!empty($this->rules)){ //如果存在路由规则

                        $tr2['/']=$tr['/']='\\/';  //用于strtr的替换 把 '/' 替换为 '\\/'

                        $i = 0; //计数

                        foreach($this->rules as $pattern=>$route){ //遍历路由规则

                            //设置路由配置				
                            if(is_array($route)){					        
                                    self::$routes[$i] = $route;
                            }else{
                                    self::$routes[$i] = array($route);
                            }

                            $route =  trim(self::$routes[$i][0],'/');

                            //优先标签 
                            if(strpos($route,'<')!==false && preg_match_all('/<(\w+)>/',$route,$routeMatches)){ 
                                            foreach($routeMatches[1] as $name){
                                                    self::$references[$name] = "<$name>";
                                            }
                            }

                            if(preg_match_all('/<(\w+):?(.*?)?>/',$pattern,$patternMatches)){ //解析模式规则

                                    //合并正则匹配到的键值 $patternMatches[1]为键数组  $patternMatches[2]为值数组

                                    $params =array_combine($patternMatches[1],$patternMatches[2]); 

                                    foreach($params as $name=>$value) //分配参数
                                    {
                                            if($value===''){
                                                    $value='[^\/]+';
                                            }

                                            $tr["<$name>"]="(?P<$name>$value)";

                                            if(isset(self::$references[$name])){
                                                    $tr2["<$name>"]=$tr["<$name>"];
                                            }else{
                                                    self::$parttens[$i]['params'][$name]=$value;
                                            }
                                    }

                            }

                            $p=rtrim($pattern,'*');   //如果正则式以*结尾则不是完全匹配 
                            $append =  $p!==$pattern; //是否在结尾追加
                            $p=trim($p,'/');
                            
                            $temp =preg_replace('/<(\w+):?.*?>/','<$1>',$p); //把正则替换成标签 <key>

                            $pattern='/^'.strtr($temp,$tr).'\/'; //把正则的<key> 解析成上方的(命名子模式)
                            var_dump($pattern);exit;
                            $pattern.=$append ?'/u':'$/u'; //确定是完全匹配还是模糊匹配		
                            self::$partterns[$i]['parttern'] = $pattern;



                        }

                }
                
                    return $this;

            }

            /**
             * @brief 解析url 返回路由数组
             **/
            public  function parseUrl(){

                switch($this->format){

                    case self::NATIVE_FORMAT:  	//原生模式

                        $route = '';
                        $route.= !empty($_GET['m'])?'/'.$_GET['m']:'';
                        $route.= !empty($_GET['c'])?'/'.$_GET['c']:'';
                        $route.= !empty($_GET['a'])?'/'.$_GET['a']:'';

                    return $route;
                    
                    break;

                    case self::PATHINFO_FORMAT:	//PATHINFO模式

                            $uri = self::getRealSelf();
                            preg_match('/\.php(.*)/',$uri,$matchAll);
                            $pathInfo = $matchAll[1]; 

                            foreach(self::$partterns as $i=>$patternArr)
                            {	
                                    //定义配置
                                   // $caseSentive = !empty(self::$routes[$i]['caseSentive'])? self::$routes[$i]['caseSentive']:self::$caseSentive;	
                                    //$suffix = !empty(self::$routes[$i]['suffix'])? self::$routes[$i]['suffix']:$this->suffix;
                                   $defaultParams = !empty(self::$routes[$i]['defaultParams'])? self::$routes[$i]['defaultParams']:array();	    
                                    //修正paInfo
                                    //$pathInfo  = empty($suffix)? $pathInfo : substr($pathInfo,0,-strlen($suffix));					
                                    $pathInfo.='/';
                                    //修正$pattern
                                    //$case = $caseSentive ?'i':'';  
                                    $pattern = $patternArr['parttern'].'i';
                                    var_dump($pattern);
                                    var_dump($pathInfo);
                                    if(preg_match($pattern,$pathInfo,$matches))
                                    {
                                            var_dump($matches);exit;
                                            //把默认参数添加到$_GET $_REQUEST
                                            $_GET = array_merge($defaultParams,$_GET);
                                            $_REQUEST = array_merge($defaultParams,$_REQUEST);								
                                            $tr=array();
                                            //把匹配的参数添加到$_GET $_REQUEST
                                            foreach($matches as $key=>$value)   
                                            {
                                                    if(isset(self::$references[$key]))
                                                            $tr[self::$references[$key]]=$value;
                                                    elseif(isset($patternArr['params'][$key]))
                                                            $_REQUEST[$key]=$_GET[$key]=$value;
                                            }
                                            //如果不是完全匹配 则继续解析pathInfo 到$_GET $_REQUEST
                                            if($pathInfo!==$matches[0]){			 
                                                   $this->pathinfoToArray(ltrim(substr($pathInfo,strlen($matches[0])),'/'));
                                            }

                                            if(self::$patternArr['routePattern']!==null){ 
                                                    $pathInfo = strtr(self::$routes[$i],$tr);
                                            }else{
                                                    $pathInfo = $patternArr['route'];
                                            }	

                                            break;
                                    }

                            }

                            return $pathInfo;

                            break;

                            case self::DIY_FORMAT: 

                                return null; 	
                            break;

                }
            }


            /**
             *
             * @brief 把pathinfo 解析到 数组
             * @param $url
             * 
             **/
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

            /**
             *
             * @brief 获取网站域名
             * @param string $protocol 协议  默认为http协议，不需要带'://'
             *
             **/
            public  function getHost($protocol='http')
            {
                    $port  = $_SERVER['SERVER_PORT'] == 80 ? '' : ':'.$_SERVER['SERVER_PORT'];
                    $host	 = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
                    $baseUrl = $protocol.'://'.$host.$port;
                    return $baseUrl;
            }

            /**
             * @brief 获取当前执行脚本的名称 对单一入口来说 就是入口文件
             **/
             public  function getScriptName(){

                     return basename($_SERVER['SCRIPT_NAME']);
             }

            /**
             * @brief 获取站点根目录
             **/
            public  function getWebDir()
            {   
                $re=str_replace('\\','/',dirname($_SERVER['SCRIPT_NAME']));
                if($re!='/')
                    $re .='/';
                return $re;
            }

            /**
             *
             * @brief 获取当前url地址
             *
             **/
            public function getUrl()
            {
                 return self::getHost().'/'.self::getUri();
            }

            /**
             *
             * @brief 获取当前URI地址
             *
             **/
            public function getUri()
            {
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
             *
             * @brief 返回脚本名称前相对document_root 的相对路劲并包含/pathinfo 不包括querystring
             *
             **/
             public  function getRealSelf(){
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

 }

?>




