<?php declare(strict_types=1);
namespace App\Utils;

use Nextras\Orm\Entity\IEntity;

trait Diffable
{
	/** @var array */
	private $originals;

	public function __set($name, $value)
	{
		if (!$this->hasValue($name) || ($this->$name !== $value && !isset($this->originals[$name]))) {
			$this->originals[$name] = $this->hasValue($name) ? $this->getStringRepresentation($this->$name) : null;
		}
		parent::__set($name, $value);
	}

	public function getModificationsSummary(): string
	{
		$output = [];
		foreach ($this->originals as $name => $original) {
			$output[] = sprintf(
				'[%s: %s => %s]',
				$name,
				$original,
				$this->getStringRepresentation($this->$name)
			);
		}
		return implode(',', $output);
	}

	private function getStringRepresentation($mixed): string
	{
		if (is_null($mixed)) {
			return (string) $mixed;
		} elseif (is_scalar($mixed)) {
			return (string) $mixed;
		} elseif ($mixed instanceof IEntity) {
			return (string) $mixed->getPersistedId();
		} elseif (is_object($mixed) &&  method_exists($mixed, '__toString')) {
			return $mixed->__toString();
		} elseif (is_array($mixed)) {
			return '[' . implode(', ', array_map(function ($mixed) { return $this->getStringRepresentation($mixed); }, $mixed)) . ']';
		}
		return '< not-serializable >';
	}

	public function onPersist($id): void
	{
		parent::onPersist($id);
		$this->originals = [];
	}
}
