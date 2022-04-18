<?php

    require_once ROOT."\\inc\\components\\validation.php";

    $custno = '90400005627';
    
    class ApiList
    {
        public $request_name;
        public $request_params;
        protected $invoice_status = [
            "paid"=>"төлөгдсөн",
            "revoked"=>"буцаагдсан",
            "approved"=>"шийдвэрлэгдсэн",
            "pending"=>"хүлээгдэж буй"
        ];
        
        function __set($propName, $propValue)
        {
            $this->$propName = $propValue;
        }

        public function request_res() {
            $request_name = strtolower($this->request_name);

            switch ($request_name) {
                case 'invoice-list':
                    return $this->inv_list("main");
                
                case 'invoice-save':
                    return $this->inv_save('main');
                
                case 'invoice-detail':
                        return $this->inv_detail();
                
                case 'invoice-edit':
                    return $this->inv_edit();
                            
                case 'delete-sent-invoice':
                    return $this->inv_sent_delete();
                
                case 'approve-rec-invoice':
                    return $this->approve_rec_delete();
                    
                case 'get-fname':
                    return $this->get_fname();
                    
                case 'invoice-recieve-list':
                    return $this->inv_recieve_list('main');              

                case 'revoke-rec-invoice':
                    return $this->inv_rec_delete();
                
                case 'invoice-rec-detail':
                    return $this->inv_rec_detail();

                case 'invoice-rec-paid':
                    return $this->inv_rec_paid();

                
                case 'invsent-history':
                    return $this->inv_list("history");
                                    
                case 'invrec-history':
                    return $this->inv_recieve_list("history");
                
                // case 'invsent-history-detail':
                //     return $this->inv_detail();
                
                // case 'invrec-history-detail':
                //     return $this->inv_rec_detail();
                
                case 'invoice-template':
                    return $this->inv_save("template");

                case 'invtemplate-list':
                    return $this->inv_list("template");

                // case 'invtemplate-detail':
                //     return $this->inv_detail();
                
                case 'invtemplate-apply':
                    return $this->inv_apply();

                case 'create-inv-tables':
                    return $this->create_tables();
            }

            return [];
        }

        protected function count_of_table($table_name){
            $query = '
                select 
                    count(*) as count
                from 
                   $table_name
            ';
            $params['$table_name'] = $table_name;

            $res = _select($query, $params);
            return $res[0]['count'];
        }

        public function get_total_page($perpage, $table_name) {
            $total_page = 1;
            $total_items = $this->count_of_table($table_name);

            if($perpage<=$total_items) {
                $total_page = $total_items/$perpage;
                $result_float = $total_items%$perpage;
                if ($result_float>0) {
                    $total_page += 1;
                }
            }
            return $total_page;
        }

        protected function get_req_params($query, $sort_name, $str_stnames, $table_name, $primary_key) {
            $sort_type = "desc";
            $total_page = 1;

            $last_id = get_or_null($_POST['last_id']);
            $is_prev_page = get_or_null($_POST['is_prev_page']);
            $req_perpage = get_or_null($_POST['perpage']);
            $req_sort_name = get_or_null($_POST['sort_name']);
            $req_sort_type = get_or_null($_POST['sort_type']);
            $req_custom_query = get_or_null($_POST['custom_query']);

            if ($req_sort_name) 
            {
                $sort_name = $req_sort_name;
                if (count($str_stnames)>0 && in_array($req_sort_name, $str_stnames)) {
                    $sort_name = check_string($sort_name);
                }
            }

            if ($req_sort_type) {
                $sort_type = $req_sort_type;
            }
          
            
            if (!empty($last_id)) {
                $params['$last_id'] = $last_id;
                $params['$primary_key'] = $primary_key;
                $is_prev = strtolower(strval($is_prev_page)); 
                if ($is_prev === 'true') {
                    $query = $query.' and $primary_key<$last_id ';
                }
                else {
                    $query = $query.' and $last_id<$primary_key ';
                }
            }
            
            $params['$sort_name'] = $sort_name;
            $params['$sort_type'] = $sort_type;
            $query = $query.' order by $sort_name $sort_type ';

            if ($req_perpage) {
                $total_page = $this->get_total_page($req_perpage, $table_name);
                $params['$perpage'] = $req_perpage;
                $query = $query.'fetch next $perpage rows only ';
            }
            return [
                "query"=>$query,
                "params"=>$params,
                "total_page"=>$total_page
            ];
        }

        protected function check_invoice_status($params) {
            $params['$invstatus'] = check_string($this->invoice_status['pending']);
            $params['$invtype'] = check_string('history');
            $query = '
                select * from 
                    vbismiddle.invoicesent
                where
                    invno=$invno
                        and 
                    invstatus=$invstatus';
            $res = _select($query, $params);

            if(count($res)<1) {
                $params['$invstatus'] = check_string($this->invoice_status['approved']);
                $query = '
                    update
                        vbismiddle.invoicesent
                    set
                        invstatus=$invstatus,
                        invtype=$invtype
                    where
                        invno=$invno';
    
                sql_execute($query, $params);
            }
        }

        protected function inv_rec_paid() {
            $params['$recno'] = $this->params['recno'];
            $params['$invstatus'] = check_string($this->invoice_status['paid']);
            
            $add_sql = 'recno=$recno';
            $req_arr = $this->inv_rec_info($add_sql, $params);
            $params['$invno'] = count($req_arr) >0 ? $req_arr[0]['invno']: "";
            
            $params['$rectype'] = check_string('template');
            $query = '
            update
            vbismiddle.invoicerec
            set 
            invstatus=$invstatus,
            rectype=$rectype,
            where
            recno = $recno and rectype != $rectype';
            sql_execute($query, $params);
            
            $params['$rectype'] = check_string('history');
            $this->check_invoice_status($params);
            return json_encode(["success"=>true]);
        }

        protected function inv_rec_detail() {
            $params['$recno'] = $this->params['recno'];
            $add_sql = 'recno=$recno';
            $rec_datas = $this->inv_rec_info($add_sql, $params);
            return json_encode([
                "success"=>true, 
                "items"=>count($rec_datas)>0 ? $rec_datas[0] : $rec_datas
            ]);
        }

        protected function approve_rec_delete() {
            $params['$recno'] = $this->params['recno'];
            $add_sql = 'recno=$recno';
            $req_arr = $this->inv_rec_info($add_sql, $params);
            $params['$invno'] = count($req_arr) >0 ? $req_arr[0]['invno']: "";
            $query = '
            delete from 
                vbismiddle.invoiceRec
            where
                recno = $recno';
            sql_execute($query, $params);
            $this->check_invoice_status($params);

            return json_encode(["success"=>true]);
        }

        protected function get_fname() {
            $handphone = $this->params['handphone'];
            $params['$handphone'] = check_string($handphone);

            $query = '
                select 
                    first_name, last_name
                from 
                    vbismiddle.app_users
                where 
                    mobile=$handphone
                fetch first 1 rows only
            ';

            $res = _select($query, $params);
            return json_encode(
                [
                    "success"=>true,
                    "items"=>$res
                ]
            );
        }

        protected function inv_list($invtype) {
            global $custno;
            $params['$custno'] = check_string($custno);
            $params['$invtype'] = check_string($invtype);

            $query = '
            select 
                *
            from 
                vbismiddle.invoicesent 
            where
                custno=$custno
                and
                invtype=$invtype
            ';
            $req = $this->get_req_params($query, 'invno', ["created_at"], "vbismiddle.invoicesent", "invno");
            $query = $req['query'];
            $params += $req['params'];
            $res = _select($query, $params);

            $res_arr = [
                "success"=>true,
                "items"=>$res
            ];
            
            $req_perpage = get_or_null($_POST['perpage']);
            if ($req_perpage) {
                $res_arr['total_page'] = $req['total_page'];
            }
            return json_encode($res_arr);
        }

        protected function inv_recieve_list($rectype) {
            global $custno;
            $params['$custno'] = $custno;
            $params['$rectype'] = check_string($rectype);

            $query = '
            select 
                *
            from 
                vbismiddle.invoicerec
            where
                custno=$custno
                and 
                rectype=$rectype
            ';
            $req = $this->get_req_params($query, 'invno', ['created_at'], "vbismiddle.invoicesent", "invno");
            $query = $req['query'];
            $params += $req['params'];
            $res = _select($query, $params);

            $res_arr = [
                "success"=>true,
                "items"=>$res
            ];
            
            $req_perpage = get_or_null($_POST['perpage']);
            if ($req_perpage) {
                $res_arr['total_page'] = $req['total_page'];
            }
            return json_encode($res_arr);
        }

        protected function inv_rec_info($add_sql, $params) {
            $rec_query = '
                select 
                    * 
                FROM 
                    vbismiddle.invoicerec
                where 
                '.$add_sql;

            return _select($rec_query, $params);
        }

        protected function inv_detail() {
            $request_param_id = $this->params['invno'];
            
            $query = '
                select 
                    * 
                FROM 
                    vbismiddle.invoicesent
                where 
                    invno=$id';
            
            $params['$id'] = $request_param_id;

            $invoice_datas = _select($query, $params);
            $add_sql = 'invno=$id';
            $rec_datas = $this->inv_rec_info($add_sql, $params);

            if (count($invoice_datas)>0) $invoice_datas[0]['rec_datas'] = $rec_datas;
            
            return json_encode([
                "success"=>true,
                "items"=>count($invoice_datas) >0 ? $invoice_datas[0]: $invoice_datas,
            ]);
        }

        protected function inv_sent_delete() {
            $params['$invno'] = $this->params['invno'];
            
            $query = '
                delete from vbismiddle.invoicerec where invno= $invno
            ';

            sql_execute($query, $params);
            $query = '
                delete from vbismiddle.invoicesent where invno= $invno
            ';

            sql_execute($query, $params);
            return json_encode([
                "success"=>true,
            ]);
        }

        protected function inv_rec_delete() {
            $params['$recno'] = $this->params['recno'];
            $params['$rectype'] = check_string('template');
            $params['$invstatus'] = check_string($this->invoice_status['revoked']);

            $query = '
                update
                    vbismiddle.invoiceRec

                set invstatus=$invstatus
                where
                    recno= $recno and rectype != $rectype';

            sql_execute($query, $params);
            return json_encode([
                "success"=>true,
            ]);
        }

        protected function check_valid_data($arr, $_class, $required_fields, $reciever=False) {
            $_class->requested_arr = $arr; 
            $_class->is_reciever = $reciever; 
            foreach ($required_fields as $key => $value) {
                $_class->field_name = $value;
                $_class->field_value = get_or_null($arr[$value]); 
                $check_res = $_class->request_res();
                if(!$check_res['success']) {
                    return [
                        "success"=>false,
                        "info"=>$check_res['info']
                    ];
                }
            }
            return [
                "success"=>true,
                "info"=>'sdfsdfds',
            ];
        }

        protected function get_response_status($res) {
            if(!$res['success']) {
                return json_encode([
                    "success"=>false,
                    "info"=>$res['info']
                ]);
            }
            return False;
        }

        protected function check_invoice_arr($datas, $required_fields, $is_recieve_field=False) {
            $validation = new Validation();
            
            foreach ($datas as $key => $rec_arr) {
                $res = $this->get_response_status($this->check_valid_data($rec_arr, $validation, $required_fields, $is_recieve_field));
                if($res) return $res;
            }
            return False;
        }

        protected function inv_save($invtype) {

            $required_fields = ["custno", "handphone", "amount", "accntno", "invdesc", "rec_datas", "fname"];
            $res = $this->check_invoice_arr([$_POST], $required_fields);
            if($res) {
                return $res;
            };

            extract($_POST);

            $rec_datas = gettype($rec_datas) === 'string'? json_decode($rec_datas, true): $rec_datas; 
            $required_fields = ["custno", "handphone", "amount", "accntno", "fname"];
            $res = $this->check_invoice_arr($rec_datas, $required_fields, true);

            if($res) {
                return $res;
            };
            
            $invstatus = $this->invoice_status['pending'];
            $sent_values = [
                $amount, $custno, $accntno,
                $invstatus, $invdesc, $fname, $invtype
            ];
            
            $sent_query = 'insert into vbismiddle.invoicesent(
            amount, custno, accntno, invstatus, invdesc, fname,
            invtype
            ) values';

            $last_id = bulk_insert($sent_query, $sent_values);
            foreach ($rec_datas as $key => $value) {
                $amount = $value['amount'];
                $custno = $value['custno'];
                $accntno = $value['accntno'];
                $handphone = $value['handphone'];
                $fname = $value['fname'];
                $rec_query = 'insert into vbismiddle.invoiceRec(
                    invno, amount, custno, accntno, invstatus, handphone, fname,
                    invdesc, rectype
                    ) values';
    
    
                $recieve_datas = [
                    $last_id, $amount, $custno, $accntno, 
                    $invstatus, $handphone, $fname, $invdesc,
                    $invtype
                ];
                bulk_insert($rec_query, $recieve_datas);
            }
         
            return json_encode([
                "success"=>true,
                "info"=>'Aмжилттай хадгалагдлаа'
            ]);
        }

        protected function inv_edit() {

            $params['$invno'] = $this->params['invno'];

            // "accntno"
            $required_fields = ["amount", "invdesc"];
            $res = $this->check_invoice_arr([$_POST], $required_fields);

            if($res) {
                return $res;
            };

            extract($_POST);
            $params['$amount'] = $amount;
            $params['$accntno'] = check_string($accntno);
            $params['$invdesc'] = check_string($invdesc);

            $query = '
                update
                    vbismiddle.invoicesent 
                SET 
                    amount=$amount,
                    invdesc=$invdesc
                 WHERE invno=$invno';

            sql_execute($query, $params);

            $add_param = "";
            if ($rec_datas) {
                
                $required_fields = ["amount", "accntno"];
                
                $rec_datas = gettype($rec_datas) === 'string'? json_decode($rec_datas, true): $rec_datas; 
                $res = $this->check_invoice_arr($rec_datas, $required_fields, true);
    
                if($res) {
                    return $res;
                };
                
                $recids = [];
                foreach ($rec_datas as $value) {
                    $recno = get_or_null($value['recno']);
                    if ($value['recno']) {
                        $params['$amount'] = $value['amount'];
                        $params['$accntno'] = $value['accntno'];
                        $params['$invdesc'] = check_string($invdesc);
                        $query = '
                            update
                                vbismiddle.invoicerec
                            SET 
                                amount=$amount,
                                accntno=$accntno,
                                invdesc=$invdesc
        
                            WHERE recno=$recno';
                        sql_execute($query, $params);
    
                        array_push($recids, $recno);
                    }
                  
                }
                $add_param = 'and recno not in('.join(", " , $recids).")";
            }


            $query = '
                delete from vbismiddle.invoicerec where invno=$invno '.$add_param;

            sql_execute($query, $params);

            return json_encode([
                "success"=>true,
                "info"=>'Aмжилттай хадгалагдлаа'
            ]);
        }

        protected function inv_apply() {

        }
        
        protected function insert_invoice_status() {
            $invoice_status_datas = [
                ["code"=> "send", "status_name"=> "илгээсэн"],
                ["code"=> "revoked", "status_name"=> "цуцласан"],
                ["code"=> "paid", "status_name"=> "төлөгдсөн"],
                ["code"=> "new", "status_name"=> "шинэ"]
            ];
            
            $query = 'insert into vbismiddle.invoicestatus(code, status_name) values';
            bulk_insert($query, $invoice_status_datas );
            echo "done";
        }

        protected  function create_tables() {

            $invoice_sql = 'create table '.DB_SCHEMA.'InvoiceSent(
                    invno integer generated always as identity,
                    amount integer,
                    custno character varying(16) NOT NULL,
                    invtype character varying(16) DEFAULT "main",
                    fname character varying(100) NOT NULL,
                    accntno character varying(16) NOT NULL,
                    invstatus character varying(50) NOT NULL,
                    invdesc character varying(100) NOT NULL,
                    created_at timestamp DEFAULT CURRENT_TIMESTAMP
                )';
            
            $invoice_rec_sql = 'create table '.DB_SCHEMA.'InvoiceRec(
                    recno integer generated always as identity,
                    invno integer,
                    amount integer,
                    fname character varying(100) NOT NULL,
                    rectype character varying(16) DEFAULT "main",
                    custno character varying(16) NOT NULL,
                    accntno character varying(16) NOT NULL,
                    handphone character varying(16) NOT NULL,
                    invstatus character varying(50) NOT NULL,
                    invdesc character varying(100) NOT NULL,
                    created_at timestamp DEFAULT CURRENT_TIMESTAMP
                )';
            sql_execute($invoice_sql);
            sql_execute($invoice_rec_sql);
            echo "done";
        }

    }

?>
