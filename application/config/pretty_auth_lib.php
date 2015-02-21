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

$config['login_route'] = '/auth/login';
$config['logout_route'] = '/auth/logout';

$config['cookie_name'] = 'prettyAuthLib_remember_authentication';
$config['cookie_lifetime'] = 60 * 60 * 24 * 7; //seven days  by default  

?>
