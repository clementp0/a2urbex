<?php 

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\HashService;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private RequestStack $requestStack, 
        private $rootDirectory,
        private HashService $hashService
    ) {}

    public function getFunctions()
    {
        return [
            new TwigFunction('getStyleName', [$this, 'getStyleName']),
            new TwigFunction('getScriptName', [$this, 'getScriptName']),
            new TwigFunction('adminConvert', [$this, 'adminConvert']),
            new TwigFunction('getEnv', [$this, 'getEnvVariable']),
            new TwigFunction('hashLoc', [$this, 'getHashLoc']),
            new TwigFunction('hashFav', [$this, 'getHashFav']),
            new TwigFunction('hashUsr', [$this, 'getHashUsr']),
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

    public function getEnvVariable($name) {
        return $_ENV[$name];
    }

    public function getHashLoc($id) {
        return $this->hashService->encodeLoc($id);
    }
    public function getHashFav($id) {
        return $this->hashService->encodeFav($id);
    }
    public function getHashUsr($id) {
        return $this->hashService->encodeUsr($id);
    }
}