<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper(array('url', 'form'));
        $this->load->library('session');
        $this->load->database();

        $this->config->load('admin');
    }

    public function index()
    {
        $this->load->view('login_view');
    }

    public function auth()
    {
        $username = trim($this->input->post('username', TRUE));
        $password = trim($this->input->post('password', TRUE));

        $user = $this->db
            ->where('username', $username)
            ->where('password', md5($password))
            ->limit(1)
            ->get('users')
            ->row();

        if ($user) {

            $adminUsers = $this->config->item('admin_users');

            $isAdmin = in_array(
                strtoupper($user->username),
                array_map('strtoupper', $adminUsers)
            );

            $this->session->set_userdata(array(
                'logged_in' => TRUE,
                'username'  => $user->username,
                'fullname'  => $user->fullname,
                'photo'     => $user->photo,
                'is_admin'  => $isAdmin
            ));

            redirect('menu');
        } else {

            $this->session->set_flashdata(
                'error',
                'Invalid username or password.'
            );

            redirect('login');
        }
    }

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }

    public function menu()
    {
        if (!$this->session->userdata('logged_in')) {
            redirect('login');
        }

        $data['msg'] = $this->session->flashdata('msg');
        $this->load->view('csv_menu', $data);
    }
}
