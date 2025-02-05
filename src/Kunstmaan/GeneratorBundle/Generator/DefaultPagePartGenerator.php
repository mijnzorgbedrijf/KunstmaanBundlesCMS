<?php

namespace Kunstmaan\GeneratorBundle\Generator;

use Doctrine\Persistence\ManagerRegistry;
use Kunstmaan\GeneratorBundle\Helper\CommandAssistant;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Yaml;

/**
 * Generates all classes/files for a new pagepart
 */
class DefaultPagePartGenerator extends KunstmaanGenerator
{
    /**
     * @var BundleInterface
     */
    private $bundle;

    /**
     * @var string
     */
    private $entity;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var array
     */
    private $sections;
    /** @var DoctrineHelper */
    private $doctrineHelper;

    public function __construct(Filesystem $filesystem, ManagerRegistry $registry, $skeletonDir, CommandAssistant $assistant, ContainerInterface $container, DoctrineHelper $doctrineHelper)
    {
        parent::__construct($filesystem, $registry, $skeletonDir, $assistant, $container);
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Generate the pagepart.
     *
     * @param BundleInterface $bundle    The bundle
     * @param string          $entity    The entity name
     * @param string          $prefix    The database prefix
     * @param array           $sections  The page sections
     * @param bool            $behatTest If we need to generate a behat test for this pagepart
     *
     * @throws \RuntimeException
     */
    public function generate(BundleInterface $bundle, $entity, $prefix, array $sections, $behatTest)
    {
        $this->bundle = $bundle;
        $this->entity = $entity;
        $this->prefix = $prefix;
        $this->sections = $sections;

        $this->generatePagePartEntity();
        if ($entity != 'AbstractPagePart') {
            $this->generateFormType();
            $this->generateResourceTemplate();
            $this->generateSectionConfig();
            if ($behatTest) {
                $this->generateBehatTest();
            }
        }
    }

    /**
     * Generate the pagepart entity.
     */
    private function generatePagePartEntity()
    {
        $params = [
            'bundle' => $this->bundle->getName(),
            'namespace' => $this->bundle->getNamespace(),
            'pagepart' => $this->entity,
            'pagepartname' => str_replace('PagePart', '', $this->entity),
            'adminType' => '\\' . $this->bundle->getNamespace(
                ) . '\\Form\\PageParts\\' . $this->entity . 'AdminType',
            'underscoreName' => strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $this->entity)),
            'prefix' => $this->prefix,
            'canUseAttributes' => version_compare(\PHP_VERSION, '8alpha', '>=') && Kernel::VERSION_ID >= 50200,
            'canUseEntityAttributes' => $this->doctrineHelper->doesClassUsesAttributes('App\\Entity\\Unkown'.uniqid()),
        ];

        $this->renderSingleFile(
            $this->skeletonDir . '/Entity/PageParts/' . $this->entity . '/',
            $this->bundle->getPath() . '/Entity/PageParts/',
            $this->entity . '.php',
            $params,
            true
        );

        $this->assistant->writeLine('Generating ' . $this->entity . ' Entity:       <info>OK</info>');
    }

    /**
     * Generate the admin form type entity.
     */
    private function generateFormType()
    {
        $params = [
            'bundle' => $this->bundle->getName(),
            'namespace' => $this->bundle->getNamespace(),
            'pagepart' => $this->entity,
            'pagepartname' => str_replace('PagePart', '', $this->entity),
            'adminType' => '\\' . $this->bundle->getNamespace() . '\\Form\\PageParts\\' . $this->entity . 'AdminType',
        ];

        $this->renderSingleFile(
            $this->skeletonDir . '/Form/PageParts/' . $this->entity . '/',
            $this->bundle->getPath() . '/Form/PageParts/',
            $this->entity . 'AdminType.php',
            $params,
            true
        );

        $this->assistant->writeLine('Generating ' . $this->entity . ' FormType:     <info>OK</info>');
    }

    /**
     * Generate the twig template.
     */
    private function generateResourceTemplate()
    {
        $params = [
            'pagepart' => strtolower(
                    preg_replace('/([a-z])([A-Z])/', '$1-$2', str_ireplace('PagePart', '', $this->entity))
                ) . '-pp',
        ];

        $this->renderSingleFile(
            $this->skeletonDir . '/Resources/views/PageParts/' . $this->entity . '/',
            $this->getTemplateDir($this->bundle) . '/PageParts/' . $this->entity . '/',
            'view.html.twig',
            $params,
            true
        );

        $this->renderSingleFile(
            $this->skeletonDir . '/Resources/views/PageParts/' . $this->entity . '/',
            $this->getTemplateDir($this->bundle) . '/PageParts/' . $this->entity . '/',
            'admin-view.html.twig',
            $params,
            true
        );

        $this->assistant->writeLine('Generating ' . $this->entity . ' template:     <info>OK</info>');
    }

    /**
     * Update the page section config files
     */
    private function generateSectionConfig()
    {
        if (count($this->sections) > 0) {
            $dir = $this->container->getParameter('kernel.project_dir') . '/config/kunstmaancms/pageparts/';
            foreach ($this->sections as $section) {
                $data = $originalData = Yaml::parse(file_get_contents($dir . $section));
                if (array_key_exists('kunstmaan_page_part', $data)) {
                    $data['types'] = $originalData['kunstmaan_page_part']['pageparts'][substr($section, 0, -4)]['types'];
                }

                if (!array_key_exists('types', $data)) {
                    $data['types'] = [];
                }
                $class = $this->bundle->getNamespace() . '\\Entity\\PageParts\\' . $this->entity;
                $found = false;
                foreach ($data['types'] as $type) {
                    if ($type['class'] == $class) {
                        $found = true;
                    }
                }

                if (!$found) {
                    $data['types'][] = [
                        'name' => str_replace('PagePart', '', $this->entity),
                        'class' => $class,
                    ];
                }

                if (array_key_exists('kunstmaan_page_part', $originalData)) {
                    $originalData['kunstmaan_page_part']['pageparts'][substr($section, 0, -4)]['types'] = $data['types'];
                    $data = $originalData;
                }

                $ymlData = Yaml::dump($data, 5);
                file_put_contents($dir . $section, $ymlData);
            }

            $this->assistant->writeLine('Updating ' . $this->entity . ' section config: <info>OK</info>');
        }
    }

    /**
     * Generate the admin form type entity.
     */
    private function generateBehatTest()
    {
        // TODO

        $this->assistant->writeLine('Generating behat test : <info>OK</info>');
    }
}
