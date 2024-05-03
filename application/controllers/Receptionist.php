<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Receptionist extends CI_Controller
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
        if ($this->session->userdata('receptionist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        $data['page_name']  = 'dashboard';
        $data['page_title'] = get_phrase('receptionist_dashboard');
        $this->load->view('backend/index', $data);
    }
    
    function patient($task = "", $patient_id = "")
    {
        if ($this->session->userdata('receptionist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        if ($task == "create") {
            $this->crud_model->save_patient_info();
            $this->session->set_flashdata('message', get_phrase('patient_info_saved_successfuly'));
            redirect(site_url('receptionist/patient'), 'refresh');
        }
        if ($task == "update") {
            $this->crud_model->update_patient_info($patient_id);
            redirect(site_url('receptionist/patient'), 'refresh');
        }
        
        if ($task == "delete") {
            $this->crud_model->delete_patient_info($patient_id);
            redirect(site_url('receptionist/patient'), 'refresh');
        }
        
        $data['patient_info'] = $this->crud_model->select_patient_info();
        $data['page_name']    = 'manage_patient';
        $data['page_title']   = get_phrase('patient');
        $this->load->view('backend/index', $data);
    }
    
    function appointment($task = "", $doctor_id = 'all', $start_timestamp = "", $end_timestamp = "")
    {
        if ($this->session->userdata('receptionist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        if ($task == 'filter') {
            $doctor_id       = $this->input->post('doctor_id');
            $start_timestamp = strtotime($this->input->post('start_timestamp'));
            $end_timestamp   = strtotime($this->input->post('end_timestamp'));
            redirect(site_url('receptionist/appointment/search/' . $doctor_id . '/' . $start_timestamp . '/' . $end_timestamp), 'refresh');
        }
        
        if ($task == "create") {
            $this->crud_model->save_appointment_info();
            $this->session->set_flashdata('message', get_phrase('appointment_info_saved_successfuly'));
            redirect(site_url('receptionist/appointment'), 'refresh');
        }
        
        $data['doctor_id'] = $doctor_id;
        if ($start_timestamp == '')
            $data['start_timestamp'] = strtotime('today - 30 days');
        else
            $data['start_timestamp'] = $start_timestamp;
        if ($end_timestamp == '')
            $data['end_timestamp'] = strtotime('today');
        else
            $data['end_timestamp'] = $end_timestamp;
        
        $data['appointment_info'] = $this->crud_model->select_appointment_info($doctor_id, $data['start_timestamp'], $data['end_timestamp']);
        $data['page_name']        = 'show_appointment';
        $data['page_title']       = get_phrase('appointment');
        $this->load->view('backend/index', $data);
    }
    
    function appointment_requested($task = "", $appointment_id = "")
    {
        if ($this->session->userdata('receptionist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        if ($task == "approve") {
            $this->crud_model->approve_appointment_info($appointment_id);
            $this->session->set_flashdata('message', get_phrase('appointment_info_approved'));
            redirect(site_url('receptionist/appointment_requested'), 'refresh');
        }
        
        $data['requested_appointment_info'] = $this->crud_model->select_requested_appointment_info();
        $data['page_name']                  = 'manage_requested_appointment';
        $data['page_title']                 = get_phrase('requested_appointment');
        $this->load->view('backend/index', $data);
    }
    
    function manage_profile($task = "")
    {
        if ($this->session->userdata('receptionist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        $receptionist_id = $this->session->userdata('login_user_id');
        if ($task == "update") {
            $this->crud_model->update_receptionist_info($receptionist_id);
            redirect(site_url('receptionist/manage_profile'), 'refresh');
        }
        
        if ($task == "change_password") {
            $password             = $this->db->get_where('receptionist', array(
                'receptionist_id' => $receptionist_id
            ))->row()->password;
            $old_password         = sha1($this->input->post('old_password'));
            $new_password         = $this->input->post('new_password');
            $confirm_new_password = $this->input->post('confirm_new_password');
            
            if ($password == $old_password && $new_password == $confirm_new_password) {
                $data['password'] = sha1($new_password);
                
                $this->db->where('receptionist_id', $receptionist_id);
                $this->db->update('receptionist', $data);
                
                $this->session->set_flashdata('message', get_phrase('password_info_updated_successfuly'));
                redirect(site_url('receptionist/manage_profile'), 'refresh');
            } else {
                $this->session->set_flashdata('message', get_phrase('password_update_failed'));
                redirect(site_url('receptionist/manage_profile'), 'refresh');
            }
        }
        
        $data['page_name']  = 'edit_profile';
        $data['page_title'] = get_phrase('profile');
        $this->load->view('backend/index', $data);
    }

    function message($param1 = 'message_home', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('receptionist_login') != 1)
            redirect(site_url(), 'refresh');
        
        if ($param1 == 'send_new') {
            $message_thread_code = $this->crud_model->send_new_private_message();
            $this->session->set_flashdata('message', get_phrase('message_sent!'));
            redirect(site_url('receptionist/message/message_read/' . $message_thread_code), 'refresh');
        }
        
        if ($param1 == 'send_reply') {
            $this->crud_model->send_reply_message($param2); //$param2 = message_thread_code
            $this->session->set_flashdata('message', get_phrase('message_sent!'));
            redirect(site_url('receptionist/message/message_read/' . $param2), 'refresh');
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
        if ($this->session->userdata('receptionist_login') != 1) {
            $this->session->set_userdata('last_page', current_url());
            redirect(site_url(), 'refresh');
        }
        
        $page_data['page_name']  = 'payroll_list';
        $page_data['page_title'] = get_phrase('payroll_list');
        $this->load->view('backend/index', $page_data);
    }

    
}