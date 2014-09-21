<?php
namespace PlaygroundGame\Form\Frontend;

use Zend\Form\Form;
use Zend\Form\Element\Captcha;
use Zend\Captcha\Image as CaptchaImage;

class PostVoteVote extends Form
{
    public function __construct($urlcaptcha = null)
    {
        parent::__construct('');
        $this->setAttribute('method', 'post');

        $dirdata = './data';

        //pass captcha image options
        $captchaImage = new CaptchaImage(  array(
                'font' => $dirdata . '/fonts/arial.ttf',
                'width' => 110,
                'height' => 60,
                'wordlen' => 4,
                'dotNoiseLevel' => 20,
                'lineNoiseLevel' => 1)
        );

        $captchaImage->setImgDir($dirdata.'/captcha');
        $captchaImage->setImgUrl($urlcaptcha);

        //add captcha element...
        $this->add(array(
            'type' => 'Zend\Form\Element\Captcha',
            'name' => 'captcha',
            'options' => array(
                'label' => 'Saisissez le texte ci-dessous :*',
                'captcha' => $captchaImage,
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Voter'
            ),
        ));
    }

}
