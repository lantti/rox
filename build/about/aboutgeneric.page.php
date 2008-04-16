<?php


class AboutGenericPage extends AboutPage
{
    public function __construct($pagename) {
        $this->_pagename = $pagename;
    }
    
    protected function getPageTitle() {
        $titles = array(
            'bod' => 'Board of Directors',
            //'getactive' => 'Get Active',
            'help' => 'Help',
            'terms' => 'Terms of Use',
            'impressum' => 'Impressum',
            'affiliations' => 'Affiliations',
            'privacy' => 'Privacy policy'
        ); 
        return 'About BeWelcome: '.$titles[$this->_pagename];
    }
    
    protected function getCurrentSubpage() {
        return $this->_pagename;
    }
    
    protected function column_col3() {
        if (!$model = $this->getModel()) {
            echo 'no model in AboutGenericView';
            $isvolunteer = false;
        } else if (!isset($_SESSION['IdMember'])) {
            $isvolunteer = false;
        } else {
            $isvolunteer = $this->getModel()->isVolunteer($_SESSION['IdMember']);
        }
        require TEMPLATE_DIR.'apps/rox/'.$this->_pagename.'.php';
    }
}


?>