<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
$cache = array (
  'table' => 'accounts',
  'fields' => 
  array (
    0 => 'id',
    1 => 'email',
    2 => 'gname',
    3 => 'sname',
    4 => 'badge',
    5 => 'dob',
    6 => 'phone',
    7 => 'cell',
    8 => 'address',
    9 => 'econtact',
    10 => 'ephone',
    11 => 'password',
    12 => 'salt',
    13 => 'reg_status',
    14 => 'created',
    15 => 'login',
  ),
  'validation' => 
  array (
    'email' => 
    array (
      'field' => 'email',
      'label' => 'Email',
      'rules' => 
      array (
        0 => 'required',
        1 => 'trim',
        2 => 'unique',
        3 => 'valid_email',
        'min_length' => 3,
        'max_length' => 55,
      ),
    ),
    'password' => 
    array (
      'field' => 'password',
      'label' => 'Password',
      'rules' => 
      array (
        0 => 'required',
        1 => 'trim',
        'min_length' => 5,
        2 => 'encrypt',
      ),
    ),
    'confirm_password' => 
    array (
      'field' => 'confirm_password',
      'label' => 'Confirm Password',
      'rules' => 
      array (
        0 => 'encrypt',
        'matches' => 'password',
      ),
    ),
    'id' => 
    array (
      'field' => 'id',
      'label' => 'Identifier',
      'rules' => 
      array (
        0 => 'integer',
      ),
    ),
    'gname' => 
    array (
      'field' => 'gname',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'sname' => 
    array (
      'field' => 'sname',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'badge' => 
    array (
      'field' => 'badge',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'dob' => 
    array (
      'field' => 'dob',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'phone' => 
    array (
      'field' => 'phone',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'cell' => 
    array (
      'field' => 'cell',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'address' => 
    array (
      'field' => 'address',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'econtact' => 
    array (
      'field' => 'econtact',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'ephone' => 
    array (
      'field' => 'ephone',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'salt' => 
    array (
      'field' => 'salt',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'reg_status' => 
    array (
      'field' => 'reg_status',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'created' => 
    array (
      'field' => 'created',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
    'login' => 
    array (
      'field' => 'login',
      'label' => '',
      'rules' => 
      array (
      ),
    ),
  ),
  'has_one' => 
  array (
  ),
  'has_many' => 
  array (
    'book' => 
    array (
      'class' => 'book',
      'other_field' => 'account',
      'join_self_as' => 'account',
      'join_other_as' => 'book',
    ),
  ),
);