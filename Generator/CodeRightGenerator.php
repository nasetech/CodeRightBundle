<?php

namespace Nasetech\CodeRightBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\Yaml\Yaml;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;

class CodeRightGenerator extends Generator
{
    /*
    * 产生Service文件
    * @param  BundleInterface $bundle
    * @param  ClassMetadataInfo $metadata
    */
    public function generateService($bundle, $metadata)
    {
        $nameMetadata = $metadata->fieldMappings;

        $metadata = (array) $metadata;

        $fullClassName   = $metadata['name'];
        $namespace       = $this->getClassNamespace($fullClassName);
        $entityClassName = $this->generateClassName($fullClassName);
        $repositoryName  = $this->generateEntityRepositoryName($entityClassName);

        $bundleName = $bundle->getName();
        $bundlePath = $bundle->getPath();
        $bundleNS = $bundle->getNamespace();        
        $path       = $bundlePath . '/Service/' . $entityClassName . 'Service.php';
        
        $twigData = [
            'bundle' => $bundleName, 
            'namespace' => $bundleNS, 
            'entityClassName' => $entityClassName, 
            'repositoryName' => $repositoryName
        ];
      
        $this->renderFile('Service.php.twig', $path, array_merge($metadata, $twigData));

        // Generate configuration
        $configFile = $bundlePath . '/Resources/config/services.yml';

        /**
         *   output as below
         *    userorderbundle_advuserorder:
         *       class: UserOrderBundle\Service\UserOrderService
         *       arguments: ["@doctrine.orm.entity_manager", 'UserOrderBundle\Entity\UserOrder', "@translator.default"]
         */
        $this->upsertServiceConfig($configFile, [
            strtolower(implode ('_' , [$bundleName, $entityClassName])) => [
                'class' =>  implode("\\", [$bundleNS, 'Service', $entityClassName.'Service']),
                'arguments' => [
                    "@doctrine.orm.entity_manager", 
                    implode("\\", [$bundleNS, 'Entity', $entityClassName]),
                    "@translator.default",
                    "@logger",
                    '@service_container'
                ]
            ]
        ]);

        return $this->render('Service.php.twig', array_merge($metadata, $twigData));
    }

    /**
    * 产生Rest Controller文件
    * @param  BundleInterface $bundle
    * @param  ClassMetadataInfo $metadata
    */
    public function generateController($bundle, $metadata)
    {
        $nameMetadata = $metadata->fieldMappings;

        $metadata = (array) $metadata;

        $fullClassName   = $metadata['name'];
        $namespace       = $this->getClassNamespace($fullClassName);
        $entityClassName = $this->generateClassName($fullClassName);
        $repositoryName  = $this->generateEntityRepositoryName($entityClassName);

        $bundleName = $bundle->getName();
        $bundlePath = $bundle->getPath();
        $bundleNS = $bundle->getNamespace();

        $path       = $bundlePath . '/Controller/' . $entityClassName . 'Controller.php';
        
        $twigData = [
            'bundle' => $bundleName, 
            'namespace' => $bundleNS, 
            'entityClassName' => $entityClassName, 
            'repositoryName' => $repositoryName
        ];
      
        $this->renderFile('Controller.php.twig', $path, array_merge($metadata, $twigData));

        $configFile = $bundlePath . '/Resources/config/routing.yml';

        /**
         *   output as below
         *    coderight:
         *        type: rest
         *        resource: CodeRightBundle\Controller\GeneratorController
         *
         */
        $this->upsertRoutingConfig($configFile, [
            strtolower(implode ('_' , [$bundleName, $entityClassName])) => [
                'type' => 'rest',
                'resource' => implode("\\", [$bundleNS, 'Controller', $entityClassName.'Controller'])
            ]
        ]);

        return $this->render('Controller.php.twig', array_merge($metadata, $twigData));
    }

