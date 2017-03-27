<?php

namespace Glavweb\ContentBlockBundle\Controller;

use Glavweb\ActionBundle\Action\Exception;
use Glavweb\ContentBlockBundle\Entity\ContentBlock;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\User;

/**
 * Class ContentBlockController
 *
 * @package GlavwebContentBlockBundle\Controller
 */
class ContentBlockController extends Controller
{
    /**
     * @Route("/content-block/save", name="content_block_save", methods={"POST"})
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function actionSave(Request $request)
    {
        $user = $this->getAdminUser();
        if (!$user) {
            return new JsonResponse(array(
                'message' => 'Нужна авторизация на сервере.'
            ), 400);
        }

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
     * @Route("/api/content-block/{name}", name="content_block_create", methods={"POST"})
     *
     * @param string $name
     * @return JsonResponse
     */
    public function createAction($name)
    {
        $user = $this->getAdminUser();
        if (!$user) {
            return new JsonResponse(array(
                'message' => 'Нужна авторизация на сервере.'
            ), 400);
        }

        $em           = $this->getDoctrine()->getManager();
        $repository   = $em->getRepository('GlavwebContentBlockBundle:ContentBlock');
        $contentBlock = $repository->findOneByName($name);

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

        return new JsonResponse(array(), $status);
    }

    /**
     * @Route("/api/content-block/{name}", name="content_block_show", methods={"GET"})
     *
     * @param string $name
     * @return JsonResponse
     */
    public function showAction($name)
    {
        $editable = false ;
        $user = $this->getAdminUser();
        if ($user) {
            $editable = true;
        }

        $em           = $this->getDoctrine()->getManager();
        $repository   = $em->getRepository('GlavwebContentBlockBundle:ContentBlock');
        $contentBlock = $repository->findOneByName($name);

        if(empty($contentBlock)) {
            $contentBlock = new ContentBlock();
            $contentBlock->setName($name);
            $contentBlock->setBody($name);
            $em->persist($contentBlock);
            $em->flush();
        }

        return new JsonResponse(array(
            'editable' => $editable,
            'contentBlock' => $contentBlock->getBody()
        ));
    }

    /**
     * @Route("/api/content-block/{name}", name="content_block_edit", methods={"PUT"})
     *
     * @param string $name
     * @param Request $request
     * @return JsonResponse
     */
    public function editAction($name, Request $request)
    {
        $user = $this->getAdminUser();
        if (!$user) {
            return new JsonResponse(array(
                'message' => 'Нужна авторизация на сервере.'
            ), 400);
        }

        $em           = $this->getDoctrine()->getManager();
        $repository   = $em->getRepository('GlavwebContentBlockBundle:ContentBlock');
        $contentBlock = $repository->findOneByName($name);
        $content      = json_decode($request->getContent());

        if(!empty($contentBlock)) {
            $contentBlock->setBody($content->body);
            $em->flush();
            $status = 200;
        } else {
            $status = 400;
        }
        return new JsonResponse(array(), $status);
    }

    /**
     * @Route("/api/content-block/{name}", name="content_block_remove", methods={"DELETE"})
     *
     * @param string $name
     * @return JsonResponse
     */
    public function removeAction($name)
    {
        $user = $this->getAdminUser();
        if (!$user) {
            return new JsonResponse(array(
                'message' => 'Нужна авторизация на сервере.'
            ), 400);
        }

        $em           = $this->getDoctrine()->getManager();
        $repository   = $em->getRepository('GlavwebContentBlockBundle:ContentBlock');
        $contentBlock = $repository->findOneByName($name);

        if(!empty($contentBlock)) {
            $em->remove($contentBlock);
            $em->flush();
            $status = 204;
        } else {
            $status = 400;
        }
        return new JsonResponse(array(), $status);
    }

    /**
     * @return User|null
     */
    private function getAdminUser()
    {
        $user = $this->getUser();
        if ($user && $user->hasRole('ROLE_SUPER_ADMIN')) {
            return $user;
        }

        return null;
    }
}