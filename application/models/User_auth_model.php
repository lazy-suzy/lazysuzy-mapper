<?php

class User_auth_model extends CI_Model {
	function __construct() {

		parent::__construct();
		$this->load->database();
		$this->load->library('session');
	}

	public function duplicateEmail($email) {

		$this->db->where('email', $email);

		$query = $this->db->get('lz_user');

		$count_row = $query->num_rows();

		if ($count_row > 0) {
			//if count row return any row; that means you have already this email address in the database. so you must set false in this sense.
			return TRUE;
		} else {
			// doesn't return any row means database doesn't have this email
			return FALSE;
		}
	}

	public function insertUser($data) {

		return $this->db->insert('lz_user', $data);
	}

	public function loginUser($username, $password) {
		//$this->db->where(array('username' = >$username, 'password' => $password));
		$query = $this->db->get_where('lz_user', array('email' => $username, 'password' => $password, 'is_mail_verified' => 1)); //status sholud be 1

		if ($query->num_rows() == 1) {

			$userArr = array();
			foreach ($query->result() as $row) {
				$userArr[0] = $row->id;
				$userArr[1] = $row->name;

			}
			$userData = array(
				'user_id' => $userArr[0],
				'user_name' => $userArr[1],
				'logged_in' => TRUE,
			);
			$this->session->set_userdata($userData);

			return $query->result();
		} else {
			return false;
		}
	}

	//send confirm mail
	public function sendEmail($receiver, $name) {
		$from = "your_email@gmail.com"; //senders email address
		$subject = 'Verify email address'; //email subject

		//sending confirmEmail($receiver) function calling link to the user, inside message body
		$message = 'Dear ' . $name . ',<br><br> Please click on the below activation link to verify your email address<br><br>
        <a href=\'http://www.localhost/codeigniter/Signup_Controller/confirmEmail/' . md5($receiver) . '\'>http://www.localhost/codeigniter/Signup_Controller/confirmEmail/' . md5($receiver) . '</a><br><br>Thanks';

		//config email settings
		$config['protocol'] = 'smtp';
		$config['smtp_host'] = 'ssl://smtp.gmail.com';
		$config['smtp_port'] = '465';
		$config['smtp_user'] = $from;
		$config['smtp_pass'] = '******'; //sender's password
		$config['mailtype'] = 'html';
		$config['charset'] = 'iso-8859-1';
		$config['wordwrap'] = 'TRUE';
		$config['newline'] = "\r\n";

		$this->load->library('email', $config);
		$this->email->initialize($config);
		//send email
		$this->email->from($from);
		$this->email->to($receiver);
		$this->email->subject($subject);
		$this->email->message($message);

		if ($this->email->send()) {
			//for testing
			echo "sent to: " . $receiver . "<br>";
			echo "from: " . $from . "<br>";
			echo "protocol: " . $config['protocol'] . "<br>";
			echo "message: " . $message;
			return true;
		} else {
			echo "email send failed";
			$this->email->print_debugger();
			echo "asd";
			return false;
		}

	}

	//activate account
	function verifyEmail($key) {
		$data = array('is_mail_verified' => 1);
		$this->db->where('md5(email)', $key);
		return $this->db->update('lz_user', $data); //update status as 1 to make active user
	}

}
?>