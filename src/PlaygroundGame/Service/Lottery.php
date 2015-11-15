<?php

namespace PlaygroundGame\Service;

use Zend\ServiceManager\ServiceManagerAwareInterface;
use PlaygroundGame\Mapper\GameInterface as GameMapperInterface;

class Lottery extends Game implements ServiceManagerAwareInterface
{
    /**
     * @var LotteryMapperInterface
     */
    protected $lotteryMapper;

    public function getGameEntity()
    {
        return new \PlaygroundGame\Entity\Lottery;
    }

    /**
     * getLotteryMapper
     *
     * @return LotteryMapperInterface
     */
    public function getLotteryMapper()
    {
        if (null === $this->lotteryMapper) {
            $this->lotteryMapper = $this->getServiceManager()->get('playgroundgame_lottery_mapper');
        }

        return $this->lotteryMapper;
    }

    /**
     * setLotteryMapper
     *
     * @param  LotteryMapperInterface $lotteryMapper
     * @return Lottery
     */
    public function setLotteryMapper(GameMapperInterface $lotteryMapper)
    {
        $this->lotteryMapper = $lotteryMapper;

        return $this;
    }
}
