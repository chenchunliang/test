<?php
date_default_timezone_set("Asia/Taipei");//設定台灣時區
@session_start();

class Functions extends PDO
{
    //屬性
    private $_dsn;
    
    private $_user = "root"; //資料庫帳號(不用改)
    private $_pass = "00000000"; //資料庫密碼(要改)
    
    private $_encode = "utf8";//編碼方式(不用改)
    private $_stmt;
    private $_data = array();
    private $_last_insert_id;
    
    function __construct($db) 
    {
								//資料庫IP位址(本機不用改，放在網路上要改)
        $this->_dsn = "mysql:host=127.0.0.1;dbname=" . $db; //設定DataBase
        try {
            parent::__construct($this->_dsn, $this->_user, $this->_pass);
            $this->_setEncode();
        }
        catch (Exception $e) {
            print_r($e);
        }
    } //end construct 建構子
    
    function __set($name, $value)
    {
        $this->_data[$name] = $value;
    } //end set
    
    function __get($name)
    {
        if (isset($this->_data[$name]))
            return $this->_data[$name];
        else
            return false;
    } //end get
    
    private function _setEncode()
    {
        $this->query("SET NAMES '{$this->_encode}'");
    } //end setEncode 設定為UTF8連線編碼
    
    function bindQuery($sql, $bind = array())
    {
        $this->_stmt = $this->prepare($sql);
        $this->_bind($bind);
        $this->_stmt->execute();
        return $this->_stmt->fetchAll();
    } //end bindQuery   執行查詢
    
