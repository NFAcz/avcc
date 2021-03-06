<?php
/**
 * AVCC
 * 
 * @category AVCC
 * @package  Application
 * @author   Nouman Tayyab <nouman@weareavp.com>
 * @author   Rimsha Khalid <rimsha@weareavp.com>
 * @license  AGPLv3 http://www.gnu.org/licenses/agpl-3.0.txt
 * @copyright Audio Visual Preservation Solutions, Inc
 * @link     http://avcc.weareavp.com
 */
namespace Application\Bundle\FrontBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Application\Bundle\FrontBundle\Entity\PrintTypes as PrintTypes;
use Application\Bundle\FrontBundle\Entity\ReelCore as ReelCore;
use Application\Bundle\FrontBundle\Entity\Colors as Colors;
use Application\Bundle\FrontBundle\Entity\Sounds as Sounds;

use Application\Bundle\FrontBundle\Entity\AcidDetectionStrips as AcidDetectionStrips;

/**
 * FilmRecords
 *
 * @ORM\Table(name="film_records")
 * @ORM\Entity
 */
class FilmRecords
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
     * @var \Application\Bundle\FrontBundle\Entity\PrintTypes
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\FrontBundle\Entity\PrintTypes")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="print_type_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $printType = null;

    /**
     * @var \Application\Bundle\FrontBundle\Entity\ReelCore
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\FrontBundle\Entity\ReelCore")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reel_core_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $reelCore = null;

    /**
     * @var integer
     *
     * @ORM\Column(name="footage", type="integer", nullable=true)
     */
    private $footage;

    /**
     * @var integer
     *
     * @ORM\Column(name="media_diameter", type="integer", nullable=true)
     */
    private $mediaDiameter;

    /**
     * @var \Application\Bundle\FrontBundle\Entity\Bases
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\FrontBundle\Entity\Bases")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="base_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $bases = null;

    /**
     * @var \Application\Bundle\FrontBundle\Entity\Colors
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\FrontBundle\Entity\Colors")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="color_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $colors = null;

    /**
     * @var \Application\Bundle\FrontBundle\Entity\Sounds
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\FrontBundle\Entity\Sounds")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sound_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $sound = null;

    /**
     * @var \Application\Bundle\FrontBundle\Entity\FrameRates
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\FrontBundle\Entity\FrameRates")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="frame_rate_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $frameRate = null;

    /**
     * @var \Application\Bundle\FrontBundle\Entity\AcidDetectionStrips
     *
     * @ORM\ManyToOne(targetEntity="Application\Bundle\FrontBundle\Entity\AcidDetectionStrips")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="acid_detection_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $acidDetectionStrip = null;
    
    /**
     * @ORM\Column(name="edge_code_year", type="string", nullable=true)
     * @var string
     *
     */
    private $edgeCodeYear;

    /**
     * @var float
     *
     * @ORM\Column(name="shrinkage", type="float", nullable=true)
     */
    private $shrinkage;

    /**
     * @var \Application\Bundle\FrontBundle\Entity\Records
     *
     * @ORM\OneToOne(targetEntity="Application\Bundle\FrontBundle\Entity\Records", cascade={"all","merge","persist","refresh","remove"}, inversedBy="filmRecord")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="record_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * })
     */
    private $record;

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
     * Set edge Code Year
     *
     * @param string $edgeCodeYear
     *
     * @return \Application\Bundle\FrontBundle\Entity\Records
     */
    public function setEdgeCodeYear($edgeCodeYear) {
        $this->edgeCodeYear = $edgeCodeYear;

        return $this;
    }

    /**
     * Get edge Code Year
     *
     * @return string
     */
    public function getEdgeCodeYear() {
        return $this->edgeCodeYear;
    }

    /**
     * Set print type.
     *
     * @param \Application\Bundle\FrontBundle\Entity\PrintTypes $pt
     *
     * @return \Application\Bundle\FrontBundle\Entity\FilmRecords
     */
    public function setPrintType(PrintTypes $pt = null)
    {
        $this->printType = $pt;

        return $this;
    }

    /**
     * Get print type.
     *
     * @return \Application\Bundle\FrontBundle\Entity\PrintTypes
     */
    public function getPrintType()
    {
        return $this->printType;
    }

    /**
     * Set reel core
     *
     * @param \Application\Bundle\FrontBundle\Entity\ReelCore $reelcore
     *
     * @return \Application\Bundle\FrontBundle\Entity\FilmRecords
     */
    public function setReelCore(ReelCore $reelcore = null)
    {
        $this->reelCore = $reelcore;

        return $this;
    }

    /**
     * Get reel core.
     *
     * @return \Application\Bundle\FrontBundle\Entity\ReelCore
     */
    public function getReelCore()
    {
        return $this->reelCore;
    }

    /**
     * Set footage.
     *
     * @param string $footage
     *
     * @return \Application\Bundle\FrontBundle\Entity\FilmRecords
     */
    public function setFootage($footage)
    {
        $this->footage = $footage;

        return $this;
    }

    /**
     * Get footage
     *
     * @return integer
     */
    public function getFootage()
    {
        return $this->footage;
    }

    /**
     * Set media diameter.
     *
     * @param string $mediaDiameter
     *
     * @return \Application\Bundle\FrontBundle\Entity\FilmRecords
     */
    public function setMediaDiameter($mediaDiameter)
    {
        $this->mediaDiameter = $mediaDiameter;

        return $this;
    }

    /**
     * Get media diameter.
     *
     * @return integer
     */
    public function getMediaDiameter()
    {
        return $this->mediaDiameter;
    }

    /**
     * Set base.
     *
     * @param \Application\Bundle\FrontBundle\Entity\Bases $bases
     *
     * @return \Application\Bundle\FrontBundle\Entity\FilmRecords
     */
    public function setBases(Bases $bases = null)
    {
        $this->bases = $bases;

        return $this;
    }

    /**
     * Get base.
     *
     * @return \Application\Bundle\FrontBundle\Entity\Bases
     */
    public function getBases()
    {
        return $this->bases;
    }

    /**
     * Set color.
     *
     * @param \Application\Bundle\FrontBundle\Entity\Colors $color
     *
     * @return \Application\Bundle\FrontBundle\Entity\FilmRecords
     */
    public function setColors(Colors $color = null)
    {
        $this->colors = $color;

        return $this;
    }

    /**
     * Get color.
     *
     * @return \Application\Bundle\FrontBundle\Entity\Colors
     */
    public function getColors()
    {
        return $this->colors;
    }

    /**
     * Set sound.
     *
     * @param \Application\Bundle\FrontBundle\Entity\Sounds $sound
     *
     * @return \Application\Bundle\FrontBundle\Entity\FilmRecords
     */
    public function setSound(Sounds $sound = null)
    {
        $this->sound = $sound;

        return $this;
    }

    /**
     * Get sound.
     *
     * @return \Application\Bundle\FrontBundle\Entity\Sounds
     */
    public function getSound()
    {
        return $this->sound;
    }

    /**
     * Set frame rate.
     *
     * @param \Application\Bundle\FrontBundle\Entity\FrameRates $frameRate
     *
     * @return \Application\Bundle\FrontBundle\Entity\FilmRecords
     */
    public function setFrameRate(FrameRates $frameRate = null)
    {
        $this->frameRate = $frameRate;

        return $this;
    }

    /**
     * Get sound.
     *
     * @return \Application\Bundle\FrontBundle\Entity\FrameRates
     */
    public function getFrameRate()
    {
        return $this->frameRate;
    }

    /**
     * Set acid detection strip.
     *
     * @param \Application\Bundle\FrontBundle\Entity\AcidDetectionStrips $ads
     *
     * @return \Application\Bundle\FrontBundle\Entity\FilmRecords
     */
    public function setAcidDetectionStrip(AcidDetectionStrips $ads = null)
    {
        $this->acidDetectionStrip = $ads;

        return $this;
    }

    /**
     * Get acid detection strip.
     *
     * @return \Application\Bundle\FrontBundle\Entity\AcidDetectionStrips
     */
    public function getAcidDetectionStrip()
    {
        return $this->acidDetectionStrip;
    }

    /**
     * Set shrinkage
     *
     * @param float $shrinkage
     *
     * @return \Application\Bundle\FrontBundle\Entity\FilmRecords
     */
    public function setShrinkage($shrinkage)
    {
        $this->shrinkage = $shrinkage;

        return $this;
    }

    /**
     * Get Shrinkage.
     *
     * @return float
     */
    public function getShrinkage()
    {
        return $this->shrinkage;
    }

    /**
     * Set record.
     *
     * @param \Application\Bundle\FrontBundle\Entity\Records $r
     *
     * @return \Application\Bundle\FrontBundle\Entity\FilmRecords
     */
    public function setRecord(Records $r)
    {
        $this->record = $r;

        return $this;
    }

    /**
     * Get record.
     *
     * @return \Application\Bundle\FrontBundle\Entity\Records
     */
    public function getRecord()
    {
        return $this->record;
    }

}
