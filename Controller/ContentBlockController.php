<?php
namespace Glavweb\ContentBlockBundle\Controller;

use Glavweb\ContentBlockBundle\Entity\ContentBlock;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContentBlockController
 * @package GlavwebContentBlockBundle\Controller
 */
class ContentBlockController extends Controller
{
    /**
     * @Route("/content-block/save", name="content_block_save", requirements={"_method": "POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function actionSave(Request $request)
    {
        $em         = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('GlavwebContentBlockBundle:ContentBlock');
        $blocks     = $request->request->get('blocks', array());

        $needFlush = false;
        foreach ($blocks as $name => $body) {
            $contentBlock = $name ? $repository->findOneByName($name) : null;
            if (!$contentBlock) {
                continue;
            }

            $contentBlock->setBody($body);
            $needFlush = true;
        }

        if ($needFlush) {
            $em->flush();
        }

        $result = true;

        return new JsonResponse(array(
            'result' => $result
        ));
    }

    /**
     * @Route("/api/content-block/{name}", name="content_block_create", requirements={"_method": "POST"})
     *
     * @param string $name
     * @return JsonResponse
     */
    public function createAction($name)
    {
        $em           = $this->getDoctrine()->getManager();
        $repository   = $em->getRepository('GlavwebContentBlockBundle:ContentBlock');
        $contentBlock = $repository->findByName($name);

        if(empty($contentBlock)) {
            $contentBlock = new ContentBlock();
            $contentBlock->setName($name);
            $contentBlock->setBody($name);
            $em->persist($contentBlock);
            $em->flush();
            $status = 201;
        } else {
            $status = 400;
        }

        return new JsonResponse(array(
            'status' =>  $status == 201 ? true : false
        ), $status);
    }

    /**
     * @Route("/api/content-block/{name}", name="content_block_show", requirements={"_method": "GET"})
     *
     * @param string $name
     * @return JsonResponse
     */
    public function showAction($name)
    {
        $em           = $this->getDoctrine()->getManager();
        $repository   = $em->getRepository('GlavwebContentBlockBundle:ContentBlock');
        $contentBlock = $repository->findByName($name);

        if(!empty($contentBlock)) {
            $status = 200;
        } else {
            $status = 400;
        }
        return new JsonResponse(array(
            'status' =>  $status == 200 ? true : false
        ), $status);
    }

    /**
     * @Route("/api/content-block/{id}", name="content_block_edit", requirements={"_method": "PUT"})
     *
     * @param string $name
     * @return JsonResponse
     */
    public function editAction($name)
    {
        $em           = $this->getDoctrine()->getManager();
        $repository   = $em->getRepository('GlavwebContentBlockBundle:ContentBlock');
        $contentBlock = $repository->findByName($name);

        if(!empty($contentBlock)) {
            $status = 200;
        } else {
            $status = 400;
        }
        return new JsonResponse(array(
            'status' =>  $status == 200 ? true : false
        ), $status);
    }

    /**
     * @Route("/api/content-block/{name}", name="content_block_remove", requirements={"_method": "DELETE"})
     *
     * @param string $name
     * @return JsonResponse
     */
    public function removeAction($name)
    {
        $em           = $this->getDoctrine()->getManager();
        $repository   = $em->getRepository('GlavwebContentBlockBundle:ContentBlock');
        $contentBlock = $repository->findByName($name);

        if(!empty($contentBlock)) {
            $em->remove($contentBlock);
            $em->flush();
            $status = 204;
        } else {
            $status = 400;
        }
        return new JsonResponse(array(
            'status' =>  $status == 200 ? true : false
        ), $status);
    }
}