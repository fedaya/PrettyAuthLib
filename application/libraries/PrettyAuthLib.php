<?php

/*
 * This file is part of PrettyAuthLib
 * Copyright (c) Etienne Gille, All rights reserved.
 * 
 * PrettyAuthLib is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 * 
 * PrettyAuthLib is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with PrettyAuthLib. If not, see <http://www.gnu.org/licenses/>
 */

/**
 * Description of PrettyAuthLib
 *
 * @author Etienne Gille
 */
class Prettyauthlib 
{
    
    /**
     * Holds the caller's controller instance
     * 
     * @var type CI_Controller
     */
    private $instance;
    /**
     * Holds the settings defined in config/pretty_auth_lib.php
     * @var type array
     */
    private $settings;
    /**
     * Holds the authentication cookie
     * @var type array
     */
    private $cookie;
    
    
    public function __construct() 
    {
        $this->instance =& get_instance(); 
        $this->instance->load->library('session');
        $this->instance->config->load('pretty_auth_lib', true);
        $this->settings = $this->instance->config->item('pretty_auth_lib');
        $this->cookie = $this->instance->input->cookie($this->settings['cookie_name'], true);
        
    }
    
    /**
     * Return the authentication status
     * @return boolean
     */
    public function is_authenticated() 
    {
        return $this->instance->session->userdata('PrettyAuthLib_authenticated');
    }
    
    /**
     * Performs the login process if needed, or restore the session if saved.
     *  unsuccessful by default
     * @param String $login_route
     * @return integer return null during the process, true if the authentication is successfull, false if not
     */
    public function login($login_route = null, $protocol = null) 
    {
        if (!$this->is_authenticated())
        {
            if (!$this->cookie) {
                if (!($this->instance->session->userdata('PrettyAuthLib_auth_success') || 
                      $this->instance->session->userdata('PrettyAuthLib_auth_failure'))) {
                    $this->instance->load->helper('url');
                    $this->instance->session->set_userdata(array(
                        'PrettyAuthLib_referer' => current_url()
                    ));
                    $this->instance->session->set_userdata(array('PrettyAuthLib_auth_failure' => true));
                    $login_route = ($login_route!==null?$login_route:$this->settings['login_route']);
                    $protocol = ($protocol!==null?$protocol:$this->settings['protocol']);
                    $full_route = base_url($login_route);
                    if(!preg_match('/^'.$protocol.'/',$full_route)) {
                        $full_route = preg_replace('/^[a-zA-z]+\:\/\//',$protocol."://",$full_route);
                    }
                    redirect($full_route);
                    return null;
                } elseif ($this->instance->session->userdata('PrettyAuthLib_auth_failure')) {
					$this->cleanup_success_failure();
                    return false;
                } else {
                    $this->instance->session->set_userdata(array('PrettyAuthLib_authenticated' => true));
					$this->cleanup_success_failure();
                    return true;
                }
            } else { 
                    $array = array (
                        'PrettyAuthLib_authenticated' => true,
                        'PrettyAuthLib_userData' => $this->cookie['value']['userData'],
                    );
                    $this->instance->session->set_userdata($array);
                    $this->instance->cookie['expire'] = $this->settings['cookie_lifetime'];
					$this->cleanup_success_failure();
                    $this->instance->input->set_cookie($this->cookie);
                    return true;
            }
        } else {
 	   $this->cleanup_success_failure();
           return true;
        }           
    }
    private function cleanup_success_failure() {
        $this->instance->session->unset_userdata(array('PrettyAuthLib_auth_success'=>"",'PrettyAuthLib_auth_failure'=>""));
    }
    /**
     * creates the authentication cookie to automatically relog the user in
     */
    public function remember_authentication() 
    {
        if (!$this->cookie) {
            $this->instance->load->helper('url');
            $cookie_value = array('userData' => $this->instance->session->userdata('PrettyAuthLib_userData'));
            $this->cookie = array(
                'name' => $this->settings['cookie_name'],
                'value' => $cookie_value,
                'expire' => $this->settings['cookie_lifetime'],
                'domain' => base_url(),
                'path' => '/',
                'prefix' => '',
                'secure' => true,
            );
            $this->instance->input->set_cookie($this->cookie);
        }
    }
    
