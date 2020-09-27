<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Custom500 extends CI_Controller {

  public function __construct()
  {
    parent::__construct();
    $this->load->helper('url');
  }

  public function index()
  {
    $this->output->set_status_header('500'); 
    $this->load->view('500');
  }
}