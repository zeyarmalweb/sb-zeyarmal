<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Customer_cart extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
            $this->load->model('google_login_model');
    }

    public function login(){
        include_once APPPATH . "libraries/vendor/authoload.php";
        $google_client = new Google_Client();
        $google_client->setClientId('');
        $google_client->setClientSecret('');
        $google_client->setRedirectUri('http://localhost/seebuy/auth_home/h_login_user');
        $google_client->addScope('email');
        $google_client->addScope('profile');

        if(isset($_GET["code"]))
        {
            $token = $google_client->fetchAccessTokenWithAuthCode($_GET["code"]);
            if(!isset($token["error"]))
            {
                $google_client->setAccessToken($token['access_token']);
                $this->session->userdata('access_token', $token['access_token']);
                $google_service = new Google_Service_oauth2($google_client);
                $data = $google_service->userinfo->get();
                $current_datatime = date('Y-m-d H:i:s');
                if($this->google_login_model->Is_already_register($data['id']))
                {
                    // update data
                    $user_data = array(
                        'customer_name' => $data['given_name'],
                        'customer_last_name' => $data['family_name'],
                        'customer_email' => $data['email'],
                        'customer_image' => $data['picture'],
                        'updated_at' => $current_datatime,
                    );
                    $this->google_login_model->Update_user_data($user_data, $data['id']);
                }
                else
                {
                    // insert data
                    $user_data = array(
                        'login_oauth_uid' => $data['id'],
                        'customer_name' => $data['given_name'],
                        'customer_last_name' => $data['family_name'],
                        'customer_email' => $data['email'],
                        'customer_image' => $data['picture'],
                        'created_at' => $current_datatime,
                    );
                    $this->google_login_model->Insert_user_data($user_data);

                }
                $this->session->userdata('user_data', $user_data);
            }
            if(!$this->session->userdata('access_token'))
            {
                $login_button = '<a href="'. $google_client->createAuthUrl() .'">
                    Login With Google
                </a>';
            }
            $data['login_button'] = $login_button;
            $this->load->view('', $data);
        }
    }
}



// model

    function Is_already_register($id){
        $this->db->where('login_oauth_uid', $id);
        $query = $this->db->get('customer');
        if($query->num_rows() > 0)
        {
            return true;
        }
        else{
            return false;
        }
    }

    function Update_user_data($data, $id){
        $this->db->where('login_oauth_uid', $id);
        $this->db->update('customer', $data);
    }

    function Insert_user_data($data){
        $this->db->insert('customer', $data);
    }


// Client id
    // 898362785133-ssnsiootbfkk02ol5erfkei4sg3fb2s2.apps.googleusercontent.com

    
// Client Secret

    // z9FDpI3-GG3WtXDA8ct0s8sl