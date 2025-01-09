<?php 
session_start();
session_destroy();
foreach($_SESSION as $k => $v){
    unset($_SESSION[$k]);
}

if( isset( $_GET['id'] ) && is_int( intval( $_GET['id'] ) ) ) {
	header('location:https://giving.usc.edu/signed/?r=' . intval( $_GET['id'] ) );
} else {
	header('location:https://giving.usc.edu/');
}