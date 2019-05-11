<?php

namespace PlaygroundGameTest\Form\Frontend;

use PlaygroundGameTest\Bootstrap;
use PlaygroundGame\Form\Frontend\InstantWinOccurrenceCode;

class InstantWinOccurrenceCodeTest extends \PHPUnit\Framework\TestCase
{
    protected $sm;

    protected $translator;

    protected $form;

    protected $codeInputData;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->getForm();
        $this->codeInputData = array(
            'code-input' => '',
        );
        parent::setUp();
    }

    public function testTextInputTagStriped()
    {
        $this->form->setData(array('code-input' => '<tag>sometext</tag>', ));
        $this->assertTrue($this->form->isValid());

        $data = $this->form->getData();
        $this->assertEquals('sometext', $data['code-input']);
    }

    public function testTextInputAlphaNumValid()
    {
        $this->form->setData(array('code-input' => 'Valid012345-$@code', ));
        $this->assertTrue($this->form->isValid());
    }

    public function testTextInputSpacesRemoved()
    {
        $this->form->setData(array('code-input' => ' no-spaces ', ));
        $this->assertTrue($this->form->isValid());
        $data = $this->form->getData();
        $this->assertEquals('no-spaces', $data['code-input']);
    }

    public function testTextInputNotEmpty()
    {
        $this->form->setData(array('code-input' => ' ', ));
        $this->assertFalse($this->form->isValid());

        $this->form->setData(array('code-input' => '', ));
        $this->assertFalse($this->form->isValid());
    }

    public function getForm()
    {
        if (null === $this->form) {
            $this->form = $this->sm->get('playgroundgame_instantwinoccurrencecode_form');
        }

        return $this->form;
    }
}
