
<?php
    require_once ROOT."\\inc\\header.php";
    require_once ROOT."\\inc\\components\\table.php";
    require_once ROOT."\\inc\\components\\sub_components\\invoice_form.php";

    
    class PageRequest
    {
        public $request_name;
        public $params;
        function __set($propName, $propValue)
        {
            $this->$propName = $propValue;
        }

        protected function page404() {
            http_response_code(404);
            console_log(
                '<div class="page404">
                    <span>404</span>      
                    <p>page not found</p>
                </div>
                '
            );
            
        }
        
        public function request_res() {

            $request_name = strtolower($this->request_name);
            switch ($request_name) {
                case 'home':
                    $this->navbar();
                    // $this->home();
                    die();
                case 'account-config':
                    $this->home();
                    die();

                default: $this->page404();

            }
        }

        protected function  navbar() {
            $request_url = 'http://localhost:81/index.php?page=account-config';
            $content = $this->home();
            echo 
                '<div class="container">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card rounded m-0">
                                <div class="card-body font-weight-bold w-100">
                                    <div class="col-md-12 text-primary d-flex justify-content-center ">
                                        <i class="fa fa-minus-circle mr-1 mt-1"></i>
                                        <span class="my-0">VB тохиргооны хуудас</span>
                                    </div>
                                    <div 
                                        class="col-md-12 text-primary d-flex justify-content-center ml-2"
                                    >
                                        <a onclick="getPage('.check_string($request_url).')" role="button">
                                            <i class="fa fa-gear mr-1 mt-1"></i>
                                            Дансны тохиргооны хэсэг
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="card rounded m-0">
                                <div class="card-body font-weight-bold w-100">
                                    <div 
                                        class="col-md-12 text-primary d-block"
                                        id="content"
                                    >
                                        <div class="col-md-12 border-bottom border-primary">
                                            <i class="fa fa-minus-circle mr-1 mt-1"></i>
                                            <label class="">VB тохиргооны хуудас</label>
                                        </div>
                                        <div class="col-md-12 py-4">
                                        '.
                                            $content
                                        .'
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
        }

        protected function home() {

            function _delete_comp($id) {
                return '
                    <a class="text-danger" href="\api\delete-sent-invoice\\'.$id.'" role="button"><i class="fa fa-trash" aria-hidden="true"></i></a>
                ';
            }

            function _edit_comp($id) {
                return '<a class="text-success" href="\invoice-detail\\'.$id.'" role="button"><i class="fa fa-user" aria-hidden="true"></i></a>';
            }

            function _add_comp($id) {
                return '<a class="text-warning" href="\invoice-detail\\'.$id.'" role="button"><i class="fas fa-dollar-sign" aria-hidden="true"></i></a>';
            }

            $employee = new NewTable();
            $employee->className="table table-striped mt-4 pt-4";
            $employee->header_details = array(
                "class_name" => "bg-dark text-white",
                "header_data" => array(
                    array("field"=>"types", "value"=>"Дансны төрөл", "className"=>"", "scope"=> " ", "action"=>false, "have_icon"=> false),
                    array("field"=>"prodname", "value"=>"Төрөл", "className"=>"", "scope"=> " ", "action"=>false, "have_icon"=> false),
                    array("field"=>"types", "value"=>"", "className"=>"", "scope"=> " ", "action"=>true, "have_icon"=> true, "key_name"=> "edit_row"),
                    array("field"=>"types", "value"=>"", "className"=>"", "scope"=> " ", "action"=>true, "have_icon"=> true, "key_name"=> "delete_row")
                )
            );

            $employee->added_datas = array(
                "delete_row" => "_delete_comp",
                "edit_row" => "_edit_comp",
                // "pay_row" => "_pay_comp"
            );
            $query = '
                select
                    types, prodname
                from 
                    forsoft.tabpro_product_dic
                group by types,  prodname
                order by types
                ';
            
            $temp_data = [];
            $check_arr = [];

            foreach (_select($query) as $value) {
                extract($value);
                if(!in_array($types, $check_arr)) {
                    array_push($temp_data, $value);
                    array_push($check_arr, $types);
                }
            }
            $employee->body_datas = $temp_data;
            // $employee->body_datas = json_decode(file_post_contents('http://172.26.153.11/api/invoice-list', ["query"=>$query]), true);
            return $employee->diplay_table();
        } 
        protected function inv_detial() {
            $invoice_form = new InvoiceForm();
            $invoice_id = $this->params['id'];
            $invoice_form->action_uri = '/api/invoice-edit/'.$this->params['id'];
            $data = json_decode(file_get_contents('http://172.26.153.11/api/invoice-detail/'.$invoice_id), true);
            if (count($data)>0) {
                extract($data[0]);
                $invoice_form->fname = $fname;
                $invoice_form->lname = $lname;
                $invoice_form->phone_number = $phone_number;
            }
            echo $invoice_form->display_form();
        }

        protected function inv_save() {
            $invoice_form = new InvoiceForm();
            echo $invoice_form->display_form();
        }

    }

    require ROOT."\\inc\\footer.php"
?>