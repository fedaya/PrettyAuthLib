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
class PrettyAuthLib {
    
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
    
    private static $AUTHENTICATION_TOKEN = true;
    
    public function __construct() 
    {
        $this->instance =& get_instance(); 
        $this->instance->load->library('session');
        $this->instance->config->load('pretty_auth_lib', true);
        $this->settings = $this->config->item('pretty_auth_lib');
        $this->cookie = $this->input->cookie($this->settings['cookie_name'], true);
        
    }
    
    /**
     * Return the authentication status
     * @return boolean
     */
    public function is_authenticated() 
    {
        return $this->session->userdata('PrettyAuthLib_authenticated') == $this->AUTHENTICATION_TOKEN;
    }
    
    /**
     * Performs the login process
     * @param String $login_route
     * @return boolean return false during the process and true afterwards
     */
    public function login(String $login_route = null) 
    {
        if (!$this->is_authenticated())
        {
            if (!$this->cookie) {
                $this->load->helper('url');
                $this->session->set_userdata(array(
                    'PrettyAuthLib_referer' => current_url()
                ));
                redirect($login_route!==null?$login_route:$this->settings['login_route']);
                return false;
            } else {                
                $array = array (
                    'PrettyAuthLib_authenticated' => $this->AUTHENTICATION_TOKEN,
                    'PrettyAuthLib_userData' => $this->cookie['value']['userData'],
                );
                $this->session->set_userdata($array);
                $this->cookie['expire'] = $this->settings['cookie_lifetime'];
                $this->input->set_cookie($this->cookie);
                return true;
            }
        } else {
            $array = array (
                'PrettyAuthLib_authenticated' => $this->AUTHENTICATION_TOKEN,
            );
            $this->session->set_userdata($array);
            return true;
        }           
    }
    
    /**
     * creates the authentication cookie if you want to remember it
     */
    public function remember_authentication() 
    {
        if (!$this->cookie) {
            $this->load->helper('url');
            $cookie_value = array(
                'userData' => $this->session->userdata('PrettyAuthLib_userData'),
            );
            $this->cookie = array(
                'name' => $this->settings['cookie_name'],
                'value' => "",
                'expire' => $this->settings['cookie_lifetime'],
                'domain' => base_url(),
                'path' => '/',
                'prefix' => '',
                'secure' => true,
            );
            $this->input->set_cookie($this->cookie);
        }
    }
    
    /**
     * deletes the authentication cookie
     */
    public function cancel_authentication_memory() 
    {
        $this->cookie['expire'] = time() - 3600;
        $this->cookie['value'] = array();
        $this->input->set_cookie($this->cookie);
        $this->cookie = false;
    }
    
    /**
     * Performs the logout process
     * @param String $logout_route the url to redirect the user to after the logout process
     * @return boolean return false during the process and true afterwards
     */
    public function logout(String $logout_route = null) 
    {
        if ($this->is_authenticated()) {
                $this->load->helper('url');
            $this->session->set_userdata(array(
                'PrettyAuthLib_referer' => current_url()
            ));
            redirect($logout_route!==null?$logout_route:$this->settings['logout_route']);  
            return false;
        } else {
            return true;
        }
    }
     
    /**
     * Performs the logout process and then redirect the user to the specified URL
     * @param String $url the url to redirect the user to after the logout process
     * @param String $logout_route the route to use to perform the logout process
     * @return boolean return false during the process and true afterwards
     */
    public function logout_and_redirect(String $url, String $logout_route = null) 
    {
        if ($this->is_authenticated()) {
            return $this->logout($logout_route!==null?$logout_route:$this->settings['logout_route']);
        } else {
            $this->load->helper('url');
            redirect($url);
            return true;
        }
        
    }
    /**
     * return the referer as set by PrettyAuthLib
     * @return String
     */
    public function get_referer() 
    {
        return $this->session->userdata('PrettyAuthLib_referer');
    }
    
    /**
     * return the user data as recorded by PrettyAuthLib
     * @return array
     */
    public function get_user_data() 
    {
        $user_data = $this->session->userdata('PrettyAuthLib_userData');
        return $user_data;
    }
    /**
     * set the user data to remember
     * @param array $user_data
     */
    public function set_user_data(array $user_data) 
    {
        $data = array ('PrettyAuthLib_userData' => $user_data);
        $this->session->set_userdata($data);
    }
}

?>
