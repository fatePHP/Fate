<?php  defined('IN_FATE') or die('Access denied');
        
        /**
         * @brief 数据库接口类
         */
        interface IDataBase {
                
                public function connect($host,$user,$pwd,$pconnect);
                    
                public function query($sql);
                
                public function insert($tb,$data);
                
                public function delete($tb,$where);
                
                public function update($tb,$data,$where);
                
        }


?>