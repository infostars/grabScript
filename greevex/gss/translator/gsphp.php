<?php

namespace greevex\gss\translator;

use greevex\gss\lib\error;
use greevex\gss\translator\action\actionTranslator;
use greevex\gss\translator\action\foreachTranslator;
use greevex\gss\translator\action\ifTranslator;
use greevex\gss\translator\action\variableActionTranslator;
use greevex\gss\translator\action\variableCallTranslator;
use greevex\gss\translator\action\translatorInterface;

/**
 * @author greevex
 * @date: 10/1/14 6:10 PM
 */

class gsphp
{
    private $structure;
    private $content = '%initialize%';
    private $generator = __CLASS__;
    private $properties = [];

    public function __construct($structure)
    {
        $this->structure = $structure;
    }


    public function process()
    {
        $this->initialize();
        $this->properties();
        $this->blocks();

        return $this->content;
    }

    protected function putToPointer($pointer, $content)
    {
        $pointer = "%{$pointer}%";
        if(strpos($this->content, $pointer) === false) {
            error::throwNewCompileException("Unable to find pointer {$pointer}", 0, 0);
        }
        $this->content = str_replace($pointer, $content, $this->content);
    }

    protected function replaceByPointer($pointer, $content, $haystack)
    {
        $pointer = "%{$pointer}%";
        if(strpos($haystack, $pointer) === false) {
            error::throwNewCompileException("Unable to find pointer {$pointer}", 0, 0);
        }
        return str_replace($pointer, $content, $haystack);
    }

    protected function initialize()
    {
        $namespace = helper::dotPathToNamespace($this->structure['package']);
        $packageData = helper::splitNamespaceAndClass($namespace);
        $date = date("Y-m-d H:i:s");

        $rootpath = dirname(__DIR__);

        $objects = [];
        $usages = "";
        foreach(scandir(dirname(__DIR__) . '/objects', SORT_ASC) as $file) {
            if(substr($file, -4) != '.php') {
                continue;
            }
            $objectName = substr($file, 0, -4);
            if(strpos($objectName, '_') !== 0) {
                $usages .= "use greevex\\gss\\objects\\{$objectName};\n";
            }
        }
        $usages .= "\n";

        foreach($objects as $object) {
            $usages .= "require_once '{$rootpath}/objects/{$object}.php';\n";
        }

        $loadRoot = dirname(dirname($rootpath));
        $autoloader = <<<AL
\$rootpath = '{$loadRoot}';
require_once "{\$rootpath}/greevex/autoloader.php";
\$autoloader = new \SplClassLoader('greevex', \$rootpath);
\$autoloader->register();
AL;


        $base = <<<PHP

namespace {$packageData['namespace']};

{$autoloader}

use greevex\\gss\\lib\\error;
{$usages}

/**
 * This file is generated by GsPHP
 *
 * @generator {$this->generator}
 * @date {$date}
 */
class {$packageData['class']}
{
    %properties_init%

    public function _initialize()
    {
        %properties_assign%
    }

    %blocks%
}

\$app = new {$packageData['class']}();
\$app->_initialize();
\$app->start();
PHP;
        $this->putToPointer('initialize', $base);
    }

    private function properties()
    {
        $properties_init = "\n";
        $properties_assign = "\n";
        $this->properties = '';
        foreach($this->structure['vars'] as $var) {
            $properties_init .= $this->property_init($var);
            $properties_assign .= $this->property_assign($var);
        }

        $this->putToPointer('properties_init', $properties_init);
        $this->putToPointer('properties_assign', $properties_assign);
    }

    private function blocks()
    {
        $blocks = "\n";
        foreach($this->structure['blocks'] as $block) {
            $blocks .= $this->block_assign($block);
        }

        $this->putToPointer('blocks', $blocks);
    }

    protected function property_init($var)
    {
        $content = <<<PHP
    /**
     * @var {$var['instanceOf']} {$var['varData']['value']}
     */
    protected {$var['varData']['value']};

PHP;
        return $content;
    }

    protected function property_assign($var)
    {
        $content = <<<PHP
        \$this->{$var['varName']} = new {$var['instanceOf']}();

PHP;
        $this->properties .= "\${$var['varName']} =& \$this->{$var['varName']};\n        ";

        return $content;
    }

    private function block_assign($block)
    {
        $bc = <<<PHP
    /**
     * This function is generated by
     *  {$this->generator}
     *%annotation%
     *
     *%return_annotation%
     **/
    public function {$block['name']}(%input%)
    {
        \$SDS = SDS::getInstance();

        {$this->properties}
        %content%

        %return%
    }


PHP;

        $inputBlock = $this->block_input($block);
        $bc = $this->replaceByPointer('annotation', $inputBlock['annotation'], $bc);
        $bc = $this->replaceByPointer('input', $inputBlock['input'], $bc);
        $bc = $this->replaceByPointer('content', $this->block_content($block), $bc);
        $returnBlock = $this->block_return($block);
        $bc = $this->replaceByPointer('return_annotation', $returnBlock['annotation'], $bc);
        $bc = $this->replaceByPointer('return', $returnBlock['return'], $bc);

        return $bc;
    }

    private function block_input($block)
    {
        $input = [];
        $result = [
            'annotation' => '',
            'input' => ''
        ];
        if(!isset($block['input']['meta'])) {
            return $result;
        }
        foreach($block['input']['meta'] as $inputParam) {
            $someVar = helper::getValueByToken($inputParam);
            $result['annotation'] .= <<<PHP

     * @var {$someVar['type']} {$someVar['value']}
PHP;
            $input[] = $someVar['value'];
        }
        $result['input'] = implode(',', $input);
        return $result;
    }

    private function block_content($block)
    {
        $result = [];
        /** @var translatorInterface $translator */
        $translator = null;
        foreach($block['contents'] as $content) {
            switch($content['type']) {
                case 'variable_action':
                    $translator = new variableActionTranslator($content);
                    break;
                case 'variable_call':
                    $translator = new variableCallTranslator($content);
                    break;
                case 'foreach':
                    $translator = new foreachTranslator($content);
                    break;
                case 'action':
                    $translator = new actionTranslator($content);
                    break;
                case 'if':
                    $translator = new ifTranslator($content);
                    break;
                default:
                    error::throwNewCompileException("Unexpected block content action {$content['type']}", $block['line']);
                    break;
            }
            $result[] = $translator->getSourceCode();
        }

        return implode("\n", $result);
    }

    private function block_return($block)
    {
        $result = [
            'annotation' => '',
            'return' => ''
        ];
        if(empty($block['return'])) {
            return $result;
        }
        $return = helper::getValueByToken($block['return']['meta']['put_result_to']);
        $result['annotation'] = <<<PHP

     * @return {$return['type']} {$return['value']}
PHP;
        $result['return'] = <<<PHP

        return {$return['value']};
PHP;


        return $result;
    }
}