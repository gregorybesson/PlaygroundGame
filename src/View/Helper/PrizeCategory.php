<?php

namespace PlaygroundGame\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class PrizeCategory extends AbstractHelper
{
    protected $prizeCategoryService;

    /**
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
     * @return \PlaygroundGame\Service\PrizeCategory
     */
    public function getPrizeCategoryService()
    {
        return $this->prizeCategoryService;
    }
}
