<?php

namespace Menu\Controller;

use Soosyze\Components\Form\FormBuilder;
use Soosyze\Components\Http\Redirect;
use Soosyze\Components\Validator\Validator;

define('VIEWS_MENU', MODULES_CORE . 'Menu' . DS . 'Views' . DS);
define('CONFIG_MENU', MODULES_CORE . 'Menu' . DS . 'Config' . DS);

class Menu extends \Soosyze\Controller
{
    public function __construct()
    {
        $this->pathServices = CONFIG_MENU . 'service.json';
        $this->pathRoutes   = CONFIG_MENU . 'routing.json';
    }

    public function show($name, $req)
    {
        $menu = self::menu()->getMenu($name)->fetch();

        if (!$menu) {
            return $this->get404($req);
        }

        $query = self::menu()
            ->getLinkPerMenu($name)
            ->orderBy('weight')
            ->fetchAll();

        $action = self::router()->getRoute('menu.show.check', [ ':item' => $name ]);
        $form   = (new FormBuilder([ 'method' => 'post', 'action' => $action ]));
        foreach ($query as &$link) {
            $link[ 'link_edit' ]   = self::router()->getRoute('menu.link.edit', [
                ':menu' => $link[ 'menu' ], ':item' => $link[ 'id' ] ]);
            $link[ 'link_delete' ] = self::router()->getRoute('menu.link.delete', [
                ':menu' => $link[ 'menu' ], ':item' => $link[ 'id' ] ]);
            if ($link[ 'key' ]) {
                $link_tmp       = $req->withUri($req->getUri()->withQuery($link[ 'link' ]));
                $link[ 'link' ] = $link_tmp->getUri()->__toString();
            }
            $nameLinkWeight = 'weight-' . $link[ 'id' ];
            $nameLinkActive = 'active-' . $link[ 'id' ];
            $form->number($nameLinkWeight, $nameLinkWeight, [
                    'class' => 'form-control',
                    'max'   => 50,
                    'min'   => 1,
                    'value' => $link[ 'weight' ]
                ])
                ->checkbox($nameLinkActive, $nameLinkActive, [ 'checked' => $link[ 'active' ] ]);
        }
        $form->token()
            ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);

        $messages = [];
        if (isset($_SESSION[ 'messages' ])) {
            $messages = $_SESSION[ 'messages' ];
            unset($_SESSION[ 'messages' ]);
        }
        if (isset($_SESSION[ 'errors_keys' ])) {
            $form->addAttrs($_SESSION[ 'errors_keys' ], [ 'style' => 'border-color:#a94442;' ]);
            unset($_SESSION[ 'errors_keys' ]);
        }

        $linkAdd = self::router()->getRoute('menu.link.create', [ ':menu' => $name ]);

        return self::template()
                ->getTheme('theme_admin')
                ->view('page', [
                    'title_main' => '<i class="fa fa-bars"></i> Menu'
                ])
                ->view('page.messages', $messages)
                ->render('page.content', 'menu-show.php', VIEWS_MENU, [
                    'menu'     => $query,
                    'form'     => $form,
                    'linkAdd'  => $linkAdd,
                    'menuName' => $menu[ 'title' ]
        ]);
    }

    public function showCheck($name, $req)
    {
        if (!($links = self::menu()->getLinkPerMenu($name)->fetchAll())) {
            $_SESSION[ 'messages' ][ 'errors' ] = [ 'Impossible d\'enregistrer la configuration, le menu ' . $name . ' n\'existe pas.' ];
            $route                              = self::router()->getRoute('menu.show', [
                ':item' => $name ]);

            return new Redirect($route);
        }

        $validator = (new Validator());

        foreach ($links as $link) {
            $validator->addRule('active-' . $link[ 'id' ], 'bool')
                ->addRule('weight-' . $link[ 'id' ], 'required|int|min:1|max:50');
        }

        $post = $req->getParsedBody();
        $validator->setInputs($post);

        if ($validator->isValid()) {
            foreach ($links as $link) {
                $linkUpdate = [
                    'weight' => $validator->getInput('weight-' . $link[ 'id' ]),
                    'active' => (bool) ($validator->getInput('active-' . $link[ 'id' ]) == 'on')
                ];

                self::query()
                    ->update('menu_link', $linkUpdate)
                    ->where('id', $link[ 'id' ])
                    ->execute();
            }

            $_SESSION[ 'messages' ][ 'success' ] = [ 'Votre configuration a été enregistrée.' ];
            $route                               = self::router()->getRoute('menu.show', [
                ':item' => $name ]);
        } else {
            $_SESSION[ 'messages' ][ 'errors' ] = $validator->getErrors();
            $_SESSION[ 'errors_keys' ]          = $validator->getKeyInputErrors();
            $route                              = self::router()->getRoute('menu.show', [
                ':item' => $name ]);
        }

        return new Redirect($route);
    }
}
