<?php
namespace App\Utils;


use Nette\Application\UI\Form;
use Nette\Forms\Controls\BaseControl as NetteBaseControl;
use Nette\Forms\Controls\Button;
use Nette\Forms\Controls\Checkbox;
use Nette\Forms\Controls\CheckboxList;
use Nette\Forms\Controls\MultiSelectBox;
use Nette\Forms\Controls\RadioList;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Forms\Controls\TextBase;
use Nette\Forms\Rendering\DefaultFormRenderer;

class BootstrapForm extends Form
{
    const VERTICAL = 'form-vertical';
    const HORIZONTAL = 'form-horizontal';

    private $type = self::HORIZONTAL;

    public function __construct(\Nette\ComponentModel\IContainer $parent = null, $name = null)
    {
        parent::__construct($parent, $name);

        /** @var DefaultFormRenderer $renderer */
        $renderer = $this->getRenderer();
        $renderer->wrappers['controls']['container'] = NULL;
        $renderer->wrappers['pair']['container'] = 'div class=form-group';
        $renderer->wrappers['pair']['.error'] = 'has-error';
        $renderer->wrappers['control']['container'] = 'div class=col-sm-9';
        $renderer->wrappers['label']['container'] = 'div class="col-sm-3 control-label"';
        $renderer->wrappers['control']['description'] = 'span class=help-block';
        $renderer->wrappers['control']['errorcontainer'] = 'span class=help-block';
        $renderer->wrappers['error']['container'] = 'div';
        $renderer->wrappers['error']['item'] = 'div class="alert alert-danger"';
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    private function setUpControls()
    {
        foreach ($this->getControls() as $control) {
            if ($control instanceof Button) {
                $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                $usedPrimary = TRUE;

            } elseif ($control instanceof TextBase || $control instanceof SelectBox || $control instanceof MultiSelectBox) {
                $control->getControlPrototype()->addClass('form-control');

            } elseif ($control instanceof Checkbox || $control instanceof CheckboxList || $control instanceof RadioList) {
                $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
            }
        }
    }

    public function disable()
    {
        /** @var NetteBaseControl $control */
        foreach ($this->getControls() as $control) {
            $control->setAttribute('readonly');
            if ($control instanceof SubmitButton) {
                $control->setDisabled();
            }
        }
    }

    public function render(...$args)
    {
        $this->getElementPrototype()->addClass($this->type);
        $this->setUpControls();
        parent::render();
    }
}