<?php defined('IN_FATE') or exit('Access denied');
			
    /**
     * @brief  mysql操作类
     * @param  $db_link       链接句柄
     * @param  $is_show_error 是否显示错误
     **/
			 
    class IMysql {

            protected $db_link; 
            protected $is_show_error;

            /**
             * @brief 初始化函数
             * @param $config 配置文件
             **/
            public function __construct($config){

                    $this->is_show_error = !empty($config['show_error'])? $config['show_error']:true;
                    $this->db_link = $this->connect($config['host'],$config['user'],$config['pwd'],$config['pconnect'])  or 
                    $this->show_error_msg('can\'t connect to Mysql Server');
                    $this->selectDb($config['name']) or $this->show_error_msg("Database '{$config['name']}' does not exist");
                    if(!empty($config['charset'])){
                        $this->query("set names ".$config['charset']);
                    }
             }

              /**
               * @brief 连接数据库
               **/
              public function connect($host,$userName,$pwd,$isPconnect){  

                    $func = $isPconnect?'mysql_pconnect':'mysql_connect';
                    return $func($host,$userName,$pwd);  
              }

              /**
               * @brief 选择数据库
               **/
              public function selectDb($dbName){

                     return  mysql_select_db($dbName,$this->db_link);
              }

              /**
               * @brief 执行sql语句 获取结果集
               **/
              public function query($sql,$cacheResult=true){

                    $func	=	$cacheResult?'mysql_query':'mysql_unbuffered_query';
                    $result = @$func($sql) or $this->show_error_msg('错误的SQL语句 "'.$sql.'"');
                    return $result;
              }

              /**
               * @brief 解析结果集
               **/
              public function fetchArray($result,$type='assoc'){

                    return ($type=='assoc')?mysql_fetch_array($result,MYSQL_ASSOC):mysql_fetch_array($result,MYSQL_NUM);
              }

              /**
               * @brief 执行sql语句 获取所有数据
               **/ 
              public function fetchAll($sql,$type='assoc',$cacheResult=true){

                    $result = $this->query($sql,$cacheResult);
                    $all = array();
                    while( $value = $this->fetchArray($result,$type)){

                                    $all[]=$value;	
                    }
                    $this->free($result);
                    return $all;
              }

              /**
               * @brief 执行sql语句 获的一条数据
               **/
              public function fetchOne($sql,$type='assoc',$cacheResult=true){

                    $result = $this->query($sql,$cacheResult);
                    $one    = $this->fetchArray($result,$type);
                    $this->free($result);
                    return $one;
              }

              /**
               * @brief 插入操作
               **/	  
              public function insert($tbName,$arr){

                    $str = "(`".implode('`,`',array_keys($arr))."`) VALUES ('".implode("','",$arr)."')";
                    $sql= "INSERT INTO `$tbName` $str";
                    if($this->query($sql)){
                        return $this->insertId();
                    }
                    
                    return false;
              }

              public function insertStr($tbName,$fields,$values){

                    $sql ="INSERT INTO `$tbName` $fields VALUES $values";
                    return $this->query($sql);	
              }

              /**
               * @brief 修改操作
               **/
              public function update($tbName,$arr,$where='1=1'){

                    $str='';
                    foreach($arr as $k=>$v){
                            $str.=" `$k`='{$v}',";
                    }
                    $str=ltrim(',',$str);
                    $sql = "UPDATE `$tbName` SET $str WHERE $where";
                    return $this->query($sql);
              }

              /**
               * @brief 获取主键
               **/
              public function getPrimary($tbName){

                    $result = $this->query("SHOW COLUMNS FROM $tbName");
                    while($r = $this->fetchArray($result))
                    {
                            if($r['Key'] == 'PRI') break;
                    }
                    $this->free($result);
                    return $r['Field'];
              }

              /**
               * @brief 获取指定表字段
               **/
              public function getFields($tbName){

                    $fields = array();
                    $result = $this->query("SHOW COLUMNS FROM $tbName");
                    while($value = $this->fetchArray($result)){

                                            $fields[]=$value['Field'];
                    }
                    $this->free($result);
                    return $fields;
              }

              /**
               * @brief 获取数据库中所有表 预留
               **/
              public function getTables(){

              }

              /**
               * @brief 上个insert操作 产生的Id号
               **/	  
              public function insertId(){

                      return  mysql_insert_id($this->db_link);
              }

              /**	  
               * @brief 上一个Mysql操作 影响的行数
               **/
              public function affectedRows(){  

                     return  mysql_affected_rows($this->db_link);
              }

              /**
               * @brief 取得结果集中的行数
               **/
              public function countRows($result){

                     return musql_num_rows($result);
              }

              /**
               * @brief 输出错误信息
               **/	  
              public function show_error_msg($msg){

                    if($this->is_show_error){
                            die("<br><b> Message : </b>$msg</br><b> MySQL Error : </b>".$this->error());
                    }
              }

              /**
               * @brief Mysql版本
               **/
              public function version(){     

                    return mysql_get_server_info($this->db_link);
              }

              /**
               * @brief 错误号
               **/
              public function errno(){       

                     return mysql_errno($this->db_link);
              }

              /**
               * @brief 错误信息
               **/
              public function error(){    

                      return mysql_error($this->db_link);
              }

             /**
              * @brief 释放资源
              **/
              public function free($result){

                      return mysql_free_result($result);
              }

              /**
               * @brief 关闭
               **/
              public function close(){ 

                      return mysql_close($this->db_link);	
              }

              /**
               * @brief explan 分析selec语句 查询优化器
               * ID 越大优先级越高
               **/
              public function explain($sql){

                    $explain = 'EXPLAIN '.$sql;
                    useTime('select_start');
                    useMemory('select_start');
                    $arr  = $this->fetchAll($explain);
                    useTime('select_end');
                    useMemory('select_end');
                    $table = "<table border='1px black solid;'><tr style='background:#eaeaea;'><td>id</td><td>select_type</td><td>table</td><td>type</td><td>possible_keys</td><td>key</td><td>key_len</td><td>ref</td><td>rows</td><td>Extra</td></tr>";

                    foreach($arr as $v){

                             $table.="<tr>";
                             foreach($v as $vv){
                                                    $str = empty($vv)?'NULL':$vv;
                                                    $table.="<td>".$str."</td>";
                             }

                             $table.="</tr>";
                    }	

                    $table .="</table><br>UseTime: ".useTime('select_start','select_end')."<br>UseMemory: ".useMemory('select_start','select_end')."KB";

                    echo $table;
              }

              /** 
               * @brief 查询优化 添加索引
               **/
              public function addIndex($tbName,$fields){

                    $str = is_array($fields)?implode('`,`',$fields):(is_string($fields)?$fields:false);
                    if($str){	  	
                        $sql ="ALTER TABLE `$tbName` ADD INDEX x (`{$str}`)";
                        $this->query($sql);
                    }
              }

              /**
               * @brief 删除索引
               **/
              public function dropIndex($tbName,$indexName){

                    $sql = "ALTER TABLE `$tbName` DROP INDEX x";
                    $this->query($sql);
              }

              /**
               * @brief 删除
               **/
            public function delete($tbName,$where){
                   $sql = "DELETE FROM `$tbName` WHERE $where";
                   $this->query($sql);
            }

    }

?>