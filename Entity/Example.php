<?php

namespace Nasetech\CodeRightBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Example
 *
 * @ORM\Table(name="example")
 * @ORM\Entity
 */
class Example
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
     * @ORM\Column(name="uid", type="string", length=45, nullable=false)
     */
    private $uid;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set uid
     *
     * @param string $uid
     *
     * @return this
     */
    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    /**
     * Get uid
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }
}
