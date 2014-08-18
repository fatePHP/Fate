<?php defined('IN_FATE') or die('Access denied');

		/**
		 * @brief 模型基类
		 * @param $db     数据库操作对象
		 * @param $fields 要查询的字段
		 * @param $join   关联字串
		 * @param $where  查询字串
		 * @param $order  排序字串
		 * @param $limit  取得条数
		 **/

		class IModel {
						
					protected $db='';      
					protected $fields='*'; 
					protected $join='';    
					protected $where='';
					protected $order='';  
					protected $limit='';   
					
					/**
					 * @breif 初始化函数
					 **/
					public function  __construct(){
						
								$this->db = new IDb;
				  }
				  				  
				  /**
				  * @brief 切换数据配置
				  **/
				  
				  /*
				  public function switch_db($config){
				  	
				  		   $this->db = $this->driver->analyze_db($config);
				  		   return $this;
				  }
				  */
				  
				  /**
				  * @brief 切换数据库驱动
				  **/
				  
				  /*
				  public function switch_db_driver($db_type,$dir=''){
				  	
				  			$this->db = $this->driver->analyze_db('',$db_type,$dir);
				  		  return $this;
				  }
				  */
				  
				 /**
				  * @brief 返回数据库对象
				  **/					  
				  public function db(){
				  		return $this->db;
				  }
				  
				 /**
				  *	@brief 设置fields字段
				  **/
				  
				  public function fields($fields='*'){
				  	
				  	$this->fields = $fields;
				  	return $this;
				  }
				  
				 /**
				  *	@brief 设置join字串 
				  **/
				 public function join($join=''){
					 	
					 	$this->join  = empty($join)?'':$join;
				 		return $this;
				 }
				 
				/**
				 * @brief 设置where字串
				 **/	 
				 public function where($where=''){
				 	
				 		$this->where  = empty($where)?'':' WHERE '.$where;
				 		return $this;
				 }
				 
				/**
				 * @brief 设置order字串
				 **/
				 public function order($order=''){
				 	
				 		$this->order  = empty($order)?'':' ORDER BY '.$order;
				 		return $this;
				 }
				 
				 /**
				 * @brief 设置 limit 字串
				 **/
				 public function limit($limit=''){
				 	
				 		$this->limit  = empty($limit)?'':' LIMIT '.$limit;
				 		return $this;
				 }
				 
				/**
				 * @brief 拼装sql 
				 **/
				 public function sql(){
				 		
				  	$sql = "SELECT ".$this->fields." FROM  ".$this->table.''.$this->join.$this->where.$this->order.$this->limit;
				 		return $sql;
				 }
				 
				 /**
				  * @brief 执行sql语句 获取所有数据
				  **/ 
				  public function find($sql,$type='assoc',$cacheResult=true){
				  				
							$result = $this->db()->query($sql,$cacheResult);
							$all = array();
							while( $value = $this->db()->fetchArray($result,$type)){
				
									$all[]=$value;	
							}
							$this->db()->free($result);
							return $all;
				  }
					  
				 /**
				  * @brief 执行sql语句 获的一条数据
				  **/
				  public function findOne($sql,$type='assoc',$cacheResult=true){
				  	
							$result = $this->db()->query($sql,$cacheResult);
						  $one    = $this->db()->fetchArray($result,$type);
						  $this->db()->free($result);
						  return $one;
				  }
					  
				 /**
					* @breif 插入操作
					**/	  
					public function insert($tbName,$arr){
					  	
					  	 	$str = "(`".implode('`,`',array_keys($arr))."`) VALUES ('".implode("','",$arr)."')";
					  	 	$sql= "INSERT INTO `$tbName` $str";
					  	 	return $this->db()->query($sql);
					}
					  
					public function insertStr($tbName,$fields,$values){
					  	
					  		$sql ="INSERT INTO `$tbName` $fields VALUES $values";
					  		return $this->db()->query($sql);	
					}
					  
				  /**
				   * @breif 修改操作
				   **/
				  public function update($tbName,$arr,$where='1=1'){
				  		
				  		$str='';
				  		foreach($arr as $k=>$v){
				  			$str.=" `$k`='{$v}',";
				  		}
				  		$str=rtrim($str,',');
				  		$sql = "UPDATE `$tbName` SET $str WHERE $where";
				  		return $this->db()->query($sql);
				  }
				  
				 /**
				  * @breif 删除操作 
				  **/
				  public function delete($tbName,$where){
				  	
				  		$sql = "DELETE FROM `$tbName` WHERE $where";
				  		$this->db()->query($sql);
				  }
		}

?>