<?php

    class ApiList
    {
        public $request_name;
        public $request_params;

        function __set($propName, $propValue)
        {
            $this->$propName = $propValue;
        }
        
        public function bla() {

        }
        public function request_res() {
            $request_name = strtolower($this->request_name);

            switch ($request_name) {

                case 'add-accnt-conf':
                    return $this->bla();
            }
            return [];
        }

    }

?>