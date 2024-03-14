<?php

namespace Flames;

use Flames\Collection\Arr;

class View
{
    protected string|null $path = null;
    protected string|null $html = null;

    public function render(Arr|array $data = null) : string|null
    {
        if ($data instanceof Arr) {
            $data = (array)$data;
        } elseif ($data === null) {
            $data = [];
        }

        if ($this->path !== null) {
            return $this->renderFile($data);
        }
        elseif ($this->html !== null) {
            return $this->renderHtml($data);
        }

        return null;
    }

    protected function renderHtml(Arr|array $data = null)
    {
        $loader = new \Flames\ThirdParty\Twig\Loader\ArrayLoader([
            'index' => $this->html,
        ]);
        $twig = new \Flames\ThirdParty\Twig\Environment($loader);
        return $twig->render('index', $data);
    }

    protected function renderFile(Arr|array $data = null)
    {
        $loader = new \Flames\ThirdParty\Twig\Loader\FilesystemLoader(ROOT_PATH . 'App/Client/View/');
        $twig = new \Flames\ThirdParty\Twig\Environment($loader, [
//            'cache' => (ROOT_PATH . '.cache/view-twig'),
        ]);

        return $twig->render($this->path, $data);
    }

    public function addView(string $path) : void
    {
        $fullPath = (ROOT_PATH . 'App/Client/View/' . $path);
        if (file_exists($fullPath) === false) {
            throw new \Exception('View path ' . $fullPath . ' does not exists.');
        }
        $this->path = $path;
    }

    public function addHtml(string $html) : void
    {
        $this->html = $html;
    }
}