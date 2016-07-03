<?php
namespace App\Utils;


use Nette\Application\UI\ITemplate;
use ReflectionClass;

trait Templated
{
    private $view;

    public function setView($view)
    {
        $this->view = $view;
    }

    public function render()
    {
        /** @var ITemplate $template */
        $template = $this->getTemplate();
        // change file extension to .latte
        if ($this->view) {
            $template->setFile(preg_replace('/\.[^.]+$/', '.' . $this->view . '.latte', $this->getClassPath()));
        } else {
            $template->setFile(preg_replace('/\.[^.]+$/', '.latte', $this->getClassPath()));
        }

        $template->render();
    }

    private function getClassPath()
    {
        $reflector = new ReflectionClass(static::class);
        return $reflector->getFileName();
    }
}