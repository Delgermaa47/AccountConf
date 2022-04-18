<?php

    function print_arr_values($arr, $desc=""){
        echo $desc;
        echo '<pre>';
        print_r($arr);
        echo '<pre>';

    }

    function console_log($any_data, $desc="") {
        $desc = empty($desc) ?  " " : $desc.": ";
        echo "<br>".$desc.$any_data."<br>";
    }

    function new_line() {
        echo "<br>";
    }

    function check_string( $key_value ) {
        if(gettype($key_value) === 'string') {
            $key_value = "'".$key_value."'";
        }
        return $key_value;
    }

    
    function replace_string($old_str, $new_str, $main_str) {
        // $request_url = replace_string('/', '\\', $request_url
        return str_replace($old_str, $new_str, $main_str);
    }

    function get_or_null(&$var, $default=null) {
        return isset($var) ? $var : $default;
    }

    function makeBlockHTML($replStr, $_element_class){
        $template = 
            '<div class=$_element_class>
                $str
            </div>';
    return strtr($template, array( '$str' => $replStr, '$_element_class'=>$_element_class));
    }

    function write_to_file($data, $path="", $filename="new_file.txt"){
        $myfile = fopen($path.$filename, "w") or die("Unable to open file!");
        fwrite($myfile, $data);
        fclose($myfile); 
    }

    function redirect($uri) {
        header('Location: '.$uri);
    }

    function now() {
        return date('Y/m/d');
    }

    function file_post_contents($url, $data)
    {
        $postdata = http_build_query($data);
    
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );
    
        // if($username && $password)
        // {
        //     $opts['http']['header'] = ("Authorization: Basic " . base64_encode("$username:$password"));
        // }
    
        $context = stream_context_create($opts);
        return file_get_contents($url, false, $context);
    }
?>