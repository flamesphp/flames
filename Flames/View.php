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
        $loader = new TemplateEngine\Loader\ArrayLoader([
            'index' => $this->html,
        ]);
        $twig = new TemplateEngine\Environment($loader);
        return $this->postRender($twig->render('index', $data), $data);
    }

    protected function renderFile(Arr|array $data = null)
    {
        $loader = new TemplateEngine\Loader\FilesystemLoader(ROOT_PATH . 'App/Client/View/');
        $twig = new TemplateEngine\Environment($loader, [
//            'cache' => (ROOT_PATH . '.cache/view-twig'),
        ]);

        return $this->postRender($twig->render($this->path, $data), $data);
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

    protected function postRender(string $html, array $data) : string
    {
        if (Environment::get('CLIENT_ENGINE_ENABLED') === false) {
            return $html;
        }

        $bodyCloseTag = '</body>';
        if (str_contains($html, $bodyCloseTag) === false) {
            throw new \Error('Missing body html tag.');
        }

        $scriptEngine = '<script src="/.flames.js" type="text/javascript"></script>';
        if (str_contains($html, $scriptEngine) === false) {
            $html = str_replace($bodyCloseTag, "\t" . $scriptEngine . "\n\t" . $bodyCloseTag, $html);
        }


        $html = str_replace($bodyCloseTag, "\t<flames hidden>" .
            base64_encode(serialize($data)) .
            "</flames>\n\t" . $bodyCloseTag, $html);
        return $html;
    }
}