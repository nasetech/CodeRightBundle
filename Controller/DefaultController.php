<?php

namespace Nasetech\CodeRightBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use CodeRightBundle\Generator\CodeRightGenerator;

class DefaultController extends Controller
{
    /**
     * @Route("/a")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function indexAction()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ArrayDenormalizer());

        $serializer = new Serializer($normalizers, $encoders);
        $response = new JsonResponse();
        $jsonContent = $serializer->serialize(array(0=>"ROLE_SUPER_ADMIN"), 'json');
        $response->setData(array(
            'data' => $jsonContent
        ));
        return $response;
    }

    /**
     * @Route("/a1")
     */
    public function index1Action()
    {

        $response = new JsonResponse();
        $doctrine = $this->get("doctrine");
        $em = $doctrine->getManager();

        $className = "Sam\UserOrderBundle\Entity\AdvUserOrder";

        $metadata = $em->getClassMetadata($className);

        $nameMetadata = $metadata->fieldMappings['name'];

        echo $nameMetadata['type'];  //print "string"
        echo $nameMetadata['length']; // print "150"

        
        $response->setData(array(
            'data' => $metadata
        ));
        return $response;
    }

    /**
     * @Route("/a2")
     */
    public function index2Action()
    {

        $entity = 'AdvUserOrder';
        $bundle = 'CodeRightBundle';

        $bundle = $this->get('kernel')->getBundle($bundle);

        
        $response = new JsonResponse();
        
        $response->setData((array)$bundle);

        return $response;
    }

    /**
     * @Route("/a3")
     */
    public function index3Action()
    {
        
        $g = new CodeRightGenerator();
        
        $doctrine = $this->get("doctrine");
        $em = $doctrine->getManager();

        $className = "Sam\UserOrderBundle\Entity\AdvUserOrder";

        $metadata = $em->getClassMetadata($className);

        $nameMetadata = $metadata->fieldMappings['name'];

        $twig = $g->generateService($metadata);


        $response = new JSONResponse();
        
        $response->setData([
            'data' =>$twig
        ]);
        return $response;
    }

    /**
     * @Route("/a4")
     */
    public function index4Action()
    {
        
        $g = new CodeRightGenerator();
        
        $doctrine = $this->get("doctrine");
        $em = $doctrine->getManager();

        $className = "Sam\UserOrderBundle\Entity\AdvUserOrder";

        $metadata = $em->getClassMetadata($className);

        // $nameMetadata = $metadata->fieldMappings['name'];

        $twig = $g->generateController($metadata);


        $response = new JSONResponse();
        
        $response->setData([
            'data' =>$twig
        ]);
        return $response;
    }

     /**
     * @Route("/a5")
     */
    public function index5Action()
    {
        
        $g = new CodeRightGenerator();
        
        $doctrine = $this->get("doctrine");

        $em = $doctrine->getManager();

        $className = "Sam\UserOrderBundle\Entity\AdvUserOrder";

        $metadata = $em->getClassMetadata($className);

        // $nameMetadata = $metadata->fieldMappings['name'];

        $twig = $g->generateRepository($metadata);

        $response = new JSONResponse();
        
        $response->setData([
            'data' =>$twig
        ]);
        return $response;
    }

}
