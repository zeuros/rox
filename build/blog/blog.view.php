<?php
/**
 * blog view
 *
 * @package blog
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: blog.view.php 186 2006-12-11 13:37:47Z david $
 */
class BlogView extends PAppView 
{
    private $_model;
    
    public function __construct(Blog $model) 
    {
        $this->_model = $model;
    }
    
    // new functions
	/* This adds other custom styles to the page*/
	public function customStylesPublic() {
        $out = '<link rel="stylesheet" href="styles/YAML/screen/custom/bw_basemod_2colright.css" type="text/css"/>';
        $out .= '<link rel="stylesheet" href="styles/YAML/screen/custom/blog.css" type="text/css"/>';        
        $out .= '<link rel="stylesheet" href="styles/YAML/screen/custom/bw_basemod_blog_public.css" type="text/css"/>';
		return $out;
    }    
	/* This adds other custom styles to the page*/
	public function customStyles() {
        $out = '<link rel="stylesheet" href="styles/YAML/screen/custom/bw_basemod_2colright.css" type="text/css"/>';
        $out .= '<link rel="stylesheet" href="styles/YAML/screen/custom/blog.css" type="text/css"/>';        
		return $out;
    }    
	/* This adds RSS links to the header of the page*/
	public function linkRSS($RSS = false) {
        $request = PRequest::get()->request;
        $requestStr = implode('/', $request);
        if ($RSS)
            return '<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="rss/'.$requestStr.'" />';
    } 
    public function teaserPublic($userHandle) {
        require 'templates/teaser_public.php';
    }
    public function teaser($userHandle) {
        require 'templates/teaser.php';
    }
    public function teaserquicksearch() {
        require 'templates/teaser_quicksearch.php';
    }
    public function submenu($subTab) {
        require 'templates/submenu.php';        
    }    
        
    // default blog view-functions:
        
    public function createForm($callbackId) 
    {
        $User = APP_User::login();
        $Blog = new Blog;
        // get the saved post vars
        $vars =& PPostHandler::getVars($callbackId);
        // get current request
        $request = PRequest::get()->request;
        
        $errors = array();
        $lang = array();
        $i18n = new MOD_i18n('apps/blog/editcreate.php');
        $errors = $i18n->getText('errors');
        $lang = $i18n->getText('lang');
        $monthNames = array();
        $i18n = new MOD_i18n('date.php');
        $monthNames = $i18n->getText('monthNames');

        $catIt = $this->_model->getCategoryFromUserIt($User->getId());
        $tripIt = $this->_model->getTripFromUserIt($User->getId());
        $google_conf = PVars::getObj('config_google');
        $defaultVis = APP_User::getSetting($User->getId(), 'APP_blog_defaultVis');
        
        if (!isset($vars['errors']) || !is_array($vars['errors'])) {
            $vars['errors'] = array();
        }
        
        if (!isset($request[2]) || $request[2] != 'finish') {
            $actionUrl = 'blog/create';
            $submitName = '';
            $submitValue = $lang['submit_create'];
            echo '<h2>'.$lang['page_title_create'].'</h2>';
        } else { // $request[2] == 'finish'
            echo '<h2>'.$lang['finish_create_title']."</h2>\n";
            echo '<p>'.$lang['finish_create_text']."</p>\n";
            echo '<p>'.$lang['finish_create_info']."</p>\n";
        }
        require 'templates/editcreateform.php';
    }

    public function editForm($blogId, $callbackId)
    {
        $User = APP_User::login();
        // get the saved post vars
        $vars =& PPostHandler::getVars($callbackId);
        
        $errors = array();
        $lang = array();
        $i18n = new MOD_i18n('apps/blog/editcreate.php');
        $errors = $i18n->getText('errors');
        $lang = $i18n->getText('lang');
        $monthNames = array();
        $i18n = new MOD_i18n('date.php');
        $monthNames = $i18n->getText('monthNames');

        $catIt = $this->_model->getCategoryFromUserIt($User->getId());
        $tripIt = $this->_model->getTripFromUserIt($User->getId());
        $google_conf = PVars::getObj('config_google');
        $defaultVis = APP_User::getSetting($User->getId(), 'APP_blog_defaultVis');

        if (!isset($request[3]) || $request[3] != 'finish') {
            echo '<h2>'.$lang['page_title_edit'].'</h2>';
        } else { // $request[2] == 'finish'
            echo '<h2>'.$lang['finish_edit_title']."</h2>\n";
            echo $lang['finish_edit_text'] ? '<p>'.$lang['finish_edit_text']."</p>\n" : '';
            echo $lang['finish_edit_info'] ? '<p>'.$lang['finish_edit_info']."</p>\n" : '';
        }

        $actionUrl = 'blog/edit/'.$blogId;
        $submitName = 'submit_blog_edit';
        $submitValue = $lang['submit_edit'];

        require 'templates/editcreateform.php';
    }

