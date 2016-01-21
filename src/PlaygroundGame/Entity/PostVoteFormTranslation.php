<?php

namespace PlaygroundGame\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="game_postvote_form_translation", indexes={
 *      @ORM\Index(name="game_postvote_form_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class PostVoteFormTranslation extends AbstractTranslation
{
}
