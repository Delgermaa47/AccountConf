<?php
    class InvoiceForm {
        public $fname;
        public $recfname;
        public $cip;
        public $all_amount;
        public $current_amount;
        public $fromcustno;
        public $fromaccntno;
        public $tocustno;
        public $toaccntno;
        public $invstatus;
        public $created_at;
        public $invdesc;
        public $tophone;
        public $status_name;
        
        public function __construct() {
            $this->action_uri ='/api/invoice-save';
        }
     
        protected function _display_in_label($label, $key, $value, $type, $classname='col-md-6', $main_class='col-md-6') {
            $content  = ' 
            <div className="'.$main_class.'">
                <label htmlFor="" class="'.$classname.'">'.$label.'</label>
                <input
                    type='.$type.'
                    name='.$key.'
                    class="rounded col-5 mt-4"
                    value='.$value.'
                ></input>
            </div>';
            return $content;
        }
           
        protected function _append_datas($datas) {
            $current_data = '';
            
            foreach ($datas as $key =>  &$value) {
                extract($value);
                $current_data = $current_data.$this -> _display_in_label($label, $key, $value, $type, $classname);
            }
            return $current_data;
        }


        public function display_form() {
        
            $send_keys = [
                ['key'=>'fromcustno', 'type'=>'text', 'value'=>$this->fromcustno, 'label'=>'Sip Дугаар', 'classname'=>'col-md-6'],
                ['key'=>'fname', 'type'=>'text', 'value'=>$this->fname, 'label'=>'Нэр', 'classname'=>'col-md-6'],
                ['key'=>'all_amount', 'type'=>'number', 'value'=>$this->all_amount, 'label'=>'Нийт дүн', 'classname'=>'col-md-6'],
                ['key'=>'fromaccntno', 'type'=>'text', 'value'=>$this->fromaccntno, 'label'=>'Данс', 'classname'=>'col-md-6'],
                ['key'=>'tophone', 'type'=>'text', 'value'=>$this->tophone, 'label'=>'Хүлээн авагчийн дугаар', 'classname'=>'col-md-6']
            ];

            $rec_keys = [
                ['key'=>'tocustno', 'type'=>'text', 'value'=>$this->tocustno, 'label'=>'Sip Дугаар', 'classname'=>'col-md-6'],
                ['key'=>'recfname', 'type'=>'text', 'value'=>$this->recfname, 'label'=>'Нэр', 'classname'=>'col-md-6'],
                ['key'=>'current_amount', 'type'=>'number', 'value'=>$this->current_amount, 'label'=>'Нийт дүн', 'classname'=>'col-md-6'],
                ['key'=>'toaccntno', 'type'=>'text', 'value'=>$this->fromaccntno, 'label'=>'Данс', 'classname'=>'col-md-6'],
                ['key'=>'fromaccntno', 'type'=>'text', 'value'=>$this->fromaccntno, 'label'=>'Хүлээн авагчийн дугаар', 'classname'=>'col-md-6']
            ];

            $both_keys = [
                ['key'=>'invstatus', 'type'=>'number', 'value'=>$this->invstatus, 'label'=>'Төлөв', 'classname'=>'col-md-9'],
                ['key'=>'status_name', 'type'=>'text', 'value'=>$this->status_name, 'label'=>'Нэр', 'classname'=>'col-md-9'],
                ['key'=>'invdesc', 'type'=>'text', 'value'=>$this->invdesc, 'label'=>'Тайлбар', 'classname'=>'col-md-9']
            ];

            $sender_data =  $this->_append_datas($send_keys);
            $recieve_data = $this->_append_datas($rec_keys);
            $both_data = $this->_append_datas($both_keys);
            
            echo json_encode($both_keys);
            echo '
            <div class="container text-primary">
                <form action="'.$this->action_uri.'" method="POST">
                    <div className="row">
                        <div class="form-row col-md-6 border-bottom border-danger">
                            <label>Илгээгч</label>
                            '.$sender_data.'
                        </div>
                        <div class="form-row col-md-6">
                            <label>Хүлээн авагч</label>
                            '.$recieve_data.'
                        </div>
                        <button class="btn btn-primary" type="button" onclick="bla()">click</button>
                        <div class="form-row col-md-12 mt-4">
                            '.$both_data.'
                        </div>
                        
                        <button
                            type="submit"
                            class="btn btn btn-primary col-md-6 mt-4"
                        >
                        Хадгалах
                    </button>
                    </div>
                </form>
            </div>';
        }
        
    }
    
?>
<script>
    function bla() {
        $url = "/api/invoice-list"

        $.post($url,   // url
       { myData: 'This is my data.' }, // data to be submit
       function(data, status, jqXHR) {// success callback
                $('p').append('status: ' + status + ', data: ' + data);
        })

    }

</script>