    /**
    * 产生Repository文件
    * @param  BundleInterface $bundle
    * @param  ClassMetadataInfo $metadata
    */
    public function generateRepository($bundle, $metadata)
    {
        $nameMetadata = $metadata->fieldMappings;

        $metadata = (array) $metadata;

        $fullClassName   = $metadata['name'];
        $namespace       = $this->getClassNamespace($fullClassName);
        $entityClassName = $this->generateClassName($fullClassName);
        $repositoryName  = $this->generateEntityRepositoryName($entityClassName);

        $bundleName = $bundle->getName();
        $bundleNS = $bundle->getNamespace();
        $bundlePath = $bundle->getPath();
        $path = $bundlePath . '/Entity/' . $entityClassName . 'Repository.php';
        
        $twigData = [
            'bundle' => $bundleName, 
            'namespace' => $bundleNS, 
            'entityClassName' => $entityClassName, 
            'repositoryName' => $repositoryName
        ];
      
        $this->renderFile('Repository.php.twig', $path, array_merge($metadata, $twigData));

        return $this->render('Repository.php.twig', array_merge($metadata, $twigData));
    }

    /**
    * 修改Entity的定义文件，设置自定义的Repository
    * @param  BundleInterface $bundle
    */
    public function setEntityRepository($bundle, $entityName)
    {
        $bundleName = $bundle->getName();
        $bundleNS = $bundle->getNamespace();
        $bundlePath = $bundle->getPath();
        $path = $bundlePath . '/Resources/config/doctrine/' . $entityName . '.orm.yml';
        
        $content = Yaml::parse(file_get_contents($path));

        $config = ['repositoryClass' => $entityName.'Repository'];

        $content[$bundleNS.'\\Entity\\'.$entityName] = array_merge($content[$bundleNS.'\\Entity\\'.$entityName], $config);

        $yaml = Yaml::dump($content);

        file_put_contents( $path, $yaml);
      
        // $this->renderFile('Repository.php.twig', $path, array_merge($metadata, $twigData));

        return $yaml;
    }

    protected function getTwigEnvironment()
    {
        return new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__.'/../Resources/skeletons'), array(
            'debug' => true,
            'cache' => false,
            'strict_variables' => true,
            'autoescape' => false,
        ));
    }

    /**
     * Generates the namespace, if class do not have namespace, return empty string instead.
     *
     * @param string $fullClassName
     * 
     * @return string $namespace
     */
    private function getClassNamespace($fullClassName)
    {
        $namespace = substr($fullClassName, 0, strrpos($fullClassName, '\\'));

        return $namespace;
    }

    /**
     * Generates the class name
     * 
     * @param string $fullClassName
     * 
     * @return string
     */
    private function generateClassName($fullClassName)
    {
        $namespace = $this->getClassNamespace($fullClassName);

        $className = $fullClassName;

        if ($namespace) {
            $className = substr($fullClassName, strrpos($fullClassName, '\\') + 1, strlen($fullClassName));
        }

        return $className;
    }

    /**
     * Generates the namespace statement, if class do not have namespace, return empty string instead.
     * 
     * @param string $fullClassName The full repository class name.
     *
     * @return string $namespace
     */
    private function generateEntityRepositoryNamespace($fullClassName)
    {
        $namespace = $this->getClassNamespace($fullClassName);

        return $namespace ? 'namespace ' . $namespace . ';' : '';
    }

    /**
     * Check whether config is there, if yes delete it and re-create it.
     * 
     * @param string $path The full path of config yml file.
     *
     * @return associate array $config
     */
    private function upsertServiceConfig($path, $config){
        $content = Yaml::parse(file_get_contents($path));
        
        if(isset($content['services'])){
            $content['services'] = array_merge($content['services'], $config);
        }else{
            $content['services'] = $config;
        }
        
        $yaml = Yaml::dump($content);

        file_put_contents($path, $yaml);
    }

    /**
     * Check whether config is there, if yes delete it and re-create it.
     * 
     * @param string $path The full path of config yml file.
     *
     * @return associate array $config
     */
    private function upsertRoutingConfig($path, $config){
        $content = Yaml::parse(file_get_contents($path));
        
        $merged = array_merge($content, $config);

        $yaml = Yaml::dump($merged);

        file_put_contents( $path, $yaml);
    }

    /**
     * @param string $entityName
     * 
     * @return string $repositoryName
     */
    private function generateEntityRepositoryName($entityName)
    {

        $repositoryName = $entityName ? $entityName.'Repository' : 'Doctrine\ORM\EntityRepository';

        return $repositoryName;
    }
}
