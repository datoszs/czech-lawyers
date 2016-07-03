<?php
namespace App\Components\UserForm;


interface UserFormFactory
{
	/** @return UserForm */
	public function create($id = null);
}