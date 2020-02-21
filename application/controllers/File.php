<?php

class File extends CI_Controller
{
    public function __construct()
    {
        echo "In __construct";
        parent::__construct();
        if( stristr(PHP_SAPI,'cli') === FALSE )  {
            log_message('error','Webaccess to CRONtroller :: ' . $this->input->ip_address() . ' :: ' . $this->input->user_agent());
           
            exit;
        }
    }
};

?>