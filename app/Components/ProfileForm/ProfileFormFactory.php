<?php
namespace App\Components\ProfileForm;

interface ProfileFormFactory
{
	/** @return ProfileForm */
	public function create();
}