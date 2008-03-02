<?php


class VolunteerPageView extends RoxPageView
{
    protected function getTopmenuActiveItem() {
        return 'getanswers';
    }

    protected function teaserContent() {
        require TEMPLATE_DIR.'apps/rox/teaser_volunteer.php';
    }
    
    protected function getPageTitle() {
        return 'Volunteer Pages - BeWelcome *';
    }
    
    protected function column_col1()
    {
        require TEMPLATE_DIR.'apps/rox/volunteertoolsbar.php';
    }
    
    protected function getSubmenuItems()
    {
        $items = array();
        $items[] = array('dashboard', 'volunteer/dashboard', 'VolunteerDashboard');
        $items[] = array('tools', 'volunteer/tools', 'VolunteerTools');
        $items[] = array('search', 'volunteer/search', 'VolunteerSearch');
        $items[] = array('tasks', 'volunteer/tasks', 'VolunteerTasks');
        $items[] = array('features', 'volunteer/features', 'VolunteerFeatures');
        return $items;
    }
    
    public function volunteerpage()
    {
        // check if member belongs to group Volunteers
        $isvolunteer = $this->_model->isVolunteer($_SESSION['IdMember']);
        define('MAGPIE_CACHE_ON',false);
        require_once ("magpierss/rss_fetch.inc");
        require TEMPLATE_DIR.'apps/rox/volunteer.php';
    }
    
    
}



class VolunteerToolsView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'tools';
    }
    
    private $_toolname;
    public function __construct($toolname) {
        $this->_toolname = $toolname;
    }
    protected function column_col3() {
        $currentSubPage = $this->_toolname;
        require TEMPLATE_DIR.'apps/rox/volunteertoolspage.php';
    }
}

class VolunteerSearchView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'search';
    }
    
    protected function column_col3() { 
        require TEMPLATE_DIR.'apps/rox/volunteersearchpage.php';
    }
}

class VolunteerTaskView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'tasks';
    }
    
    protected function column_col3() { 
        require TEMPLATE_DIR.'apps/rox/volunteertaskpage.php';
    }
}

class VolunteerFeaturesView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'features';
    }
    
    protected function column_col3() { 
        require TEMPLATE_DIR.'apps/rox/volunteerfeaturespage.php';
    }
}


class VolunteerDashboardView extends VolunteerPageView
{
    protected function getSubmenuActiveItem() {
        return 'dashboard';
    }
    
    protected function column_col3() {
        define('MAGPIE_CACHE_ON',false);
        require_once ("magpierss/rss_fetch.inc");
        $isvolunteer = true;
        require TEMPLATE_DIR.'apps/rox/volunteer.php';
    }
}


?>