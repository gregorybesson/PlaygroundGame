<?php

namespace PlaygroundGame\Service;

use PlaygroundGame\Mapper\GameInterface as GameMapperInterface;

class Lottery extends Game
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
            $this->lotteryMapper = $this->serviceLocator->get('playgroundgame_lottery_mapper');
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
