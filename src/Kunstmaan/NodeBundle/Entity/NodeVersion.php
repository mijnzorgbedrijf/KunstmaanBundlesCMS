<?php

namespace Kunstmaan\NodeBundle\Entity;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Kunstmaan\AdminBundle\Entity\AbstractEntity;
use Kunstmaan\NodeBundle\Repository\NodeVersionRepository;
use Kunstmaan\UtilitiesBundle\Helper\ClassLookup;

/**
 * @ORM\Entity(repositoryClass="Kunstmaan\NodeBundle\Repository\NodeVersionRepository")
 * @ORM\Table(name="kuma_node_versions", indexes={@ORM\Index(name="idx_node_version_lookup", columns={"ref_id", "ref_entity_name"})})
 * @ORM\HasLifecycleCallbacks()
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
#[ORM\Entity(repositoryClass: NodeVersionRepository::class)]
#[ORM\Table(name: 'kuma_node_versions')]
#[ORM\Index(name: 'idx_node_version_lookup', columns: ['ref_id', 'ref_entity_name'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\ChangeTrackingPolicy('DEFERRED_EXPLICIT')]
class NodeVersion extends AbstractEntity
{
    const DRAFT_VERSION = 'draft';
    const PUBLIC_VERSION = 'public';

    /**
     * @var NodeTranslation
     *
     * @ORM\ManyToOne(targetEntity="NodeTranslation", inversedBy="nodeVersions")
     * @ORM\JoinColumn(name="node_translation_id", referencedColumnName="id")
     */
    #[ORM\ManyToOne(targetEntity: NodeTranslation::class, inversedBy: 'nodeVersions')]
    #[ORM\JoinColumn(name: 'node_translation_id', referencedColumnName: 'id')]
    protected $nodeTranslation;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    #[ORM\Column(name: 'type', type: 'string')]
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    #[ORM\Column(name: 'owner', type: 'string')]
    protected $owner;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    #[ORM\Column(name: 'created', type: 'datetime')]
    protected $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    #[ORM\Column(name: 'updated', type: 'datetime')]
    protected $updated;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint", name="ref_id")
     */
    #[ORM\Column(name: 'ref_id', type: 'bigint')]
    protected $refId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="ref_entity_name")
     */
    #[ORM\Column(name: 'ref_entity_name', type: 'string')]
    protected $refEntityName;

    /**
     * The nodeVersion this nodeVersion originated from
     *
     * @var NodeVersion
     *
     * @ORM\ManyToOne(targetEntity="NodeVersion")
     * @ORM\JoinColumn(name="origin_id", referencedColumnName="id")
     */
    #[ORM\ManyToOne(targetEntity: NodeVersion::class)]
    #[ORM\JoinColumn(name: 'origin_id', referencedColumnName: 'id')]
    protected $origin;

    public function __construct()
    {
        $this->setCreated(new DateTime());
        $this->setUpdated(new DateTime());
    }

    /**
     * Set nodeTranslation
     *
     * @return NodeVersion
     */
    public function setNodeTranslation(NodeTranslation $nodeTranslation)
    {
        $this->nodeTranslation = $nodeTranslation;

        return $this;
    }

    /**
     * Get NodeTranslation
     *
     * @return NodeTranslation
     */
    public function getNodeTranslation()
    {
        return $this->nodeTranslation;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function isDraft()
    {
        return self::DRAFT_VERSION === $this->type;
    }

    public function isPublic()
    {
        return self::PUBLIC_VERSION === $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return NodeVersion
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set owner
     *
     * @param string $owner
     *
     * @return NodeVersion
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
     * Set created
     *
     * @return NodeVersion
     */
    public function setCreated(DateTime $created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @return NodeVersion
     */
    public function setUpdated(DateTime $updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Get refId
     *
     * @return int
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set refId
     *
     * @param int $refId
     *
     * @return NodeVersion
     */
    protected function setRefId($refId)
    {
        $this->refId = $refId;

        return $this;
    }

    /**
     * Set reference entity name
     *
     * @param string $refEntityName
     *
     * @return NodeVersion
     */
    protected function setRefEntityName($refEntityName)
    {
        $this->refEntityName = $refEntityName;

        return $this;
    }

    /**
     * Get reference entity name
     *
     * @return string
     */
    public function getRefEntityName()
    {
        return $this->refEntityName;
    }

    public function getDefaultAdminType()
    {
        return null;
    }

    /**
     * @return NodeVersion
     */
    public function setRef(HasNodeInterface $entity)
    {
        $this->setRefId($entity->getId());
        $this->setRefEntityName(ClassLookup::getClass($entity));

        return $this;
    }

    /**
     * @return HasNodeInterface
     */
    public function getRef(EntityManagerInterface $em)
    {
        return $em->getRepository($this->getRefEntityName())->find($this->getRefId());
    }

    /**
     * @param NodeVersion $origin
     *
     * @return NodeVersion
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return NodeVersion
     */
    public function getOrigin()
    {
        return $this->origin;
    }
}
