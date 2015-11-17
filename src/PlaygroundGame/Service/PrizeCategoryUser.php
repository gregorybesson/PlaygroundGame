<?php

namespace PlaygroundGame\Service;

use PlaygroundGame\Entity\PrizeCategoryUser as PrizeCategoryUserEntity;
use PlaygroundGame\Mapper\PrizeCategoryUser as PrizeCategoryUserMapper;
use PlaygroundGame\Service\PrizeCategory as PrizeCategoryService;

class PrizeCategoryUser extends PrizeCategoryService
{
    /**
     * @var prizeCategoryUserMapper
     */
    protected $prizeCategoryUserMapper;

    public function edit(array $data, $user, $formClass)
    {
        $this->getPrizeCategoryUserMapper()->removeAll($user);
        if (isset($data['prizeCategory']) && $data['prizeCategory']) {
            foreach ($data['prizeCategory'] as $k => $v) {
                $category = $this->getPrizeCategoryMapper()->findById($v);
                $userCategory = new PrizeCategoryUserEntity($user, $category);
                $this->getPrizeCategoryUserMapper()->insert($userCategory);
            }
            $this->getEventManager()->trigger(__FUNCTION__.'.post', $this, array('user' => $user, 'data' => $data));
        }

        return true;
    }

    /**
     * getPrizeCategoryUserMapper
     *
     * @return PrizeCategoryUserMapper
     */
    public function getPrizeCategoryUserMapper()
    {
        if (null === $this->prizeCategoryUserMapper) {
            $this->prizeCategoryUserMapper = $this->getServiceManager()->get('playgroundgame_prizecategoryuser_mapper');
        }

        return $this->prizeCategoryUserMapper;
    }

    /**
     * setPrizeCategoryUserMapper
     *
     * @param PrizeCategoryUserMapper $prizeCategoryUserMapper
     *
     */
    public function setPrizeCategoryUserMapper(PrizeCategoryUserMapper $prizeCategoryUserMapper)
    {
        $this->prizeCategoryUserMapper = $prizeCategoryUserMapper;

        return $this;
    }
}
