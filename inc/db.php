<?php

    $db_user = 'gb';
    $db_pass = 'gb96gb318454';
    $db_name = 'gb96';
    $db_host = '172.29.2.71';
    $db_port = '1521';
    
    ini_set("dipley_errors", 1);
    
    $conn = oci_connect($db_user, $db_pass, $db_host.'/'.$db_name, 'AL32UTF8');

    if (!$conn) {
        $e = oci_error();
        echo htmlentities($e['message'], ENT_QUOTES);
        unset($db_user); unset($db_pass);
    }
    
    define('PG_Conn', $conn); 

    function fetch_rows($stid) {
        $res = [];
        while (($row = oci_fetch_array($stid, OCI_ASSOC+OCI_RETURN_LOBS)) != false) {
            $row = array_change_key_case($row, CASE_LOWER);
            array_push($res, $row);
        }
        return $res;
    }
 
    
    function sql_execute($sql, $params=[]) {
        $query = strtr($sql, $params);
        $stid = oci_parse(PG_Conn, $query);
        oci_execute($stid);
        return $stid;
    }

    function _select($query, $params) {
        $stid = sql_execute($query, $params);
        return fetch_rows($stid);
    }

    function bulk_insert($query, $datas ) {
        
        $lastInvNo = "";
        $bla = $query.'('.join(", ", array_map("check_string", $datas)).')';
        $bla = $bla.' RETURNING invno INTO :invno';
        
        $parsed = oci_parse(PG_Conn, $bla);
        oci_bind_by_name($parsed, ":invno", $lastInvNo,32);
        oci_execute($parsed);
        
        return  intval($lastInvNo);
    }
?>
