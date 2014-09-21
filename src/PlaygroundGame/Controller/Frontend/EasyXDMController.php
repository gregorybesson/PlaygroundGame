<?php

namespace PlaygroundGame\Controller\Frontend;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class EasyXDMController extends AbstractActionController
{

    public function indexAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTerminal(true);
        return $viewModel;
    }
    
    public function nameAction()
    {
    	$viewModel = new ViewModel();
    	$viewModel->setTerminal(true);
    	return $viewModel;
    }
}