    /**
     * deletes the authentication cookie
     */
    public function forget_authentication() 
    {
        $this->cookie['expire'] = time() - 3600;
        $this->cookie['value'] = array();
        $this->instance->input->set_cookie($this->cookie);
        $this->cookie = false;
    }
    
    /**
     * Performs the logout process.
     *  successful by default
     * @param String $logout_route the url to redirect the user to after the logout process
     * @return boolean return null during the process, true if logged out successfuly, false if not
     */
    public function logout($logout_route = null, $protocol = null) 
    {
        //First check if the person is authenticated
        if ($this->is_authenticated()) {
            if (!($this->instance->session->userdata('PrettyAuthLib_auth_success') || 
				$this->instance->session->userdata('PrettyAuthLib_auth_failure'))) {      
                $this->instance->load->helper('url');
                $this->instance->session->set_userdata(array('PrettyAuthLib_referer' => current_url()));
                $this->instance->session->set_userdata(array('PrettyAuthLib_auth_success' => true));
                
                $logout_route = ($logout_route!==null?$logout_route:$this->settings['logout_route']);
                $protocol = ($protocol!==null?$protocol:$this->settings['protocol']);
                $full_route = base_url($logout_route);
                if(!preg_match('/^'.$protocol.'/',$full_route)) {
                    $full_route = preg_replace('/^[a-zA-z]+\:\/\//',$protocol."://",$full_route);
                }
                redirect($full_route);                
                return null;
            } elseif ($this->instance->session->userdata('PrettyAuthLib_auth_success')) {
                $this->instance->session->unset_userdata('PrettyAuthLib_authenticated');
				$this->cleanup_success_failure();
                return true;
            } else {
		    $this->cleanup_success_failure();
                return false;
			}
        } else {           
		    $this->cleanup_success_failure();
            return true;
        }
    }
     
    /**
     * Performs the logout process and then redirect the user to the specified URL.
     *  successful by default
     * @param String $url the url to redirect the user to after the logout process
     * @param String $logout_route the route to use to perform the logout process
     * @return boolean return null during the process, true if logged out successfuly, false if not
     */
    public function logout_and_redirect(String $url, String $logout_route = null) 
    {
        $process = $this->logout($logout_route!==null?$logout_route:$this->settings['logout_route']);
        if ($process) {
            $this->instance->load->helper('url');
            redirect($url);
        }
        return $process;
        
    }
    /**
     * return the referer as set by PrettyAuthLib
     * @return String
     */
    public function get_referer() 
    {
        return $this->instance->session->userdata('PrettyAuthLib_referer');
    }
    
    /**
     * return the user data as recorded by PrettyAuthLib
     * @return array
     */
    public function get_user_data() 
    {
        $user_data = $this->instance->session->userdata('PrettyAuthLib_userData');
        return $user_data;
    }
    
    /**
     * set the user data to remember
     * @param array $user_data
     */
    public function set_user_data(array $user_data) 
    {
        $data = array ('PrettyAuthLib_userData' => $user_data);
        $this->instance->session->set_userdata($data);
    }
    
    /**
     * use this function to indicate a failure in the logout process to PrettyAuthLib 
     */
    public function set_failure() {
        $this->instance->session->unset_userdata('PrettyAuthLib_auth_success');
        $this->instance->session->set_userdata(array('PrettyAuthLib_auth_failure'=> true));
    }
    
    /**
     * use this function to indicate a success in the login process to PrettyAuthLib
     */
    public function set_success() {
        $this->instance->session->unset_userdata('PrettyAuthLib_auth_failure');
        $this->instance->session->set_userdata(array('PrettyAuthLib_auth_success' => true));        
    }
}

?>
