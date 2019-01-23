<?php

namespace Template\Services;

use Soosyze\Components\Util\Util;

/* Chemin des vues par défauts. */
define('TPL_PATH', MODULES_CORE . 'Template' . DS . 'Views' . DS);

class TemplatingHtml extends \Soosyze\Components\Http\Response
{
    /**
     * @var TemplateHtml
     */
    protected $template;

    /**
     * @var \Soosyze\Config
     */
    protected $config;

    /**
     * @var \Soosyze\App
     */
    protected $core;

    /**
     * Chemin du thème.
     *
     * @var string
     */
    protected $theme_default = '';

    /**
     * Nom du theme utilisé par défaut.
     *
     * @var string
     */
    protected $theme_default_name = '';

    public function __construct($core, $config)
    {
        $this->core   = $core;
        $this->config = $config;
        $this->getTheme();
    }

    public function __toString()
    {
        $content    = $this->template->render();
        $this->body = new \Soosyze\Components\Http\Stream($content);

        return parent::__toString();
    }

    public function init()
    {
        $messages = $this->themeOverride('messages.php', TPL_PATH)
            ->addVars([
            'errors'   => [],
            'warnings' => [],
            'infos'    => [],
            'success'  => []
        ]);

        $page = $this->themeOverride('page.php', TPL_PATH)
            ->addVars([
                'title_main' => '',
                'logo'       => ''
            ])
            ->addVars($this->core->getSettings())
            ->addBlock('content')
            ->addBlock('messages', $messages)
            ->addBlock('main_menu')
            ->addBlock('second_menu');

        $this->template = $this->themeOverride('html.php', TPL_PATH)
                ->addBlock('page', $page)
                ->addVars([
                    'title'       => '',
                    'logo'        => '',
                    'favicon'     => '',
                    'description' => '',
                    'keyboard'    => '',
                    'styles'      => '',
                    'scripts'     => ''
                ])->addVars($this->core->getSettings());
    }

    public function getTheme($theme = 'theme')
    {
        $this->theme_default_name = $theme;
        foreach ($this->core->getSetting('themes_path') as $path) {
            $dir = $path . DS . $this->config->get('settings.' . $theme, '');
            if (is_dir($dir)) {
                $this->theme_default = $dir;

                break;
            }
        }
        $this->init();

        return $this;
    }

    public function isTheme($theme)
    {
        return $this->theme_default_name === $theme;
    }

    /**
     * Ajoute des variables à la template courante.
     *
     * @param array $vars
     *
     * @return $this
     */
    public function add(array $vars)
    {
        $this->template->addVars($vars);

        return $this;
    }

    /**
     * Ajoute des variables à la template courante ou à une sous template.
     *
     * @param string $parent
     * @param array  $vars
     *
     * @return $this
     */
    public function view($parent, array $vars)
    {
        $this->template->getBlockWithParent($parent)->addVars($vars);

        return $this;
    }

    /**
     * Ajoute un bloc à la template courante ou une sous template.
     * Ce bloc peut recevoir des variables fournit en dernier paramètre de la fonction.
     *
     * @param sring  $parent
     * @param string $tpl
     * @param string $tplPath
     * @param array  $vars
     *
     * @return $this
     */
    public function render($parent, $tpl, $tplPath, array $vars = null)
    {
        $template = $this->themeOverride($tpl, $tplPath);
        if ($vars) {
            $template->addVars($vars);
        }

        if (($block = strstr($parent, '.', true))) {
            $this->template->getBlock($block)
                ->addBlock(substr(strstr($parent, '.'), 1), $template);
        } else {
            $this->template->addBlock($parent, $template);
        }

        return $this;
    }

    public function addFilterVar($parent, $key, callable $function)
    {
        $this->template->getBlockWithParent($parent)
            ->addFilterVar($key, $function);

        return $this;
    }

    public function addFilterBlock($parent, $key, callable $function)
    {
        $this->template->getBlockWithParent($parent)
            ->addFilterBlock($key, $function);

        return $this;
    }

    public function addFilterOutput($parent, $key, callable $function)
    {
        $this->template->getBlockWithParent($parent)
            ->addFilterOutput($key, $function);

        return $this;
    }

    public function override($parent, array $templates)
    {
        $dir   = $this->theme_default;
        $block = $this->template->getBlockWithParent($parent)
            ->addNamesOverride($templates);

        if (is_dir($dir)) {
            foreach ($templates as $tpl) {
                if (is_file($dir . DS . $tpl)) {
                    $block->setName($tpl);

                    break;
                }
            }
        }

        return $this;
    }

    public function getBlock($key)
    {
        return $this->template->getBlockWithParent($key);
    }

    public function getBlocks()
    {
        return $this->template->getBlocks();
    }

    public function getVar($key)
    {
        return $this->template->getVar($key);
    }

    public function getVars()
    {
        return $this->template->getVars();
    }

    public function getThemes()
    {
        $folders = [];
        foreach ($this->core->getSetting('themes_path') as $path) {
            if (is_dir($path)) {
                $folders = array_merge($folders, Util::getFolder($path));
            }
        }

        return $folders;
    }

    protected function themeOverride($templates, $tplPath)
    {
        $dir = $this->theme_default;

        if (is_dir($dir)) {
            if (is_file($dir . DS . $templates)) {
                $tplPath = $dir . DS;
            }
        }

        return (new TemplateHtml($templates, $tplPath))
                ->addVars([
                    'base_path' => $this->core->getRequest()->getUri()->getBasePath()
                ])
                ->addVar('base_theme', $tplPath);
    }
}