    private function _bind($bind) //綁定欄位型態
    {
        foreach ($bind as $key => $value) {
            $this->_stmt->bindValue($key, $value, is_numeric($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
    } //end bind 綁定植
    
    function error()
    {
        $error = $this->_stmt->errorInfo();
        echo "<br>" . 'errorCode:' . $error[0] . "<br>";
        echo 'errorString:' . $error[2] . "<br>";
    } //end error  顯示錯誤訊息
    
    function getData()
    {
        return $this->_data;
    } //end getData  取得DATA
    
    function getlastInsertId()
    {
        return $this->_last_insert_id;
    }
    
    //=====================================================通用SQL方法===============================================
    function select($table, $where = array())
    {
        $data        = array_merge($this->_data, $where); // array_merge 陣列合併
        $wherevalues = array();
        $bind_data   = array();
        
        //查詢的SQL語法：select `field1`,`field2` from `table` where `conditionfield` = 'conditionvalue';
        
        foreach ($data as $key => $value) {
            $wherevalues[] = "`" . $key . "` like :$key";
        }
		
        $sql = ($wherevalues) ? "select * from `{$table}` where " . implode(' and ', $wherevalues) . ";" : "select * from `{$table}`;";
        return $this->bindQuery($sql, $where);
        
    } //end select查詢
    
    function selectLimit($table, $where = array(), $start, $rows)//(從0算起第$start筆，往下抓$rows筆
    {
        $data        = array_merge($this->_data, $where); // array_merge 陣列合併
        $wherevalues = array();
        $bind_data   = array();
        
        foreach ($data as $key => $value) {
            $wherevalues[] = "`" . $key . "` like :$key";
        }
		
        $sql = ($wherevalues) ? "select * from `{$table}` where " . implode(' and ', $wherevalues) . " limit $start,$rows;" : "select * from `{$table}` limit $start,$rows;";
        return $this->bindQuery($sql, $where);
        
    } //end selectLimit查詢
    
    function selectOffset($table, $where = array(), $offsetQ)//略過前面$offsetQ筆
    {
        $data        = array_merge($this->_data, $where); // array_merge 陣列合併
        $wherevalues = array();
        $bind_data   = array();
        
        foreach ($data as $key => $value) {
            $wherevalues[] = "`" . $key . "` like :$key";
        }
		
        $sql = ($wherevalues) ? "select * from `{$table}` where " . implode(' and ', $wherevalues) . "limit 100000000 OFFSET $offsetQ;" : "select * from `{$table}` limit 100000000 OFFSET $offsetQ;";
        return $this->bindQuery($sql, $where);
        
    } //end selectOffset查詢
    
    function selectGroupBy($table, $where = array(), $groupfield = array())
    {
        $data             = array_merge($this->_data, $where); // array_merge 陣列合併
        $wherevalues      = array();
        $bind_data        = array();
        $groupfieldvalues = array(); //EX:欄位1=>ASC , 欄位2=>DESC
        
        foreach ($data as $key => $value) {
            $wherevalues[] = "`" . $key . "` like :$key";
        }
		
        foreach ($groupfield as $key => $value) {
            $groupfieldvalues[] = "`" . $key . "` " . $value;
        }
		
        $sql = ($wherevalues) ? "select * from `{$table}` where " . implode(' and ', $wherevalues) . " group by " . implode(' , ', $wherevalues) . ";" : "select * from `{$table}` group by " . implode(' , ', $wherevalues) . ";";
        return $this->bindQuery($sql, $where);
        
        
    } //end selectGroupBy查詢	
    
    function selectInnerJoin($table1, $table2, $on = array())
    {
        $data      = array_merge($this->_data, $on); // array_merge 陣列合併
        $onvalues  = array();
        $bind_data = array();
        
        foreach ($data as $key => $value) {
            $onvalues[] = "`" . $key . "` like $value";
        }
		
        $sql = "select * from `{$table1}` inner join `{$table2}` on " . implode(' and ', $onvalues) . ";";
        return $this->bindQuery($sql, $on);
        
    } //end selectInnerJoin 查詢
    
    function selectInnerJoinOrderby($table1, $table2, $on = array(), $orderbyfield = array())
    {
        $data               = array_merge($this->_data, $on); // array_merge 陣列合併
        $onvalues           = array();
        $bind_data          = array();
        $orderbyfieldvalues = array(); //EX:欄位1=>ASC , 欄位2=>DESC
        
        foreach ($data as $key => $value) {
            $onvalues[] = "`" . $key . "` like $value";
        }
		
        foreach ($orderbyfield as $key => $value) {
            $orderbyfieldvalues[] = "`" . $key . "` " . $value;
        }
		
        $sql = "select * from `{$table1}` inner join `{$table2}` on " . implode(' and ', $onvalues) . " order by " . implode(' and ', $orderbyfieldvalues) . ";";
        return $this->bindQuery($sql, $on);
        
    } //end selectInnerJoinOrderby查詢
    
    function selectInnerJoinGroupBy($table1, $table2, $on = array(), $groupfield = array())
    {
        $data      = array_merge($this->_data, $on); // array_merge 陣列合併
        $onvalues  = array();
        $bind_data = array();
        
        foreach ($data as $key => $value) {
            $onvalues[] = "`" . $key . "` like $value";
        }
		
        foreach ($groupfield as $key => $value) {
            $groupfieldvalues[] = "`" . $key . "` " . $value;
        }
		
        $sql = "select * from `{$table1}` inner join `{$table2}` on " . implode(' and ', $onvalues) . " group by " . implode(' and ', $groupfieldvalues) . ";";
        return $this->bindQuery($sql, $on);
        
    } //end selectInnerJoinGroupBy查詢
    
    function selectMax($table, $maxfield = array(), $where = array())
    {
        $data        = array_merge($this->_data, $where); // array_merge 陣列合併
        $wherevalues = array();
        $bind_data   = array();
        
        foreach ($data as $key => $value) {
            $wherevalues[] = "`" . $key . "` like :$key";
        }
        
        foreach ($maxfield as $key => $value) {
            $maxfieldvalues[] = "MAX(`{$value}`) ";
        }
        
        $sql = ($wherevalues) ? "select " . implode(' , ', $maxfieldvalues) . " from `{$table}` where " . implode(' and ', $wherevalues) . ";" : "select " . implode(' , ', $maxfieldvalues) . " from `{$table}`;";
        return $this->bindQuery($sql, $where);
    } //end selectMax查詢
    
    function selectOrderBy($table, $where = array(), $order = array())
    {
        $data        = array_merge($this->_data, $where); // array_merge 陣列合併
        $wherevalues = array();
        $bind_data   = array();
        
        foreach ($data as $key => $value) {
            $wherevalues[] = "`" . $key . "` like :$key";
        }
		
        foreach ($order as $key => $value) {
            $orderfield[] = "`$key` $value";//EX:欄位1=>ASC , 欄位2=>DESC
        }
        
        $sql = ($wherevalues) ? "select * from `{$table}` where " . implode(' and ', $wherevalues) . " order by " . implode(' , ', $orderfield) . ";" : "select * from `{$table}` order by " . implode(' , ', $orderfield) . ";";
        return $this->bindQuery($sql, $where);
        
    } //end selectOrderBy查詢
    
    function selectBetween($table, $where = array(), $betweenfield, $betweenSTARTrange, $betweenENDrange)
    {
        $data        = array_merge($this->_data, $where); // array_merge 陣列合併
        $wherevalues = array();
        $bind_data   = array();
        
        foreach ($data as $key => $value) {
            $wherevalues[] = "`" . $key . "` like :$key";
        }
		
        $sql = ($wherevalues) ? "select * from `{$table}` where " . implode(' and ', $wherevalues) . " and $betweenfield between `{$betweenSTARTrange}` and `{$betweenENDrange}`;" : "select * from `{$table}` between `{$betweenSTARTrange}` and `{$betweenENDrange}`";
        return $this->bindQuery($sql, $where);
        
    } //end selectBetween查詢
    
    function selectSum($table, $sumfield = array(), $where = array())
    {
        $data        = array_merge($this->_data, $where); // array_merge 陣列合併
        $wherevalues = array();
        $bind_data   = array();
        
        foreach ($data as $key => $value) {
            $wherevalues[] = "`" . $key . "` like :$key";
        }
        
        foreach ($sumfield as $key => $value) {
            $sumfieldvalues[] = "SUM(`{$value}`) ";
        }
        
        $sql = ($wherevalues) ? "select " . implode(' , ', $sumfieldvalues) . " from `{$table}` where " . implode(' and ', $wherevalues) . ";" : "select " . implode(' , ', $sumfieldvalues) . " from `{$table}`;";
        return $this->bindQuery($sql, $where);
    } //end selectSum查詢
    
    
    //***************************************************************
    
    function insert($table, $param = array())
    {
        $data      = array_merge($this->_data, $param); // array_merge 陣列合併
        $columns   = array_keys($data); //array_keys 陣列索引值
        $values    = array();
        $bind_data = array();
        
        //新增的SQL語法：insert into table (`field1`,`field2`) values ('value1','value2') ;
        
        foreach ($data as $key => $value) {
            $values[]             = ":{$key}";
            $bind_data[":{$key}"] = $value;
        }
        $sql         = "INSERT INTO `{$table}` (`" . implode('`,`', $columns) . "`) VALUES (" . implode(',', $values) . ");";
        $this->_stmt = $this->prepare($sql);
        $this->_bind($bind_data);
        $this->_stmt->execute();
        return $this->_last_insert_id = $this->lastInsertId(); //將最後insert ID存入_last_insert_id屬性
        
    } //end insert新增
    
    function update($table, $param = array(), $id = false)
    {
        if ($id == false && !($id = $this->id)) { //判斷id是否存在
            return false;
        } else {
            $data      = array_merge($this->_data, $param); // array_merge 陣列合併
            $columns   = array_keys($data); //array_keys 陣列索引值
            $bind_temp = array();
            $bind_data = array();
            
            //更新的SQL語法：update table set `field1`='value1',`field2`='value2' where `id`='id';
            
            foreach ($data as $key => $value) {
                $bind_temp[]          = "`{$key}`=:{$key}";
                $bind_data[":{$key}"] = $value;
            }
            $sql         = "UPDATE `{$table}` SET " . implode(',', $bind_temp) . " where `{$table}_id` = :id;";
            $this->_stmt = $this->prepare($sql);
            $this->_bind(array(
                ":id" => $id
            )); //檢查id欄位型態
            $this->_bind($bind_data); //檢查欄位型態
            return $this->_stmt->execute();
        } //end else
    } //end update更新
    
    function upadteFK($table, $param = array(), $where = array())
    {
        $data        = array_merge($this->_data, $param); // array_merge 陣列合併
        $columns     = array_keys($data); //array_keys 陣列索引值
        $bind_temp   = array();
        $bind_data   = array();
        $wherevalues = array();
        
        //更新的SQL語法：update table set `field1`='value1',`field2`='value2' where `id`='id';
        //更新的欄位
        foreach ($data as $key => $value) {
            $bind_temp[]          = "`{$key}`=:{$key}";
            $bind_data[":{$key}"] = $value;
        }
        //更新的where條件式
        foreach ($where as $key => $value) {
            $sqlwhere[]             = "`{$key}` like :$key";
            $wherevalues[":{$key}"] = $value;
        }
        
        $sql         = "UPDATE `{$table}` SET " . implode(',', $bind_temp) . " where " . implode(' and ', $sqlwhere) . ";";
        $this->_stmt = $this->prepare($sql);
        $this->_bind($bind_data); //檢查欄位
        $this->_bind($wherevalues); //檢查where
        return $this->_stmt->execute();
    } //end upadteFK更新	
    
    function delete($table, $id = false)
    {
        if ($id == false && !($id = $this->id)) { //判斷id是否存在
            return false;
        } else {
            $sql         = "delete from `{$table}` where `{$table}_id` = :id;";
            $this->_stmt = $this->prepare($sql);
            $this->_bind(array(
                ":id" => $id
            )); //檢查id欄位型態
            return $this->_stmt->execute();
        } //end else
    } //end delete刪除
    
    function deleteFK($table, $where = array())
    {
        //刪除的where條件式
        foreach ($where as $key => $value) {
            $sqlwhere[]             = "`{$key}` like :$key";
            $wherevalues[":{$key}"] = $value;
        }
        
        $sql         = "delete from `{$table}` where " . implode(' and ', $sqlwhere) . ";";
        $this->_stmt = $this->prepare($sql);
        $this->_bind($wherevalues); //檢查where
        return $this->_stmt->execute();
        
    } //end deleteFk刪除
    
    
	
	
    //======================================自訂SQL方法====================================
										//參數列可自行增加
    function analysis_print_displaylist($datasheet_startdate, $datasheet_enddate, $customer_id, $product_group)
    {
        $sql = "select * from `datasheet` inner join `product` on `datasheet`.`product_id` = `product`.`product_id` and `datasheet`.`datasheet_date` >= '{$datasheet_startdate}' and `datasheet`.`datasheet_date` <='{$datasheet_enddate}' and `datasheet`.`customer_id` like '{$customer_id}' and `product`.`product_group` like '{$product_group}' order by `datasheet`.`datasheet_date` , `datasheet`.`customer_id` , `datasheet`.`datasheet_quantity` desc";
        //$sql內的文字替換成SQL語法
        
        $this->_stmt = $this->prepare($sql);
        $this->_stmt->execute();
        return $this->_stmt->fetchAll();
    }
	
	
	
} //end class


?>