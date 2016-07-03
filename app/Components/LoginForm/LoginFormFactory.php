<?php
namespace App\Components\LoginForm;

interface LoginFormFactory
{
	/** @return LoginForm */
	public function create();
}