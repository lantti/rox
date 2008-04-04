<?php


class PageWithHTML extends AbstractBasePage
{
    private $_widgets = array();  // will be asked for stylesheet and scriptfile information

    public function render() {
        $this->_render();
        PVars::getObj('page')->output_done = true;
    }
    
    
    /**
     * don't forget to call
     * $stylesheets = parent::$this->getStylesheets();
     * when reimplementing this method!!
     */
    protected function getStylesheets()
    {
        $stylesheets = array();
        foreach ($this->_widgets as $widget) {
            foreach ($widget->getStylesheets() as $stylesheet) {
                $stylesheets[] = $stylesheet;
            }
        }
        return $stylesheets;
    }
    
    protected function getScriptfiles()
    {
        $scriptfiles = array(
            'script/main.js'
        );
        foreach ($this->_widgets as $widget) {
            foreach ($widget->getScriptfiles() as $scriptfile) {
                $scriptfiles[] = $scriptfile;
            }
        }
        return $scriptfiles;
    }
    
    
    protected function __call($methodname, $args)
    {
        echo '
            Please implement<br>
            '.get_class($this).'<br>
            ::'.$methodname.'()
        '; 
    }
    
    protected function getPageTitle() {
        return 'BeWelcome';
    }
    
    
    /**
     * Widgets added this way will be asked
     * for stylesheet and scriptfile information
     * TODO: evtl not a good idea to do it this way.
     *
     * @param RoxWidget $widget
     */
    public function addWidget(RoxWidget $widget)
    {
        $this->_widgets[] = $widget;
    }
    
    
    private function _render() {
        header('Content-type: text/html;charset="utf-8"');
        ?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=PVars::get()->lang; ?>" lang="<?=PVars::get()->lang; ?>" xmlns:v="urn:schemas-microsoft-com:vml">
        <head>
        <?php
        $this->head();
        ?>
        </head>
        <body>
        <?php
        $this->body();
        ?>
        </body>
        </html><?php
    }
    
    protected function includeStylesheets()
    {
        ?>
        <!--[if lte IE 7]>
        <link rel="stylesheet" href="styles/YAML/patches/iehacks_3col_vlines.css" type="text/css" />
        <![endif]-->
        
        <?php
        if (!$stylesheets = $this->getStylesheets()) {
            // no stylesheets
        } else foreach($stylesheets as $url) {
            ?><link rel="stylesheet" href="<?=$url ?>" type="text/css" />
            <?php
        }
    }
    
    protected function includeScriptfiles()
    {
        ?>
        <!--[if lt IE 7]>
        <script defer type="text/javascript" src="script/pngfix.js"></script>
        <![endif]-->
        <?php
        if (!$scriptfiles = $this->getScriptfiles()) {
            // no stylesheets
        } else foreach($scriptfiles as $url) {
            ?><link rel="stylesheet" href="<?=$url ?>" type="text/css" />
            <?php
        }
    }
    
    protected function head()
    {
        ?>
        <title><?=$this->getPageTitle() ?></title>
        <base id="baseuri" href="<?=PVars::getObj('env')->baseuri; ?>" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="verify-v1" content="NzxSlKbYK+CRnCfULeWj0RaPCGNIuPqq10oUpGAEyWw=" />
        
        <?php
        $this->includeStylesheets();
        ?>
    
        <?php
        $this->includeScriptfiles();
        $this->_tr_buffer_header = $this->getWords()->flushBuffer();
    }
    
    protected function getPagePermalink() {
        return 'index';
    }
}


?>