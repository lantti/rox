<?php

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;

class TwigView extends AbstractBasePage {

    protected $_loader;
    private $_environment;
    private $_template;
    private $_parameters;
    protected $_words;
    protected $_translator;
    private $_stylesheets = array(
        'bewelcome.css?1',
/*
                 'minimal/screen/custom/index.css?3',
                'minimal/screen/custom/font-awesome.min.css',
                'minimal/screen/custom/font-awesome-ie7.min.css'
                */
        'select2.css',
        'select2-bootstrap.css',

    );
    private $_lateScriptFiles = array(
        'bootstrap' => 'bootstrap/bootstrap.min.js',
        'initialize' => 'common/initialize.js'
    );
    private $_earlyScriptFiles = array(
        'common' => 'common/common.js?1',
        'jQuery' => 'jquery-1.11.2/jquery-1.11.2.min.js',
        'select2' => 'select2/select2.min.js'
    );

    public function __construct($logged_in = false) {
        $this->_loader = new Twig_Loader_Filesystem();
        $this->_loader->addPath(SCRIPT_BASE . 'templates/twig/base', 'base');
        $this->_environment = new Twig_Environment(
            $this->_loader ,
            array(
                'cache' => SCRIPT_BASE . 'data/twig',
                'auto_reload' => true,
            )
        );
        $this->_defaults = $this->_getDefaults(new RoxModelBase());

        $this->_words = $this->getWords();

        $this->_translator = new Translator($_SESSION['lang'], new MessageSelector());
        if ($_SESSION['lang'] <> 'en') {
            $this->_translator->setFallbackLocales(array('en'));
        }
        $this->_translator->addLoader('database', new DatabaseLoader());
        $this->_translator->addResource('database', null, $_SESSION['lang']);
        $this->_environment->addExtension(new Symfony\Bridge\Twig\Extension\TranslationExtension($this->_translator));
    }

    private function _getDefaults(RoxModelBase $roxModel) {
        $member = $roxModel->getLoggedInMember();
        $loggedIn = ($member !== false);
        return array(
            'logged_in' => $loggedIn,
            'meta.robots' => 'ALL'
        );
    }

    private function _getLanguages() {
        $model = new FlaglistModel();
        $langarr = array();
        foreach($model->getLanguages() as $language) {
            $lang = new StdClass;
            $lang->NativeName = $language->Name;
            $lang->TranslatedName = $this->_words->getSilent($language->WordCode);
            $lang->ShortCode = $language->ShortCode;
            $langarr[] = $lang;
        }
        $ascending = function($a, $b) {
            if ($a == $b) {
                return 0;
            }
            return (strtolower($a->TranslatedName) < strToLower($b->TranslatedName)) ? -1 : 1;
        };
        usort($langarr, $ascending);

        return array(
            'language' => $_SESSION['lang'],
            'languages' => $langarr
        );
    }

    protected function addStylesheet($stylesheet) {
        $this->_stylesheets[] = $stylesheet;
    }

    protected function addEarlyJavascriptFile($scriptFile, $name = false) {
        if ($name) {
            $this->_earlyScriptFiles[$name] = $scriptFile;
        } else {
            $this->_earlyScriptFiles[] = $scriptFile;
        }
        error_log(print_r($this->_earlyScriptFiles, true));
    }

    protected function addLateJavascriptFile($scriptFile, $name = false) {
        if ($name) {
            $this->_lateScriptFiles[$name] = $scriptFile;
        } else {
            $this->_lateScriptFiles[] = $scriptFile;
        }
    }

    protected function _getStylesheets() {
        return array(
            'stylesheets' => $this->_stylesheets
        );
    }

    protected function _getEarlyJavascriptFiles() {
        return array(
            'earlyScriptFiles' => $this->_earlyScriptFiles
        );
    }

    protected function _getLateJavascriptFiles() {
        return array(
            'lateScriptFiles' => $this->_lateScriptFiles
        );
    }

    public function setTemplate($template, $namespace = false, $parameters = array()) {
        if ($namespace) {
            $this->_loader->addPath(SCRIPT_BASE . 'templates/twig/' . $namespace, $namespace);
            $this->_template = '@' . $namespace . '/' . $template;
        } else {
            $this->_template = $template;
        }
        $finalParameters = array_merge(
            $parameters,
            $this->_getStylesheets(),
            $this->_getLanguages(),
            $this->_getEarlyJavascriptFiles(),
            $this->_getLateJavascriptFiles(),
            $this->_defaults
        );
        $this->_parameters = $finalParameters;
    }

    public function render() {
        echo $this->_environment->render($this->_template, $this->_parameters);
        PVars::getObj('page')->output_done = true;
    }
}