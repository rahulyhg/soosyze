<?php

namespace System\Services;

class HookConfig
{
    protected $template;

    protected $file;

    public function __construct($template, $file)
    {
        $this->template = $template;
        $this->file     = $file;
    }

    public function menu(&$menu)
    {
        $menu[] = [
            'key'        => 'system',
            'title_link' => 'Système'
        ];
    }

    public function form(&$form, $data)
    {
        $optionThemes = [];
        foreach ($this->template->getThemes() as $theme) {
            $optionThemes[] = [ 'value' => $theme, 'label' => $theme ];
        }

        return $form->group('system-information-fieldset', 'fieldset', function ($form) use ($data, $optionThemes) {
                    $form->legend('system-information-legend', 'Information')
                    ->group('system-email-group', 'div', function ($form) use ($data) {
                        $form->label('system-email-label', 'E-mail du site')
                        ->email('email', 'email', [
                            'class'       => 'form-control',
                            'required'    => 1,
                            'placeholder' => 'E-mail',
                            'value'       => $data[ 'email' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-maintenance-group', 'div', function ($form) use ($data) {
                        $form->checkbox('maintenance', 'maintenance', [
                            'checked' => $data[ 'maintenance' ]
                        ])
                        ->label('system-maintenance-group', '<span class="ui"></span>Mettre le site en maintenance', [
                            'for' => 'maintenance'
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-theme-group', 'div', function ($form) use ($data, $optionThemes) {
                        $form->label('system-theme-label', 'Theme du site')
                        ->select('theme', 'theme', $optionThemes, [
                            'class'    => 'form-control',
                            'required' => 1,
                            'selected' => $data[ 'theme' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-theme_admin-group', 'div', function ($form) use ($data, $optionThemes) {
                        $form->label('system-theme_admin-label', 'Theme d\'administration du site')
                        ->select('theme_admin', 'theme_admin', $optionThemes, [
                            'class'    => 'form-control',
                            'required' => 1,
                            'selected' => $data[ 'theme_admin' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-logo-group', 'div', function ($form) use ($data) {
                        $form->label('label-logo', 'Logo', [ 'class' => 'control-label' ]);
                        $this->file->inputFile('logo', $form, $data[ 'logo' ]);
                        $form->html('system-logo-info-size', '<p:css:attr>:_content</p>', [
                            '_content' => 'Le fichier doit peser moins de <b>200 Ko</b>.'
                        ]);
                    }, [ 'class' => 'form-group' ]);
                })
                ->group('system-path-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('system-path-legend', 'Page par défaut')
                    ->group('system-path_index-group', 'div', function ($form) use ($data) {
                        $form->label('system-path_index-label', 'Page d’accueil par défaut')
                        ->text('path_index', 'path_index', [
                            'class'       => 'form-control',
                            'required'    => 1,
                            'placeholder' => 'Path page index',
                            'value'       => $data[ 'path_index' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-path_access_denied-group', 'div', function ($form) use ($data) {
                        $form->label('system-path_access_denied-label', 'Page 403 par défaut (accès refusé)')
                        ->text('path_access_denied', 'path_access_denied', [
                            'class'       => 'form-control',
                            'placeholder' => 'Path page access denied',
                            'value'       => $data[ 'path_access_denied' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-path_no_found-group', 'div', function ($form) use ($data) {
                        $form->label('system-path_no_found-label', 'Page 404 par défaut (page non trouvée)')
                        ->text('path_no_found', 'path_no_found', [
                            'class'       => 'form-control',
                            'placeholder' => 'Path page not found',
                            'value'       => $data[ 'path_no_found' ]
                        ]);
                    }, [ 'class' => 'form-group' ]);
                })
                ->group('system-metadata-fieldset', 'fieldset', function ($form) use ($data) {
                    $form->legend('system-metadata-legend', 'SEO Metadonnées')
                    ->group('system-title-group', 'div', function ($form) use ($data) {
                        $form->label('system-title-label', 'Titre du site')
                        ->text('title', 'title', [
                            'class'       => 'form-control',
                            'maxlength'   => 64,
                            'placeholder' => 'Titre du site',
                            'required'    => 'required',
                            'value'       => $data[ 'title' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-description-group', 'div', function ($form) use ($data) {
                        $form->label('system-description-label', 'Description')
                        ->textarea('description', 'description', $data[ 'description' ], [
                            'class'     => 'form-control',
                            'maxlength' => 256,
                            'required'  => 'required',
                            'rows'      => 5
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-keyboard-group', 'div', function ($form) use ($data) {
                        $form->label('system-keyboard-label', 'Mots-clés')
                        ->text('keyboard', 'keyboard', [
                            'class'       => 'form-control',
                            'placeholder' => 'Mot1, Mot2, Mot3...',
                            'value'       => $data[ 'keyboard' ]
                        ]);
                    }, [ 'class' => 'form-group' ])
                    ->group('system-group-favicon', 'div', function ($form) use ($data) {
                        $form->label('system-favicon-label', 'Favicon', [ 'class' => 'control-label' ]);
                        $this->file->inputFile('favicon', $form, $data[ 'favicon' ]);
                        $form->html('system-favicon-info-size', '<p:css:attr>:_content</p>', [
                            '_content' => 'Le fichier doit peser moins de <b>100 Ko</b>.'
                        ])->html('system-favicon-info-dimensions', '<p:css:attr>:_content</p>', [
                            '_content' => 'La largeur et hauteur min et max : <b>16px et 310px</b>.'
                        ]);
                    }, [ 'class' => 'form-group' ]);
                })
                ->token()
                ->submit('submit', 'Enregistrer', [ 'class' => 'btn btn-success' ]);
    }

    public function validator(&$validator)
    {
        $themes = implode(',', $this->template->getThemes());
        $validator->setRules([
            'email'              => 'required|email|max:254|htmlsc',
            'maintenance'        => '!required|bool',
            'theme'              => 'required|inarray:' . $themes,
            'theme_admin'        => 'required|inarray:' . $themes,
            'path_index'         => 'required|string|htmlsc',
            'path_access_denied' => '!required|string|htmlsc',
            'path_no_found'      => '!required|string|htmlsc',
            'title'              => 'required|string|max:64|htmlsc',
            'description'        => 'required|string|max:256|htmlsc',
            'keyboard'           => '!required|string|htmlsc',
            'favicon'            => '!required|image|image_dimensions_height:16,310|image_dimensions_width:16,310|max:100000',
            'logo'               => '!required|image|max:2000000',
            'token'              => 'required|token'
        ]);
    }

    public function before(&$validator, &$data)
    {
        $data = [
            'email'              => $validator->getInput('email'),
            'maintenance'        => (bool) $validator->getInput('maintenance'),
            'theme'              => $validator->getInput('theme'),
            'theme_admin'        => $validator->getInput('theme_admin'),
            'path_index'         => $validator->getInput('path_index'),
            'path_access_denied' => $validator->getInput('path_access_denied'),
            'path_no_found'      => $validator->getInput('path_no_found'),
            'title'              => $validator->getInput('title'),
            'description'        => $validator->getInput('description'),
            'keyboard'           => $validator->getInput('keyboard'),
        ];
    }

    public function files(&$inputFiles)
    {
        $inputFiles = [ 'logo', 'favicon' ];
    }
}
