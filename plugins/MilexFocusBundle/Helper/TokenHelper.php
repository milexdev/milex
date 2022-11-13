<?php

namespace MilexPlugin\MilexFocusBundle\Helper;

use Milex\CoreBundle\Security\Permissions\CorePermissions;
use MilexPlugin\MilexFocusBundle\Model\FocusModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class TokenHelper
{
    private $regex = '{focus=(.*?)}';

    /**
     * @var FocusModel
     */
    protected $model;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var CorePermissions
     */
    protected $security;

    public function __construct(FocusModel $model, RouterInterface $router, CorePermissions $security)
    {
        $this->router   = $router;
        $this->model    = $model;
        $this->security = $security;
    }

    /**
     * @param $content
     *
     * @return array
     */
    public function findFocusTokens($content)
    {
        $regex = '/'.$this->regex.'/i';

        preg_match_all($regex, $content, $matches);

        $tokens = [];

        if (count($matches[0])) {
            foreach ($matches[1] as $id) {
                $token = '{focus='.$id.'}';
                $focus = $this->model->getEntity($id);
                if (null !== $focus
                    && (
                        $focus->isPublished()
                        || $this->security->hasEntityAccess(
                            'focus:items:viewown',
                            'focus:items:viewother',
                            $focus->getCreatedBy()
                        )
                    )
                ) {
                    $script = '<script src="'.
                        $this->router->generate(
                        'milex_focus_generate',
                        ['id' => $id],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ).
                    '" type="text/javascript" charset="utf-8" async="async"></script>';
                    $tokens[$token] = $script;
                } else {
                    $tokens[$token] = '';
                }
            }
        }

        return $tokens;
    }
}
