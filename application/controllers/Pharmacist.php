<?php
if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Pharmacist extends CI_Controller
{
    
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        $this->load->model('crud_model');
        $this->load->model('email_model');
        $this->load->model('sms_model');
        $this->load->model('frontend_model');
    }
    
    function index()
    {
        if ($this->session->userdata('pharmacist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        $data['page_name']  = 'dashboard';
        $data['page_title'] = get_phrase('pharmacist_dashboard');
        $this->load->view('backend/index', $data);
    }
    
    function medicine_category($task = "", $medicine_category_id = "")
    {
        if ($this->session->userdata('pharmacist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        if ($task == "create") {
            $this->crud_model->save_medicine_category_info();
            $this->session->set_flashdata('message', get_phrase('medicine_category_info_saved_successfuly'));
            redirect(site_url('pharmacist/medicine_category'), 'refresh');
        }
        
        if ($task == "update") {
            $this->crud_model->update_medicine_category_info($medicine_category_id);
            $this->session->set_flashdata('message', get_phrase('medicine_category_info_updated_successfuly'));
            redirect(site_url('pharmacist/medicine_category'), 'refresh');
        }
        
        if ($task == "delete") {
            $this->crud_model->delete_medicine_category_info($medicine_category_id);
            redirect(site_url('pharmacist/medicine_category'), 'refresh');
        }
        
        $data['medicine_category_info'] = $this->crud_model->select_medicine_category_info();
        $data['page_name']              = 'manage_medicine_category';
        $data['page_title']             = get_phrase('medicine_category');
        $this->load->view('backend/index', $data);
    }
    
    function medicine($task = "", $medicine_id = "")
    {
        if ($this->session->userdata('pharmacist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        if ($task == "create") {
            $this->crud_model->save_medicine_info();
            $this->session->set_flashdata('message', get_phrase('medicine_info_saved_successfuly'));
            redirect(site_url('pharmacist/medicine'), 'refresh');
        }
        
        if ($task == "update") {
            $this->crud_model->update_medicine_info($medicine_id);
            $this->session->set_flashdata('message', get_phrase('medicine_info_updated_successfuly'));
            redirect(site_url('pharmacist/medicine'), 'refresh');
        }
        
        if ($task == "delete") {
            $this->crud_model->delete_medicine_info($medicine_id);
            redirect(site_url('pharmacist/medicine'), 'refresh');
        }
        
        $data['medicine_info'] = $this->crud_model->select_medicine_info();
        $data['page_name']     = 'manage_medicine';
        $data['page_title']    = get_phrase('medicine');
        $this->load->view('backend/index', $data);
    }
    
    function manage_profile($task = "")
    {
        if ($this->session->userdata('pharmacist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        $pharmacist_id = $this->session->userdata('login_user_id');
        if ($task == "update") {
            $this->crud_model->update_pharmacist_info($pharmacist_id);
            redirect(site_url('pharmacist/manage_profile'), 'refresh');
        }
        
        if ($task == "change_password") {
            $password             = $this->db->get_where('pharmacist', array(
                'pharmacist_id' => $pharmacist_id
            ))->row()->password;
            $old_password         = sha1($this->input->post('old_password'));
            $new_password         = $this->input->post('new_password');
            $confirm_new_password = $this->input->post('confirm_new_password');
            
            if ($password == $old_password && $new_password == $confirm_new_password) {
                $data['password'] = sha1($new_password);
                
                $this->db->where('pharmacist_id', $pharmacist_id);
                $this->db->update('pharmacist', $data);
                
                $this->session->set_flashdata('message', get_phrase('password_info_updated_successfuly'));
                redirect(site_url('pharmacist/manage_profile'), 'refresh');
            } else {
                $this->session->set_flashdata('message', get_phrase('password_update_failed'));
                redirect(site_url('pharmacist/manage_profile'), 'refresh');
            }
        }
        
        $data['page_name']  = 'edit_profile';
        $data['page_title'] = get_phrase('profile');
        $this->load->view('backend/index', $data);
    }

    function message($param1 = 'message_home', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('pharmacist_login') != 1)
            redirect(site_url(), 'refresh');
        
        if ($param1 == 'send_new') {
            $message_thread_code = $this->crud_model->send_new_private_message();
            $this->session->set_flashdata('message', get_phrase('message_sent!'));
            redirect(site_url('pharmacist/message/message_read/' . $message_thread_code), 'refresh');
        }
        
        if ($param1 == 'send_reply') {
            $this->crud_model->send_reply_message($param2); //$param2 = message_thread_code
            $this->session->set_flashdata('message', get_phrase('message_sent!'));
            redirect(site_url('/message/message_read/' . $param2), 'refresh');
        }
        
        if ($param1 == 'message_read') {
            $page_data['current_message_thread_code'] = $param2; // $param2 = message_thread_code
            $this->crud_model->mark_thread_messages_read($param2);
        }
        
        $page_data['message_inner_page_name'] = $param1;
        $page_data['page_name']               = 'message';
        $page_data['page_title']              = get_phrase('private_messaging');
        $this->load->view('backend/index', $page_data);
    }
    
    function payroll_list($param1 = '', $param2 = '')
    {
        if ($this->session->userdata('pharmacist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        $page_data['page_name']  = 'payroll_list';
        $page_data['page_title'] = get_phrase('payroll_list');
        $this->load->view('backend/index', $page_data);
    }
    
    function medicine_sale($task = "", $param2 = "")
    {
        if ($this->session->userdata('pharmacist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        if ($task == "create") {
            $this->crud_model->create_medicine_sale();
            $this->session->set_flashdata('message', get_phrase('data_added_successfully'));
            redirect(site_url('pharmacist/medicine_sale'), 'refresh');
        }
        
        $data['page_name']  = 'medicine_sale';
        $data['page_title'] = get_phrase('medicine_sales');
        $this->load->view('backend/index', $data);
    }
    
    function create_medicine_sale($task = "", $param2 = "")
    {
        if ($this->session->userdata('pharmacist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        $data['page_name']  = 'medicine_sale_add';
        $data['page_title'] = get_phrase('add_medicine_sale');
        $this->load->view('backend/index', $data);
    }
    
    function get_available_quantity($medicine_id = '')
    {
        $medicine           = $this->db->get_where('medicine', array(
            'medicine_id' => $medicine_id
        ))->row();
        $available_quantity = $medicine->total_quantity - $medicine->sold_quantity;
        echo $available_quantity;
    }
    
    function get_medicine_price($medicine_id = '')
    {
        echo $this->db->get_where('medicine', array(
            'medicine_id' => $medicine_id
        ))->row()->price;
    }
}