<?php
namespace TigerKit;

use Slim\View;
use Thru\UUID;
use Thru\Translation\Translation;

class TigerView extends View
{
    private $_js = array();
    private $_css = array();
    private $_site_title = 'Untitled site';
    private $_page_title;

    public function render($template, $data = null)
    {
        if (!is_array($data)) {
            $data = (array)$data;
        }
        if (isset($data['noWrapper'])) {
            return parent::render($template, $data);
        }else {
            $data['view'] = $this;
            $data['template'] = $template;
            return parent::render("decorator/decorator.phtml", $data);
        }
    }

    public function getCSS()
    {
        return $this->_css;
    }

    public function addCSS($css)
    {
        $filename = basename($css);
        $data = file_get_contents(TigerApp::AppRoot() . "/" . $css);
        $id = md5($filename);
        $publicLocation = "cache/{$id}.css";
        $publicLocationOnDisk = TigerApp::AppRoot() . "/public/" . $publicLocation;
        if (!file_exists(dirname($publicLocationOnDisk))) {
            mkdir(dirname($publicLocationOnDisk), 0777, true);
        }
        file_put_contents($publicLocationOnDisk, $data);
        chmod($publicLocationOnDisk, 0664);
        $this->_css[] = $publicLocation;
        return $this;
    }

    public function getJS()
    {
        return $this->_js;
    }

    public function addJS($js)
    {
        $filename = basename($js);
        $data = file_get_contents(TigerApp::AppRoot() . "/" . $js);
        $id = md5($filename);
        $publicLocation = "cache/{$id}.js";
        $publicLocationOnDisk = TigerApp::AppRoot() . "/public/" . $publicLocation;
        if (!file_exists(dirname($publicLocationOnDisk))) {
            mkdir(dirname($publicLocationOnDisk), 0777, true);
        }
        file_put_contents($publicLocationOnDisk, $data);
        chmod($publicLocationOnDisk, 0664);
        $this->_js[] = $publicLocation;
        return $this;
    }

    public function url($url)
    {
        // Do not process absolute URLs.
        if (strpos($url, '://') !== false) {
            return $url;
        }

        // Relative-to-root URLs...
        if (substr($url, 0, 1) == '/' && substr($url, 1, 1) != '/') {
            return $url;
        }

        // Remove excess slashes to the left of URL.
        for ($i = 0; $i <= 3; $i++) {
            $url = ltrim($url, "/");
        }

        $url = ltrim($url);
        return TigerApp::WebRoot() . $url;
    }

    public function link($url, $text, $options = null)
    {
        $url = $this->url($url);
        if (isset($options['classes'])) {
            $classes = "class=\"" . implode(" ", $options['classes']) . "\"";
        }
        return "<a " . (isset($classes) ? $classes . " " : null) . "href=\"{$url}\">{$text}</a>";
    }

    public function l($url, $text, $options = null)
    {
        return $this->link($url, $text, $options);
    }

    public function getSiteTitle($decorate = true)
    {
        if ($this->_page_title && $decorate) {
            return "{$this->_site_title} - {$this->_page_title}";
        }else {
            return "{$this->_site_title}";
        }
    }

    public function setSiteTitle($title)
    {
        $this->_site_title = $title;
        return $this;
    }

    public function setPageTitle($title)
    {
        $this->_page_title = $title;
        return $this;
    }

    public function translate($string, $replacements = array())
    {
        return Translation::getInstance()->translate($string, $replacements);
    }

    public function t($string, $replacements = array())
    {
        return $this->translate($string, $replacements);
    }
}
