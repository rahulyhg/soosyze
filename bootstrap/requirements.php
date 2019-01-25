<?php

$requiement = new Requiement();
$require    = $requiement
    ->versionPhp('5.4')
    ->extensions(array( 'hash', 'json', 'mbstring', 'session' ));

if (!$require->isValid()) {
    echo $require;
    exit();
}

class Requiement
{
    protected $error;

    protected $warning;

    protected $requiements;

    protected $tests;

    public function __construct()
    {
        $this->requiements = array();
        $this->tests       = array();
    }

    public function __toString()
    {
        $html = '<!DOCTYPE html>
        <html lang=\'en\'>
        <head>
            <meta http-equiv=\'Content-Type\' content=\'text/html; charset=utf-8\'>
            <meta content=\'IE=edge\' http-equiv=\'X-UA-Compatible\'>
            <title>site</title>
            <style>
            table{width: 100%; border-collapse: collapse; margin-bottom: 3px;}
            thead{background-color: #272822; color:#FFF;}
            thead th{padding: 10px; border-bottom: 3px solid #FFF;}
            th{border:0px;}
            .table-trace td{border-left: #FFF 3px solid;}
            .table-trace tr:hover td,
            .table-trace tr:hover th{background-color: #272822;}
            .table-trace th, 
            .table-trace td{padding: 10px;}
            .two th,
            .two td{background-color: #E9E9E9;}
            .error th,
            .error td{color: #be3232;}
            .warning th,
            .warning td{color: #bc9533;}
            .success th,
            .success td{color: #33bc36;}
            </style>
        </head>
        <body>
            <div style=\'width: 80%; margin-left: auto; margin-right: auto; overflow-x:auto\'>
                <h1 style=\'color: #be3232; text-align:center;\'>✘ Error Required</h1>
                <table class=\'table-trace\'>
                    <thead>
                        <tr>
                            <th>Validité</th>
                            <th>Type</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>';
        foreach ($this->requiements as $key => $value) {
            $class = $key % 2
                ? 'two'
                : 'one';
            $html  .= "<tr class='$class {$value[ 'type' ]}'>";
            if ('error' == $value[ 'type' ]) {
                $html .= '<th>✗</th>';
            } elseif ('warning' == $value[ 'type' ]) {
                $html .= '<th>!</th>';
            } else {
                $html .= '<th>✓</th>';
            }
            $html .= "<td>{$value[ 'name' ]}</td>
                    <td>{$value[ 'message' ]}</td>
                </tr>";
        }
        $html .= '</tbody>
                </table>
            </div>
        </body>
        </html>';

        return $html;
    }

    public function versionPhp($version, $operator = '>=')
    {
        $this->tests[] = array(
            'func' => __FUNCTION__,
            'args' => array(
                htmlspecialchars(strtolower($version)),
                $operator
            ));

        return $this;
    }

    public function extensions($extensions)
    {
        $this->tests[] = array(
            'func' => __FUNCTION__,
            'args' => array( $extensions )
        );

        return $this;
    }

    public function memoryLimit($size = 128, $unit = 'MB')
    {
        $this->tests[] = array(
            'func' => __FUNCTION__,
            'args' => array(
                $size,
                $unit
            ));

        return $this;
    }

    public function isValid()
    {
        $this->error   = null;
        $this->warning = null;
        foreach ($this->tests as $test) {
            call_user_func_array(array( $this, 'valid' . $test[ 'func' ] ), $test[ 'args' ]);
        }

        return empty($this->error) && empty($this->warning);
    }

    protected function validVersionPhp($version, $operator = '>=')
    {
        if (!function_exists('version_compare')) {
            $this->addReturn('versionphp', 'warning', 'PHP version', 'La version ne peut être comparée.');
        } elseif (!version_compare(phpversion(), $version, $operator)) {
            $this->addReturn('versionphp', 'error', 'PHP version', 'La version PHP :version :operator est attendue. Vous êtes actuellement en version :current_version', array(
                ':version'         => htmlspecialchars($version),
                ':operator'        => htmlspecialchars($operator),
                ':current_version' => phpversion()
            ));
        } else {
            $this->addReturn('versionphp', 'success', 'PHP version', 'La version PHP :version_current est ok.', array(
                ':version_current' => phpversion()
            ));
        }
    }

    protected function validExtensions($extensions = array())
    {
        foreach ($extensions as $value) {
            if (!function_exists('extension_loaded')) {
                // Ne peu pas tester les extension chargées
                $this->addReturn('extension', 'warning', 'PHP extensions', 'Les extensions ne peuvent être testées.');
            } elseif (!extension_loaded($value)) {
                $this->addReturn('extension', 'error', 'PHP extensions', 'L\'extension <code>:extension</code> ne doit pas être désactivée.', array(
                    ':extension' => htmlspecialchars($value)
                ));
            } else {
                $this->addReturn('extension', 'success', 'PHP extensions', 'L\'extension <code>:extension</code> est activée.', array(
                    ':extension' => htmlspecialchars($value)
                ));
            }
        }
    }

    protected function validMemoryLimit($size = 128, $bytes = 'MB')
    {
        if (!function_exists('ini_get')) {
            $this->addReturn('memory', 'warning', 'PHP memory limit', 'La mémoire requise ne peut être testée.');

            return;
        }
        $memory = ini_get('memory_limit');
        if (false === $memory) {
            $this->addReturn('memory', 'warning', 'PHP memory limit', 'La configuration memory_limit n\'existe pas.');
        } elseif (null === $memory) {
            $this->addReturn('memory', 'warning', 'PHP memory limit', 'La configuration memory_limit est vide.');
        }
    }

    private function addReturn($key, $type, $name, $message, $args = array())
    {
        $msg = str_replace(array_keys($args), $args, $message);
        array_push($this->requiements, array(
            'key'     => $key,
            'type'    => $type,
            'name'    => $name,
            'message' => $msg
        ));
        if ($type === 'error') {
            if (empty($this->error)) {
                $this->error = true;
            }
        } elseif ($type === 'warning') {
            if (empty($this->warning)) {
                $this->warning = true;
            }
        }
    }
}
