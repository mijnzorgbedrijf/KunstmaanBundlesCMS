<?php

namespace Kunstmaan\NodeBundle\Helper\Services;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Kunstmaan\AdminBundle\Repository\UserRepository;
use Kunstmaan\NodeBundle\Entity\HasNodeInterface;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Repository\NodeRepository;
use Kunstmaan\PagePartBundle\Helper\HasPagePartsInterface;
use Kunstmaan\SeoBundle\Entity\Seo;
use Kunstmaan\SeoBundle\Repository\SeoRepository;

/**
 * Service to create new pages.
 */
class PageCreatorService
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var ACLPermissionCreatorService */
    protected $aclPermissionCreatorService;

    /** @var string */
    protected $userEntityClass;

    public function __construct(EntityManagerInterface $em, ACLPermissionCreatorService $aclPermissionCreatorService, string $userEntityClass)
    {
        $this->entityManager = $em;
        $this->aclPermissionCreatorService = $aclPermissionCreatorService;
        $this->userEntityClass = $userEntityClass;
    }

    /**
     * @param HasNodeInterface $pageTypeInstance the page
     * @param array            $translations     Containing arrays. Sample:
     *                                           [
     *                                           [   "language" => "nl",
     *                                           "callback" => function($page, $translation) {
     *                                           $translation->setTitle('NL titel');
     *                                           }
     *                                           ],
     *                                           [   "language" => "fr",
     *                                           "callback" => function($page, $translation) {
     *                                           $translation->setTitle('FR titel');
     *                                           }
     *                                           ]
     *                                           ]
     *                                           Perhaps it's cleaner when you create one array and append another array for each language.
     * @param array            $options          Possible options:
     *                                           parent: type node, nodetransation or page.
     *                                           page_internal_name: string. name the page will have in the database.
     *                                           set_online: bool. if true the page will be set as online after creation.
     *                                           hidden_from_nav: bool. if true the page will not be show in the navigation
     *                                           creator: username
     *
     * Automatically calls the ACL + sets the slugs to empty when the page is an Abstract node.
     *
     * @return Node the new node for the page
     *
     * @throws \InvalidArgumentException
     */
    public function createPage(HasNodeInterface $pageTypeInstance, array $translations, array $options = [])
    {
        if (\is_null($options)) {
            $options = [];
        }

        if (\is_null($translations) || (\count($translations) == 0)) {
            throw new \InvalidArgumentException('There has to be at least 1 translation in the translations array');
        }

        $em = $this->entityManager;

        /** @var NodeRepository $nodeRepo */
        $nodeRepo = $em->getRepository(Node::class);
        /** @var UserRepository $userRepo */
        $userRepo = $em->getRepository($this->userEntityClass);
        /* @var SeoRepository $seoRepo */
        try {
            $seoRepo = $em->getRepository(Seo::class);
        } catch (ORMException $e) {
            $seoRepo = null;
        }

        $pagecreator = \array_key_exists('creator', $options) ? $options['creator'] : 'pagecreator';

        if ($pagecreator instanceof $this->userEntityClass) {
            $creator = $pagecreator;
        } else {
            $creator = $userRepo->findOneBy(['username' => $pagecreator]);
        }

        $parent = isset($options['parent']) ? $options['parent'] : null;

        $pageInternalName = isset($options['page_internal_name']) ? $options['page_internal_name'] : null;

        $setOnline = isset($options['set_online']) ? $options['set_online'] : false;

        // We need to get the language of the first translation so we can create the rootnode.
        // This will also create a translationnode for that language attached to the rootnode.
        $first = true;
        $rootNode = null;

        /* @var \Kunstmaan\NodeBundle\Repository\NodeTranslationRepository $nodeTranslationRepo*/
        $nodeTranslationRepo = $em->getRepository(NodeTranslation::class);

        foreach ($translations as $translation) {
            $language = $translation['language'];
            $callback = $translation['callback'];

            $translationNode = null;
            if ($first) {
                $first = false;

                $em->persist($pageTypeInstance);
                $em->flush();

                // Fetch the translation instead of creating it.
                // This returns the rootnode.
                $rootNode = $nodeRepo->createNodeFor($pageTypeInstance, $language, $creator, $pageInternalName);

                if (\array_key_exists('hidden_from_nav', $options)) {
                    $rootNode->setHiddenFromNav($options['hidden_from_nav']);
                }

                if (!\is_null($parent)) {
                    if ($parent instanceof HasPagePartsInterface) {
                        $parent = $nodeRepo->getNodeFor($parent);
                    }
                    $rootNode->setParent($parent);
                }

                $em->persist($rootNode);
                $em->flush();

                $translationNode = $rootNode->getNodeTranslation($language, true);
            } else {
                // Clone the $pageTypeInstance.
                $pageTypeInstance = clone $pageTypeInstance;

                $em->persist($pageTypeInstance);
                $em->flush();

                // Create the translationnode.
                $translationNode = $nodeTranslationRepo->createNodeTranslationFor($pageTypeInstance, $language, $rootNode, $creator);
            }

            // Make SEO.
            $seo = null;

            if (!\is_null($seoRepo)) {
                $seo = $seoRepo->findOrCreateFor($pageTypeInstance);
            }

            $callback($pageTypeInstance, $translationNode, $seo);

            // Overwrite the page title with the translated title
            $pageTypeInstance->setTitle($translationNode->getTitle());
            $em->persist($pageTypeInstance);
            $em->persist($translationNode);
            $em->flush();

            $translationNode->setOnline($setOnline);

            if (!\is_null($seo)) {
                $em->persist($seo);
                $em->flush();
            }

            $em->persist($translationNode);
            $em->flush();
        }

        // ACL
        $this->aclPermissionCreatorService->createPermission($rootNode);

        return $rootNode;
    }
}
