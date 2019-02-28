<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class User_auth extends CI_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->library('email');
		$this->load->library('session');
		$this->load->library('form_validation');
		$this->load->helper(array('url', 'language'));
		$this->load->database();

		$this->load->model('User_auth_model');
	}

	function index() {

		$this->load->view('user/home_page');
		//$this->load->view('user/index');
	}

	public function signup() {

		$this->form_validation->set_rules('txt_name', 'name', 'required');
		$this->form_validation->set_rules('txt_email', 'email', 'trim|required|valid_email');
		$this->form_validation->set_rules('txt_password', 'password', 'required');

		if ($this->form_validation->run() == false) {
			/*$this->load->view('header');
				$this->load->view('signup_view');
			*/
			$this->load->view('user/home_page?error_in_input', $data);

			// redirect to homepage. Open the register form and flash error message.

		} else {
			//call db
			$data = array(
				'name' => strtolower(trim($this->input->post('txt_name'))),
				'email' => $this->input->post('txt_email'),
				'password' => md5($this->input->post('txt_password')),
			);

			if (!$this->User_auth_model->duplicateEmail($data['email'])) {
				if ($this->User_auth_model->insertUser($data)) {

					//send confirm mail
					if ($this->User_auth_model->sendEmail(strtolower(trim($this->input->post('txt_email'))), $data['name'])) {

						redirect('user/auth/77');
					} else {
						$error = "Error, Cannot insert new user details!";

					}
				} else {
					redirect('user/auth/1', 'refresh');
				}
			} else {
				redirect('user/auth/3', 'refresh');
			}
		}
	}
	public function signin() {

		$this->form_validation->set_rules('txt_username', 'Username', 'trim|required');
		$this->form_validation->set_rules('txt_password', 'Password', 'trim|required');

		if ($this->form_validation->run() == false) {
			/*$this->load->view('header');
				$this->load->view('login_view');
			*/
			$this->load->view('user/home_page?wrong_input');
		} else {

			$username = strtolower(trim($this->input->post('txt_username')));
			$password = md5($this->input->post('txt_password'));
			$user = $this->User_auth_model->loginUser($username, $password);

			if ($user) {

				// fetch all the user data here and show its profile.
				$userInfo = $user;
				$this->session->set_userdata('login', true);
				$this->session->set_userdata('email', $username);

				//$this->load->view('header');
				//$this->load->view('tasks_view');
				//$this->load->view('footer');
				redirect('/');

			} else {
				/*$this->load->view('header');
					$this->load->view('login_view');
				*/
				redirect('user/auth/2', 'refresh');

			}
		}

	}

	public function google_auth() {
		if (isset($_GET['code'])) {

			$this->googleplus->getAuthenticate();
			$user = $this->googleplus->getUserInfo();
			$contents['user_profile'] = $this->session->userdata('user_info');
			$contents['first_name'] = $user['given_name'];
			$contents['last_name'] = $user['last_name'];
			$contents['profile_pic'] = $user['picture'];
			$contents['gender'] = $user['gender'];
			$contents['locale'] = $user['locale'];
			$contents['email'] = $user['email'];

			/*
					[id] => 106572627990975248724
				    [email] => adityasaxena602@gmail.com
				    [verified_email] => 1
				    [name] => Aditya Saxena
				    [given_name] => Aditya
				    [family_name] => Saxena
				    [link] => https://plus.google.com/106572627990975248724
				    [picture] => https://lh5.googleusercontent.com/-rsDhJuawy34/AAAAAAAAAAI/AAAAAAAABYk/Fiso2UVfAlQ/photo.jpg
				    [gender] => male
				    [locale] => en
			*/

			// got the google credentials
			// decide if we want to login or register
			// if the [email] field is present in the database then login
			// else register
			if (!$this->User_auth_model->duplicateEmail(trim(strtolower($user['email'])))) {
				// register user
				$userInfo = array(
					'first_name' => $user['given_name'],
					'last_name' => $user['family_name'],
					'name' => $user['name'],
					'email' => $user['email'],
					'is_mail_verified' => $user['verified_email'],
					'gender' => $user['gender'],
					'locale' => $user['locale'],
					'picture' => $user['picture'],
					'oauth_uid' => $user['id'],
					'oauth_provider' => 'GOOGLE',
					'created' => date('Y-m-d H:i:s'),
				);

				if ($this->User_auth_model->insertUser($userInfo)) {
					$this->session->set_userdata('login', true);
					$this->session->set_userdata('user_info', $user);
					redirect('/');
				}

			} else {
				// login user
				$this->session->set_userdata('login', true);
				$this->session->set_userdata('user_info', $user);
				redirect('/');
			}

		}

	}

	public function logout() {
		$this->session->sess_destroy();
		$this->session->set_userdata('login', false);
		redirect('/');

	}

	function confirmEmail($hashcode) {
		if ($this->User_auth_model->verifyEmail($hashcode)) {
			$this->session->set_flashdata('verify', '<div class="alert alert-success text-center">Email address is confirmed. Please login to the system</div>');
			$this->load->view('user/auth/78', array('status' => 1));
		} else {
			$this->session->set_flashdata('verify', '<div class="alert alert-danger text-center">Email address is not confirmed. Please try to re-register.</div>');
			$this->load->view('user/auth/79', array('status' => 0));
		}
	}
}

?>