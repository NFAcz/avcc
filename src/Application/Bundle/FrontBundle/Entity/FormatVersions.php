<?php

namespace Application\Bundle\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * FormatVersions
 *
 * @ORM\Table(name="format_versions")
 * @ORM\Entity
 */
class FormatVersions
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50)
     * @Assert\NotBlank(message="Format version is required")
     */
    private $name;

    /**
     * @var real
     *
     * @ORM\Column(name="score", type="float", options={"default" = 0})
     * @Assert\NotBlank(message="Score is required")
     */
    private $score;

    /**
     * @ORM\ManyToOne(targetEntity="Formats" , fetch="EAGER", inversedBy="formatVersion")
     * @ORM\JoinColumn(
     *     name="format_id",
     *     referencedColumnName="id",
     *     nullable=true,
     *     onDelete="SET NULL"
     * )
     *
     * @var integer
     */
    private $formatVersionFormat;

    /**
     * @ORM\ManyToOne(targetEntity="Organizations", fetch="EAGER", inversedBy="formatOrg")
     * @ORM\JoinColumn(
     *     name="organization_id",
     *     referencedColumnName="id",
     *     nullable=true,
     *     onDelete="SET NULL"
     * )
     * @var integer
     */
    private $organization;

    /**
     * Returns Base
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Get Id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return \Application\Bundle\FrontBundle\Entity\FormatVersions
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set base formats
     *
     * @param \Application\Bundle\FrontBundle\Entity\Formats $formatVersionFormat
     *
     * @return \Application\Bundle\FrontBundle\Entity\FormatVersions
     */
    public function setFormatVersionFormat(\Application\Bundle\FrontBundle\Entity\Formats $formatVersionFormat)
    {
        $this->formatVersionFormat = $formatVersionFormat;

        return $this;
    }

    /**
     * Get base formats
     *
     * @return \Application\Bundle\FrontBundle\Entity\Formats
     */
    public function getFormatVersionFormat()
    {
        return $this->formatVersionFormat;
    }

    /**
     * Set organization.
     *
     * @param \Application\Bundle\FrontBundle\Entity\Organizations $organization
     *
     * @return \Application\Bundle\FrontBundle\Entity\FormatVersions
     */
    public function setOrganization(\Application\Bundle\FrontBundle\Entity\Organizations $organization)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return \Application\Bundle\FrontBundle\Entity\Organizations
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Get score
     *
     * @return real number
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set score
     *
     * @param float $score
     *
     * @return \Application\Bundle\FrontBundle\Entity\Colors
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

}
