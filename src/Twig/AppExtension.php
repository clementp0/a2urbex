<?php 

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;

class AppExtension extends AbstractExtension
{
    public function __construct(private RequestStack $requestStack, private $rootDirectory) {}

    public function getFunctions()
    {
        return [
            new TwigFunction('getStyleName', [$this, 'getStyleName']),
            new TwigFunction('getScriptName', [$this, 'getScriptName']),
        ];
    }

    public function getStyleName() {
        $name = $this->getCurrentControllerName();
        if(!$name || !file_exists($this->rootDirectory.'assets/scss/page/'.$name.'.scss')) return null;

        return $name.'-style';
    }

    public function getScriptName() {
        $name = $this->getCurrentControllerName();
        if(!$name || !file_exists($this->rootDirectory.'assets/js/page/'.$name.'.js')) return null;

        return $name.'-script';
    }

    private function getCurrentControllerName() {
        $request = $this->requestStack->getCurrentRequest();
        if(!$request) return null;

        $name = $request->attributes->get('_controller');
        $nameSplit = explode('\\', $name);
        if(count($nameSplit) < 1) return null;
        
        $action = $nameSplit[count($nameSplit) - 1];
        $actionSplit = explode('::', $action);
        if(count($actionSplit) < 1) return null;

        $controller =  strtolower($actionSplit[0]);
        return str_replace('controller', '', $controller);
    }
}