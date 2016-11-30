<?php

namespace Oro\Bundle\WebCatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\RedirectBundle\Entity\LocalizedSlugPrototypeAwareInterface;
use Oro\Bundle\RedirectBundle\Entity\LocalizedSlugPrototypeAwareTrait;
use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\WebCatalogBundle\Model\ExtendContentNode;
use Oro\Component\Tree\Entity\TreeTrait;
use Oro\Component\WebCatalog\Entity\ContentNodeInterface;

/**
 * @ORM\Entity(repositoryClass="Oro\Bundle\WebCatalogBundle\Entity\Repository\ContentNodeRepository")
 * @ORM\Table(name="oro_web_catalog_content_node")
 * @Gedmo\Tree(type="nested")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\AssociationOverrides({
 *      @ORM\AssociationOverride(
 *          name="slugPrototypes",
 *          joinTable=@ORM\JoinTable(
 *              name="oro_web_catalog_node_slug_prot",
 *              joinColumns={
 *                  @ORM\JoinColumn(name="node_id", referencedColumnName="id", onDelete="CASCADE")
 *              },
 *              inverseJoinColumns={
 *                  @ORM\JoinColumn(
 *                      name="localized_value_id",
 *                      referencedColumnName="id",
 *                      onDelete="CASCADE",
 *                      unique=true
 *                  )
 *              }
 *          )
 *      )
 * })
 * @Config(
 *      defaultValues={
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "activity"={
 *              "show_on_page"="\Oro\Bundle\ActivityBundle\EntityConfig\ActivityScope::UPDATE_PAGE"
 *          }
 *     }
 * )
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ContentNode extends ExtendContentNode implements
    ContentNodeInterface,
    DatesAwareInterface,
    LocalizedSlugPrototypeAwareInterface
{
    use TreeTrait;
    use DatesAwareTrait;
    use LocalizedSlugPrototypeAwareTrait;

    const FIELD_PARENT_NODE = 'parentNode';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var ContentNode
     *
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="ContentNode", inversedBy="childNodes")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $parentNode;

    /**
     * @var Collection|ContentNode[]
     *
     * @ORM\OneToMany(targetEntity="ContentNode", mappedBy="parentNode", cascade={"persist", "remove"})
     * @ORM\OrderBy({"left" = "ASC"})
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $childNodes;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="parent_scope_used", type="boolean", options={"default"=true})
     */
    protected $parentScopeUsed = true;

    /**
     * @var Collection|LocalizedFallbackValue[]
     *
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue",
     *      cascade={"ALL"},
     *      orphanRemoval=true
     * )
     * @ORM\JoinTable(
     *      name="oro_web_catalog_node_title",
     *      joinColumns={
     *          @ORM\JoinColumn(name="node_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="localized_value_id", referencedColumnName="id", onDelete="CASCADE", unique=true)
     *      }
     * )
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $titles;

    /**
     * @var Collection|Scope[]
     *
     * @ORM\ManyToMany(
     *      targetEntity="Oro\Bundle\ScopeBundle\Entity\Scope"
     * )
     * @ORM\JoinTable(name="oro_web_catalog_node_scope",
     *      joinColumns={
     *          @ORM\JoinColumn(name="node_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="scope_id", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     */
    protected $scopes;

    /**
     * @var Collection|ContentVariant[]
     *
     * @ORM\OneToMany(
     *     targetEntity="Oro\Bundle\WebCatalogBundle\Entity\ContentVariant",
     *     mappedBy="node",
     *     cascade={"ALL"},
     *     fetch="EAGER",
     *     orphanRemoval=true
     * )
     */
    protected $contentVariants;

    /**
     * @var string
     *
     * @ORM\Column(name="materialized_path", type="string", length=1024, nullable=true)
     */
    protected $materializedPath;

    /**
     * @var WebCatalog
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WebCatalogBundle\Entity\WebCatalog")
     * @ORM\JoinColumn(name="web_catalog_id", referencedColumnName="id",onDelete="CASCADE",nullable=false)
     */
    protected $webCatalog;

    /**
     * ContentNode Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->titles = new ArrayCollection();
        $this->childNodes = new ArrayCollection();
        $this->slugPrototypes = new ArrayCollection();
        $this->scopes = new ArrayCollection();
        $this->contentVariants = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getDefaultTitle();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return ContentNode
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return ContentNode
     */
    public function getParentNode()
    {
        return $this->parentNode;
    }

    /**
     * @param ContentNode|null $parentNode
     *
     * @return $this
     */
    public function setParentNode(ContentNode $parentNode = null)
    {
        $this->parentNode = $parentNode;

        return $this;
    }

    /**
     * @return Collection|ContentNode[]
     */
    public function getChildNodes()
    {
        return $this->childNodes;
    }

    /**
     * @param ContentNode $node
     *
     * @return $this
     */
    public function addChildNode(ContentNode $node)
    {
        if (!$this->childNodes->contains($node)) {
            $this->childNodes->add($node);
            $node->setParentNode($this);
        }

        return $this;
    }

    /**
     * @param ContentNode $node
     *
     * @return $this
     */
    public function removeChildNode(ContentNode $node)
    {
        if ($this->childNodes->contains($node)) {
            $this->childNodes->removeElement($node);
        }

        return $this;
    }

    /**
     * @return Collection|LocalizedFallbackValue[]
     */
    public function getTitles()
    {
        return $this->titles;
    }

    /**
     * @param LocalizedFallbackValue $title
     *
     * @return $this
     */
    public function addTitle(LocalizedFallbackValue $title)
    {
        if (!$this->titles->contains($title)) {
            $this->titles->add($title);
        }

        return $this;
    }

    /**
     * @param LocalizedFallbackValue $title
     *
     * @return $this
     */
    public function removeTitle(LocalizedFallbackValue $title)
    {
        if ($this->titles->contains($title)) {
            $this->titles->removeElement($title);
        }

        return $this;
    }

    /**
     * @return Collection|Scope[]
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param Scope $scope
     * @return $this
     */
    public function addScope(Scope $scope)
    {
        if (!$this->scopes->contains($scope)) {
            $this->scopes->add($scope);
        }

        return $this;
    }

    /**
     * @param Scope $scope
     * @return $this
     */
    public function removeScope(Scope $scope)
    {
        if ($this->scopes->contains($scope)) {
            $this->scopes->removeElement($scope);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function resetScopes()
    {
        $this->scopes->clear();

        return $this;
    }

    /**
     * @return Collection|ContentVariant[]
     */
    public function getContentVariants()
    {
        return $this->contentVariants;
    }

    /**
     * @param ContentVariant $page
     *
     * @return $this
     */
    public function addContentVariant(ContentVariant $page)
    {
        if (!$this->contentVariants->contains($page)) {
            $page->setNode($this);
            $this->contentVariants->add($page);
        }

        return $this;
    }

    /**
     * @param ContentVariant $page
     *
     * @return $this
     */
    public function removeContentVariant(ContentVariant $page)
    {
        if ($this->contentVariants->contains($page)) {
            $this->contentVariants->removeElement($page);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getMaterializedPath()
    {
        return $this->materializedPath;
    }

    /**
     * @param string $materializedPath
     *
     * @return $this
     */
    public function setMaterializedPath($materializedPath)
    {
        $this->materializedPath = $materializedPath;

        return $this;
    }

    /**
     * @return WebCatalog
     */
    public function getWebCatalog()
    {
        return $this->webCatalog;
    }

    /**
     * @param WebCatalog $webCatalog
     * @return $this
     */
    public function setWebCatalog(WebCatalog $webCatalog)
    {
        $this->webCatalog = $webCatalog;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isParentScopeUsed()
    {
        return $this->parentScopeUsed;
    }

    /**
     * @param boolean $parentScopeUsed
     * @return $this
     */
    public function setParentScopeUsed($parentScopeUsed)
    {
        $this->parentScopeUsed = $parentScopeUsed;

        return $this;
    }
}