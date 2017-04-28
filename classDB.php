<?php

define("BR", '<br>');

class ClassDB
{
    var $domain_name = '';
    var $run = False;  // allow write db
    var $localhost = '127.0.0.1';
    var $username = 'local_user';
    var $password = 'local_password';

    function __construct($domain, $dbname)
    {
        $this->domain_name = $domain;
        $this->mysqli = new mysqli($this->localhost, $this->username, $this->password, $dbname);
        if ($this->mysqli->connect_errno) {
            echo "Не удалось подключиться к MySQL: (" . $this->mysqli->connect_errno . ") " . $this->mysqli->connect_error;
            exit;
        }
        $this->init();
    }

//*****************************************************************************************
    function init(){
        // tests
        echo BR."---------------------------------------------------------------> ";
        echo $this->domain_name.BR;
    }

//*****************************************************************************************
    function clone_table($table, $dbname){
        $str = "SELECT DATABASE() as db";
        $tmp_arr = $this->query($str);
        $curr_db = $tmp_arr['db'];

        $str = "DROP TABLE IF EXISTS $table";
        $this->query($str);

        $str = "CREATE TABLE $curr_db.$table LIKE $dbname.$table";
        $this->query($str);

        $str = "INSERT INTO $curr_db.$table SELECT * FROM $dbname.$table";
        $this->query($str);
    }

//*****************************************************************************************
    function select($tablename, $sort_column = False, $where = False){
        if ($sort_column === False and $where === False){
            $str = "SELECT * FROM $tablename";
        } elseif($where === False) {
            $str = "SELECT * FROM $tablename ORDER BY $sort_column ASC";
        } else {
            $str = "SELECT * FROM $tablename $where ORDER BY $sort_column ASC";
        }
        return $this->query($str);
    }

//*****************************************************************************************
    function last_id ($id, $tablename){
        $l_id = $this->query("SELECT max(LAST_INSERT_ID($id)) as last_id FROM $tablename");
        if (empty($l_id['last_id']))
            $last_id = 1;
        else $last_id = $l_id['last_id'];
        return $last_id;
    }

//*****************************************************************************************
    function write_db($data, $tablename, $skip_column = False){
        foreach ($data as $num ) {
            $str = "INSERT INTO $tablename SET";
            foreach ($num as $k => $v) {
/*------------------------------------------------------------*/
/*------------------------ filters ---------------------------*/
                if ($skip_column !==False AND $k == $skip_column)
                    continue;

                if (is_float($v) || is_int($v) || is_numeric($v))
                    $str .= " $k = ".$this->mysqli->real_escape_string($v).", ";
                else
                    $str .= " $k = '".$this->mysqli->real_escape_string($v)."', ";
/*------------------------------------------------------------*/
            }
            echo $str.BR.BR;
            if ($this->run){
                $this->insert($str, True);
            }
        }
    }

//*****************************************************************************************
    function insert($query, $sub = False){
        if ($sub)
            $query = substr($query, 0, strlen($query)-2); // Del space and comma
        
        if(!$this->mysqli->query($query)){
            echo "Не удалось выполнить запрос".BR;
            echo $this->mysqli->error.BR;
            echo $query.BR."========== ERROR =============".BR;
            die();
        }
        return True;
    }

//*****************************************************************************************
    function query ($q){
        if (!$res = $this->mysqli->query($q)){
            die("MySQL Error -> ".$this->mysqli->error);
        }
        
        if ($res === True)
            return True;

        if ($res->num_rows > 1 )
            return $res->fetch_all(MYSQLI_ASSOC);
        else
            return $res->fetch_assoc();
    }

//*****************************************************************************************
    function __destruct(){
        $this->mysqli->close();
    }
}