    public function blogText($str, $stripAfterHR = true) 
    {
        $repCount = 0;
        $strrep = preg_replace('/<hr.*?\/?>'.($stripAfterHR ? '.*' : '').'/', '', $str, -1, $repCount);
        $html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>'.$strrep.'</body></html>';
        $doc = @DOMDocument::loadHTML($html);
        if (!$doc)
            return $str;
        $x = new DOMXPath($doc);
        $nodes = $x->query('/html/body/node()');
        $ret = '';
        foreach ($nodes as $node) {
        	$ret .= $doc->saveXML($node);
        }
        return array($ret, $repCount);
    }

    public function delete($callbackId, $post)
    {
        require 'templates/delete.php';
    }

    /**
     * Displays Main blog/ page.
     */
    public function allBlogs($page = 1)
    {
        $blogIt      = $this->_model->getRecentPostIt();
        $pages       = PFunctions::paginate($blogIt, $page);
        $blogIt      = $pages[0];
        $maxPage     = $pages[2];
        $pages       = $pages[1];
        $currentPage = $page;
        require 'templates/allblogs.php';
        $this->pages($pages, $currentPage, $maxPage, 'blog/page%d');
    }
    
    public function pages($pages, $currentPage, $maxPage, $request) 
    {
        require 'templates/pages.php';
    }

    /**
     * Displays blogsite of given user.
     */
    public function userPosts($userHandle, $page = 1)
    {
        if (!$userId = APP_User::userId($userHandle))
            return false;
        $blogIt = $this->_model->getRecentPostIt($userId);
        $pages       = PFunctions::paginate($blogIt, $page);
        $blogIt      = $pages[0];
        $maxPage     = $pages[2];
        $pages       = $pages[1];
        $currentPage = $page;
        require 'templates/userposts.php';
        $this->pages($pages, $currentPage, $maxPage, 'blog/'.$userHandle.'/page%d');
    }
    
    /**
     * Displays blog posts in a given category.
     */
    public function PostsByCategory($categoryId, $page = 1)
    {
        $catIt = $this->_model->getCategoryFromUserIt(false,$categoryId);
        $cat = $catIt->fetch(PDB::FETCH_OBJ);
        if (!$cat) {
            echo '<p class="error">Category doesn`t exist</p>';
            return false;
        }
        $title = $cat->name;
        $blogIt      = $this->_model->getRecentPostIt('',$categoryId);
        $pages       = PFunctions::paginate($blogIt, $page);
        $blogIt      = $pages[0];
        $maxPage     = $pages[2];
        $pages       = $pages[1];
        $currentPage = $page;
        require 'templates/allblogs.php';
        $this->pages($pages, $currentPage, $maxPage, 'blog/page%d');
    }
    
    public function stickyPosts() {
        require 'templates/stickyposts.php';
    }

    public function settingsForm() {
    	require 'templates/settingsform.php';
    }

    /**
     * Displays a single blogt.
     */
    public function singlePost($blog, $showComments = true) {
        require 'templates/singlepost.php';
    }
    
    public function searchPage($posts = false,$tagsposts = false) {
        require 'templates/searchpage.php';
    }

    public function tags($tag = false) {
        require 'templates/tags.php';
    }

    public function userbar()
    {
    	if (!APP_User::login())
            return false;
        require 'templates/userbar.php';
    }
    public function sidebarRSS()
    {
        require 'templates/sidebar_rss.php';
    }

    public function userSettingsForm()
    {
    	require 'templates/usersettings.php';
    }

    public function categories() {
        require 'templates/categories.php';
    }

    public function categories_list($categoryId, $username = false) {
        require 'templates/categories_list.php';
    }
    
    /**
    * Generate clickable Links to the suggested tags
    *
    * @param stringarray tags The new suggested tags to create the links from
    * @return Links to update the tag form
    */
    public function generateClickableTagSuggestions($tags)
    {
        if ($tags) {
            $out = '';
            foreach ($tags as $suggestion) {
                $out .= '<a href="#" onclick="javascript:BlogSuggest.updateForm(\'';
                foreach ($suggestion as $word) {
                    $out .= $word.', ';
                }
                $out = rtrim($out, ', ');
                $out .= '\'); return false;">'.$word.'</a>, ';
            }
            $out = rtrim($out, ', ');
            return $out;
        }
        return '';
    }

    /**
    * Generate a list of the found locations
    * @param locations The places to display
    * @return HTML-List of the locations
    */
    public function generateLocationOverview($locations)
    {
    	$i18n = new MOD_i18n('apps/blog/editcreate.php');
		$lang = $i18n->getText('lang');
        if ($locations) {
        	$out = '<p class="desc">'.$lang['hint_click_location'].'</p>';
            $out .= '<ol id="locations">';
            foreach ($locations as $location) {
                $out .= '<li id="li_'.$location['geonameId'].'"><a id="href_'.$location['geonameId'].'" onclick="javascript: setMap(\''.$location['geonameId'].'\', \''.$location['lat'].'\',  \''.$location['lng'].'\', \''.$location['zoom'].'\', \''.$location['name'].'\', \''.$location['countryName'].'\', \''.$location['countryCode'].'\', \''.$location['fcodeName'].'\'); return false;">'.$location['name'].', '.$location['countryName'];
                if (isset($location['fcodeName'])) {
                    $out .= ' ('.$location['fcodeName'].')';
                }
                $out .= '</a></li>';
            }
            $out .= '</ol>';
            return $out;
        }
        return '';
    }
}
?>