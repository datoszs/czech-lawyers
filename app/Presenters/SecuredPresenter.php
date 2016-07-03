<?php

namespace App\Presenters;

use Nette;
use App\Model;
use Nette\Application\ForbiddenRequestException;

abstract class SecuredPresenter extends BasePresenter
{
	public function checkRequirements($element)
	{
		if ($element instanceof Nette\Application\UI\MethodReflection) {
			$privilege = Nette\Application\UI\ComponentReflection::parseAnnotation($element, 'privilege');
			if (count($privilege) == 2) {
					$this->requirePrivilege($this->stringToConstant($privilege[0]), $this->stringToConstant($privilege[1]));
			} else {
				throw new ForbiddenRequestException("Anotation @privilege of method {$element->getName()} has invalid count of parameters.");
			}
		} else {
			parent::checkRequirements($element);
		}
	}

	private function stringToConstant($value)
	{
		if (Nette\Utils\Strings::contains($value, '::')) {
			return constant($value);
		}
		return $value;
	}
}
