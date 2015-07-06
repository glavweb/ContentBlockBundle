<?php
namespace Glavweb\ContentBlockBundle\Controller;

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
}