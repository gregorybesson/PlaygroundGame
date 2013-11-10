<?php

namespace PlaygroundGame\Entity;

interface GameInterface
{
    public function createChrono();

    /** @PreUpdate */
    public function updateChrono();

    /**
     * @return the $id
     */
    public function getId();

    /**
     * @param field_type $id
     */
    public function setId($id);

    /**
     * @return the $type
     */
    public function getType();

    /**
     * @param field_type $type
     */
    public function setType($type);

    /**
     * @return the $name
     */
    public function getTitle();

    /**
     * @param field_type $name
     */
    public function setTitle($title);

    /**
     * @return the $identifier
     */
    public function getIdentifier();

    /**
     * @param field_type $identifier
     */
    public function setIdentifier($identifier);

    /**
     * @return the $main_image
     */
    public function getMainImage();

    /**
     * @param field_type $main_image
     */
    public function setMainImage($main_image);

    /**
     * @return the $second_image
     */
    public function getSecondImage();

    /**
     * @param field_type $second_image
     */
    public function setSecondImage($second_image);

    /**
     * @return the $canal
     */
    public function getCanal();

    /**
     * @param field_type $canal
     */
    public function setCanal($canal);

    /**
     * @return the $push_home
     */
    public function getPushHome();

    /**
     * @param field_type $push_home
     */
    public function setPushHome($push_home);

    /**
     * @return the $push_start_date
     */
    public function getPushStartDate();

    /**
     * @param field_type $push_start_date
     */
    public function setPushStartDate($push_start_date);

    /**
     * @return the $push_end_date
     */
    public function getPushEndDate();

    /**
     * @param field_type $push_end_date
     */
    public function setPushEndDate($push_end_date);

    /**
     * @return the $publication_date
     */
    public function getPublicationDate();

    /**
     * @param field_type $publication_date
     */
    public function setPublicationDate($publication_date);

    /**
     * @return the $start_date
     */
    public function getStartDate();

    /**
     * @param field_type $start_date
     */
    public function setStartDate($start_date);

    /**
     * @return the $end_date
     */
    public function getEndDate();

    /**
     * @param field_type $end_date
     */
    public function setEndDate($end_date);

    /**
     * @return the $close_date
     */
    public function getCloseDate();

    /**
     * @param field_type $close_date
     */
    public function setCloseDate($close_date);

    /**
     * @return the $prize_type
     */
    public function getPrizeType();

    /**
     * @param field_type $prize_type
     */
    public function setPrizeType($prize_type);

    /**
     * @return the $prize_mode
     */
    public function getPrizeMode();

    /**
     * @param field_type $prize_mode
     */
    public function setPrizeMode($prize_mode);

    /**
     * @return the $prize_winners
     */
    public function getPrizeWinners();

    /**
     * @param field_type $prize_winners
     */
    public function setPrizeWinners($prize_winners);

    /**
     * @return the $layout
     */
    public function getLayout();

    /**
     * @param field_type $layout
     */
    public function setLayout($layout);

    /**
     * @return the $welcome_page_id
     */
    public function getWelcomeBlock();

    /**
     * @param field_type $welcome_page_id
     */
    public function setWelcomeBlock($welcome_block);

    /**
     * @return the $game_page_id
     */
    public function getGamePageId();

    /**
     * @param field_type $game_page_id
     */
    public function setGamePageId($game_page_id);

    /**
     * @return the $participate_page_id
     */
    public function getParticipatePageId();

    /**
     * @param field_type $participate_page_id
     */
    public function setParticipatePageId($participate_page_id);

    /**
     * @return the $result_page_id
     */
    public function getResultPageId();

    /**
     * @param field_type $result_page_id
     */
    public function setResultPageId($result_page_id);

    /**
     * @return the $share_page_id
     */
    public function getSharePageId();

    /**
     * @param field_type $share_page_id
     */
    public function setSharePageId($share_page_id);

    /**
     * @return the $recirculation_page_id
     */
    public function getRecirculationPageId();

    /**
     * @param field_type $recirculation_page_id
     */
    public function setRecirculationPageId($recirculation_page_id);

    /**
     * @return the $closed_page_id
     */
    public function getClosedPageId();

    /**
     * @param field_type $closed_page_id
     */
    public function setClosedPageId($closed_page_id);

    /**
     * @return the $created_at
     */
    public function getCreatedAt();
    /**
     * @param \DateTime $created_at
     */
    public function setCreatedAt($created_at);

    /**
     * @return the $updated_at
     */
    public function getUpdatedAt();

    /**
     * @param \DateTime $updated_at
     */
    public function setUpdatedAt($updated_at);

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

    public function setInputFilter();
    public function getInputFilter();
}
