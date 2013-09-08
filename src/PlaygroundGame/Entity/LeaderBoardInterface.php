<?php
namespace PlaygroundGame\Entity;

Interface LeaderBoardInterface
{

    public function createChrono();

    /** @PreUpdate */
    public function updateChrono();

    /**
     * @return the $game_id
     */
    public function getGame_id();

    /**
     * @param field_type $game_id
     */
    public function setGame_id($game_id);

    /**
     * @return the $user_id
     */
    public function getUser_id();

    /**
     * @param field_type $user_id
     */
    public function setUser_id($user_id);

    /**
     * @return the $points
     */
    public function getPoints();

    /**
     * @param field_type $points
     */
    public function setPoints($points);

    /**
     * @return the $created_at
     */
    public function getCreated_at();

    /**
     * @param \DateTime $created_at
     */
    public function setCreated_at($created_at);

    /**
     * @return the $updated_at
     */
    public function getUpdated_at();

    /**
     * @param \DateTime $updated_at
     */
    public function setUpdated_at($updated_at);

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy();

    /**
     * Populate from an array.
     *
     * @param array $data
     */
    public function populate($data = array());
}
