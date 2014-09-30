<?php

namespace PlaygroundGame\View\Helper;

use Zend\View\Helper\AbstractHelper;

class PrizeCategory extends AbstractHelper
{
    protected $prizeCategoryService;

    /**
     * @param  int|string $identifier
     * @return string
     */
    public function __invoke()
    {
        $results = $this->getPrizeCategoryService()->getActivePrizeCategories();

        return $results;
    }

    /**
     * @param \PlaygroundGame\Service\PrizeCategory $prizeCategoryService
     */
    public function setPrizeCategoryService(\PlaygroundGame\Service\PrizeCategory $prizeCategoryService)
    {
        $this->prizeCategoryService = $prizeCategoryService;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrizeCategoryService()
    {
        return $this->prizeCategoryService;
    }
}
