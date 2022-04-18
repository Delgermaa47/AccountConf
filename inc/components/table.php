<?php
    class NewTable
    {
        public $header_details;
        public $className;
        public $body_datas;
        public $added_datas;

        function __set($propName, $propValue)
        {
            $this->$propName = $propValue;
        }

        public function diplay_table() {
            $table_class_name = $this->className ? $this->className : "table";
            $header_body_req = get_or_null($this->header_details['header_data']);
            $added_datas = get_or_null($this->added_datas);

            $body_datas = empty($this->body_datas) || gettype($this->body_datas) === 'string' ? [] : $this->body_datas;
            
            $header_body = '';
            $header_body_req = empty($header_body_req) ? [] : $header_body_req;

            foreach($header_body_req as $key=>$value) {
                extract($value);
                $header_body = $header_body.'<th scope='.$scope.'class='.$className.'>'.$value.'</th>';
            }

            $table_body = '';
            
            foreach($body_datas as $key=>$body_value) {
                $table_body = $table_body.'<tr>';
                foreach($header_body_req as $key=>$head_value) {
                    $table_body = $table_body.'<td class='.$head_value['className'].'>';
                    $field_name = $body_value[strtoupper($head_value['field'])];
                    $table_body = $table_body.($head_value['have_icon'] ? " " : $field_name);
                    
                    if($added_datas && $head_value['action']) {
                        $field_action = $added_datas[$head_value['key_name']];
                        $table_body = $table_body.$field_action($field_name);
                        
                    }

                    $table_body = $table_body.'</td>';
                }
                $table_body = $table_body.'</tr>';
            }
            
            return makeBlockHTML(
                "<table class='{$table_class_name}'>
                <thead>
                    <tr>
                        $header_body
                    </tr>
                </thead>
                $table_body
              </table>"
            ,"table-responsive");
        }

    }
?>
