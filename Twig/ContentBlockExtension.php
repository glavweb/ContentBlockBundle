<?php

namespace Glavweb\ContentBlockBundle\Twig;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Glavweb\ContentBlockBundle\Entity\ContentBlock;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class ContentBlockExtension
 * @package GlavwebContentBlockBundle\Twig
 */
class ContentBlockExtension extends \Twig_Extension
{
    /**
     * @var null|\Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @var SecurityContext
     */
    private $securityContext;

    /**
     * @param RequestStack $requestStack
     * @param Registry $doctrine
     * @param SecurityContext $securityContext
     */
    public function __construct(RequestStack $requestStack, Registry $doctrine, SecurityContext $securityContext)
    {
        $this->request         = $requestStack->getCurrentRequest();
        $this->doctrine        = $doctrine;
        $this->securityContext = $securityContext;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'glavweb_content_block_extension';
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('content_block', array($this, 'contentBlock'), array('is_safe' => array('html')))
        );
    }

    /**
     * @param string $blockName
     * @param array  $options
     * @param string $default
     * @return string
     */
    public function contentBlock($blockName, $options = array(), $default = null)
    {
        $em = $this->doctrine->getManager();
        $repository = $em->getRepository('GlavwebContentBlockBundle:ContentBlock');
        $contentBlock = $repository->findOneByName($blockName);

        $tag = isset($options['tag']) ? $options['tag'] : 'div';
        $attr = isset($options['attr']) ? $options['attr'] : array();

        if (isset($options['class'])) {
            $attr['class'] = $options['class'];
        }

        if (isset($options['href'])) {
            $attr['href'] = $options['href'];
        }

        if (!$contentBlock) {
            $contentBlock = new ContentBlock();
            $contentBlock->setName($blockName);
            $contentBlock->setBody(($default ? $default : $blockName));

            $em->persist($contentBlock);
            $em->flush();
        }

        $contentEditable = '';
        $dataBlockName   = '';

        $isEditable =
            $this->request && $this->request->get('contenteditable') &&
            $this->securityContext->isGranted('ROLE_ADMIN')
        ;

        if ($isEditable) {
            $contentEditable = ' contenteditable="true"';
            $dataBlockName   = ' data-block-name="' . $blockName . '"';
            $attr['class'] = isset($attr['class']) ? $attr['class'] . ' js-content-block' : 'js-content-block';
        }

        $attrParts = array();
        foreach ($attr as $attrName => $value) {
            $attrParts[] = sprintf('%s="%s"', $attrName, $value);
        }

        return '<' . $tag . ' ' . implode(' ', $attrParts) . ' ' . $contentEditable . $dataBlockName . '>' . $contentBlock->getBody()  . '</' . $tag . '>';
    }
}