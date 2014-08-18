<?php defined('IN_FATE') or die('Access denied');
			
			/**
			 * @brief 数据库驱动类
			 * @param $type 数据库类型
			 * @param $db   数据库对象
			 **/
			 
			class IDb {
				
						private $type;
						private $db;
						
						/**
						 * @brief 初始化函数 
						 **/
						public function __construct($type=''){
								
								$db_config = IApp::config('db');
								$this->type = empty($config['type'])?'mysql':$config['type'];
								$dbName = 'I'.ucfirst($this->type);
								$this->db = IApp::object($dbName,$db_config);
								return $this->db;
						}

			}
?>