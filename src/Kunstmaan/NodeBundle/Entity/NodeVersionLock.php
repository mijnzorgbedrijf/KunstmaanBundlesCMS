<?php

namespace Kunstmaan\NodeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\NodeBundle\Repository\NodeVersionLockRepository;

/**
 * @ORM\Table(name="kuma_node_version_lock", indexes={
 *     @ORM\Index(name="nt_owner_public_idx", columns={"owner", "node_translation_id", "public_version"}),
 * })
 * @ORM\Entity(repositoryClass="Kunstmaan\NodeBundle\Repository\NodeVersionLockRepository")
 */
#[ORM\Table(name: 'kuma_node_version_lock')]
#[ORM\Index(name: 'nt_owner_public_idx', columns: ['owner', 'node_translation_id', 'public_version'])]
#[ORM\Entity(repositoryClass: NodeVersionLockRepository::class)]
class NodeVersionLock extends \Kunstmaan\AdminBundle\Entity\AbstractEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="owner", type="string", length=255)
     */
    #[ORM\Column(name: 'owner', type: 'string', length: 255)]
    private $owner;

    /**
     * @var NodeTranslation
     *
     * @ORM\ManyToOne(targetEntity="Kunstmaan\NodeBundle\Entity\NodeTranslation")
     * @ORM\JoinColumn(name="node_translation_id", referencedColumnName="id")
     */
    #[ORM\ManyToOne(targetEntity: NodeTranslation::class)]
    #[ORM\JoinColumn(name: 'node_translation_id', referencedColumnName: 'id')]
    private $nodeTranslation;

    /**
     * @var bool
     *
     * @ORM\Column(name="public_version", type="boolean")
     */
    #[ORM\Column(name: 'public_version', type: 'boolean')]
    private $publicVersion;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    #[ORM\Column(name: 'created_at', type: 'datetime')]
    private $createdAt;

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return NodeVersionLock
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return bool
     */
    public function isPublicVersion()
    {
        return $this->publicVersion;
    }

    /**
     * @param bool $publicVersion
     */
    public function setPublicVersion($publicVersion)
    {
        $this->publicVersion = $publicVersion;
    }

    /**
     * Set owner
     *
     * @param string
     *
     * @return NodeVersionLock
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner
     *
     * @return string
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set nodeTranslation
     *
     * @param \Kunstmaan\NodeBundle\Entity\NodeTranslation $nodeTranslation
     *
     * @return NodeVersionLock
     */
    public function setNodeTranslation(NodeTranslation $nodeTranslation = null)
    {
        $this->nodeTranslation = $nodeTranslation;

        return $this;
    }

    /**
     * Get nodeTranslation
     *
     * @return \Kunstmaan\NodeBundle\Entity\NodeTranslation
     */
    public function getNodeTranslation()
    {
        return $this->nodeTranslation;
    }
}
