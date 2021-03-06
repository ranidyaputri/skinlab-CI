<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Page extends CI_Controller{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('form_validation');
    }
    public function index()
    {
        $data['title'] = 'User';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $this->load->view('template/head', $data);
        $this->load->view('User/_partials/sidebar');
        $this->load->view('template/navbar', $data);
        $this->load->view('User/index', $data);
        $this->load->view('template/foot');
        
        
    }
    public function editProfile()
    {
        $data['title'] = 'Edit Profile';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $this->form_validation->set_rules('name', 'Name', 'required|trim');

        if($this->form_validation->run() == false){
            $this->load->view('template/head', $data);
            $this->load->view('User/_partials/sidebar', $data);
            $this->load->view('template/navbar', $data);
            $this->load->view('User/editProfile', $data);
            $this->load->view('template/foot');
        }else{
            $name = $this->input->post('name');
            $email = $this->input->post('email');

            //cek jika ada gambar
            $upload_image = $_FILES['image']['name'];
            if($upload_image){
                $config['allowed_types'] = 'jpg|png';
                $config['max_size'] = '2048';
                $config['upload_path'] = './admin_assets/img/profile/';
                $this->load->library('upload', $config);

                if($this->upload->do_upload('image')){
                    $old_image = $data['user']['foto'];
                    if($old_image != 'default.svg'){
                        unlink(FCPATH. './admin_asets/img/profile/'. $old_image);
                    }

                    $new_image = $this->upload->data('file_name');
                    $this->db->set('foto', $new_image);
                }else{
                    echo $this->upload->display_errors();
                }
            }
            
            $this->db->set('nama', $name);
            $this->db->where('email', $email);
            $this->db->update('user');
            $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Your profile has been edited.</div>');
            redirect('User/Page/');
        }
    }

    public function changePassword()
    {
        $data['title'] = 'Change Password';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        
        $this->form_validation->set_rules('current_password', 'Current Password', 'required|trim');
        $this->form_validation->set_rules('new_password1', 'New Password', 'required|trim|min_length[3]|matches[new_password2]', ['matches' => "Your Password doesn't match.", 'min_length' => 'Your Password should be at least 3.']);
        $this->form_validation->set_rules('new_password2', 'Confirm New Password', 'required|trim|min_length[3]|matches[new_password1]', ['matches' => "Your Password doesn't match.", 'min_length' => 'Your Password should be at least 3.']);

        if ($this->form_validation->run() == false){
            $this->load->view('template/head', $data);
            $this->load->view('User/_partials/sidebar', $data);
            $this->load->view('template/navbar', $data);
            $this->load->view('User/changePassword', $data);
            $this->load->view('template/foot');
        }else{
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password1');
            if(!password_verify($current_password, $data['user']['password'])){
                $this->session->set_flashdata('message', "<div class='alert alert-warning' role='alert'>Your current password doesn't match.</div>");
                redirect('User/Page/changePassword');
            }else{
                if ($current_password == $new_password){
                    $this->session->set_flashdata('message', "<div class='alert alert-warning' role='alert'>New password can't be the same as current password.</div>");
                    redirect('User/Page/changePassword');
                }else{
                    //password bener
                    $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $this->db->set('password', $password_hash);
                    $this->db->where('email', $this->session->userdata('email'));
                    $this->db->update('user');
                    $this->session->set_flashdata('message', "<div class='alert alert-success' role='alert'>Your password has been changed.</div>");
                    redirect('User/Page/changePassword');
                }
            }
        }
        
    }
}