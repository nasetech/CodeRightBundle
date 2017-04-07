<?php

namespace Nasetech\CodeRightBundle\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Yaml\Yaml;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations;

use Doctrine\Common\Annotations\AnnotationReader;

use CodeRightBundle\Generator\CodeRightGenerator;

/**
 * Description of 
 * @author likai
 */
class GeneratorController extends FOSRestController
{
    public function getSerializeAction()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ArrayDenormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        $jsonContent = $serializer->serialize(array(0=>"ROLE_SUPER_ADMIN"), 'json');
        return [
            'data' => $jsonContent
        ];
    }

    /**
     * Example
     *    http://127.0.0.1:8000/metadata?className=CodeRightBundle\Entity\Example
     */
    public function getMetadataAction(Request $req)
    {
        $doctrine = $this->get("doctrine");

        $em = $doctrine->getManager();

        $className = $req->get('className') ? $req->get('className') : "CodeRightBundle\Entity\Example";

        $metadata = $em->getClassMetadata($className);

        //  use this to get the Mappings : $nameMetadata = $metadata->fieldMappings['id'];

        return $metadata->fieldMappings;
    }

    /**
     * Example
     *    http://127.0.0.1:8000/bundle?bundle=CodeRightBundle
     */
    public function getBundleAction(Request $req)
    {
        $bundle = $req->get('bundle') ? $req->get('bundle') : 'CodeRightBundle';

        $bundle = $this->get('kernel')->getBundle($bundle);
        
        list($ns, $name, $path) = [ $bundle->getNamespace(), $bundle->getName(), $bundle->getPath()];

        return ['bundle'=>$bundle, 'Namespace' => $ns, 'Name' => $name, 'Path' => $path];
    }

    /**
     * Example
     *    http://127.0.0.1:8000/service?bundle=CodeRightBundle&className=CodeRightBundle\Entity\Example
     */
    public function getServiceAction(Request $req)
    {
        $bundle = $req->get('bundle');
        $className = $req->get('className');
        
        if(empty($className) || empty($bundle)){
            return $this->printError('Bundle Name and Entity Class Name are required, try http://127.0.0.1:8000/service?bundle=CodeRightBundle&className=CodeRightBundle\Entity\Example');
        }

        $gen = new CodeRightGenerator();

        $doctrine = $this->get("doctrine");
        $em = $doctrine->getManager();

        $metadata = $em->getClassMetadata($className);
        $bundle = $this->get('kernel')->getBundle($bundle);

        $twig = $gen->generateService($bundle, $metadata);

        return $twig;
    }

    /**
     * Example
     *    http://127.0.0.1:8000/controller?bundle=CodeRightBundle&className=CodeRightBundle\Entity\Example
     */
    public function getControllerAction(Request $req)
    {
        $bundle = $req->get('bundle');
        $className = $req->get('className');
        
        if(empty($className) || empty($bundle)){
            return $this->printError('Bundle Name and Entity Class Name are required, try http://127.0.0.1:8000/service?controller=CodeRightBundle&className=CodeRightBundle\Entity\Example');
        }

        $gen = new CodeRightGenerator();

        $doctrine = $this->get("doctrine");
        $em = $doctrine->getManager();

        $metadata = $em->getClassMetadata($className);
        $bundle = $this->get('kernel')->getBundle($bundle);

        $twig = $gen->generateController($bundle, $metadata);

        return $twig;
    }

    /**
     * Example
     *    http://127.0.0.1:8000/repository?bundle=CodeRightBundle&className=CodeRightBundle\Entity\Example
     */
    public function getRepositoryAction(Request $req)
    {
        $bundle = $req->get('bundle');
        $className = $req->get('className');
        
        if(empty($className) || empty($bundle)){
            return $this->printError('Bundle Name and Entity Class Name are required, try http://127.0.0.1:8000/repository?bundle=CodeRightBundle&className=CodeRightBundle\Entity\Example');
        }

        $gen = new CodeRightGenerator();

        $doctrine = $this->get("doctrine");
        $em = $doctrine->getManager();

        $metadata = $em->getClassMetadata($className);
        $bundle = $this->get('kernel')->getBundle($bundle);

        $twig = $gen->generateRepository($bundle, $metadata);

        return $twig;
    }

    /**
     * Example
     *    http://127.0.0.1:8000/entityrepoistory?bundle=CodeRightBundle&entityName=Example
     */
    public function getEntityrepoistoryAction(Request $req)
    {
        $bundle = $req->get('bundle');
        $entityName = $req->get('entityName');
        
        if(empty($entityName) || empty($bundle)){
            return $this->printError('Bundle Name and Entity Class Name are required, try http://127.0.0.1:8000/repository?bundle=CodeRightBundle&className=CodeRightBundle\Entity\Example');
        }

        $gen = new CodeRightGenerator();

        $bundle = $this->get('kernel')->getBundle($bundle);

        $twig = $gen->setEntityRepository($bundle, $entityName);

        return $twig;
    }

    public function getAnnoAction(Request $req){
        $annotationReader = new AnnotationReader();
        $reflectionClass = new \ReflectionClass('CodeRightBundle\Entity\Example');
        $classAnnotations = $annotationReader->getClassAnnotations($reflectionClass);
        return $classAnnotations;
    }

    private function printError($error=null){
        return [
            'error' => !empty($error) ? $error : 'no data'
        ];
    }
}