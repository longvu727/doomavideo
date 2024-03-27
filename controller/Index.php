<?php
    include_once( "Controller.php" );
    include_once('doomaconfig/Config.php');
    
    class Index extends Controller {
        private $db;
        private $videoPerPage;
        private $DOOMA_GLOBALS;
        private $VIEW_VARS;
        
        public function __construct() { 
            parent::__construct();
            
            $this->DOOMA_GLOBALS  = Config::getGlobals();
            $this->VIEW_VARS  = $this->DOOMA_GLOBALS;
            $this->db             = Config::getDB();
            $this->videoPerPage   = 20; 
        }
        
        public function searchSuggestion() {
            $VIEW_VARS = $this->VIEW_VARS;
            
            include_once($this->DOOMA_GLOBALS["siteRoot"] . "/controller/SearchSuggestion.php");
            
            $searchSuggestion= new SearchSuggestion();
            $searchSuggestion->printSuggestion($this->db,$_REQUEST['title']);
        }
        
        public function getLatestVideo($db) {
            include_once($this->DOOMA_GLOBALS['siteRoot'] . "/models/Video.php");
            
            $video = new Video($db);
            return $video->findAllByUpdateTime();
        }

        public function searchResult() {
            $VIEW_VARS = $this->VIEW_VARS;
            
            include_once($this->DOOMA_GLOBALS["siteRoot"] . "/controller/SearchResult.php");
            
            $page=isset($_REQUEST['page'])?$_REQUEST['page']:1;
            $searchText=  isset($_REQUEST['searchText'])  ?  $_REQUEST['searchText'] : '';
            
            $searchResult= new SearchResult();
            $videoResults=$searchResult->searchMovieByTitle($this->db, $searchText, $this->videoPerPage, $page);
            $videoCount=$videoResults['resultCount'];
            $VIEW_VARS['videoList']=$videoResults['results'];
            
            $VIEW_VARS['currentPage']=$page;
            $VIEW_VARS['totalPage']=ceil($videoCount/$this->videoPerPage);
            $VIEW_VARS['searchTerm']=$searchText;
            
            $VIEW_VARS['nextPage']=($VIEW_VARS['currentPage']+1 > $VIEW_VARS['totalPage'])?0:$VIEW_VARS['currentPage']+1;
            $VIEW_VARS['previousPage']=$VIEW_VARS['currentPage']-1;
            
            include_once($this->DOOMA_GLOBALS["siteRoot"] . "/views/SearchResultsPage.php");
        }
        
        public function episode() {
            $VIEW_VARS = $this->VIEW_VARS;
            
            include_once($this->DOOMA_GLOBALS["siteRoot"] . "/controller/Episode.php");
            
            $episode = new Episode();
            $results=$episode->getLinksByVideoId($this->db,$_REQUEST['videoId']);
            
            $VIEW_VARS['searchResults']=$results;
            $VIEW_VARS['currentActiveEpisode']=1;
            
            include_once($this->DOOMA_GLOBALS["siteRoot"] . "/views/EpisodePage.php");
        }
        
        public function login() {
            $VIEW_VARS = $this->VIEW_VARS;
            
            $errors='';
            if( isset($_REQUEST['submit']) ) {
                if( isset($_REQUEST['username']) && isset($_REQUEST['password']) ){
                    include_once($this->DOOMA_GLOBALS["siteRoot"] . "/controller/Login.php");
                    $login = new Login( $this->db );
                    
                    if(  $login->validateUser( $_REQUEST['username'], $_REQUEST['password'] )  ) {
                        if( isset($_COOKIE['DOOMA_SESSION']) ) {
                            $login->removeSession( $_COOKIE['DOOMA_SESSION'] );
                        }
                        header("Location: " . $VIEW_VARS['siteUrl']);
                        $login->createSession( $_REQUEST['username'], $_SERVER['REMOTE_ADDR'] );
                    }
                    else {
                        $errors="Invalid username or password!!!";
                    }
                }
            }
            #error message
            $VIEW_VARS['loginError']=$errors;
            include_once($this->DOOMA_GLOBALS["siteRoot"] . "/views/Login.php");
        }
        
        public function logout() {
            
            if( isset($_COOKIE['DOOMA_SESSION']) ) {
                include_once($this->DOOMA_GLOBALS["siteRoot"] . "/controller/Login.php");
                $login = new Login( $this->db );
                
                $login->removeSession( $_COOKIE['DOOMA_SESSION'] );
            }
            
            $this->index();
        }
        
        public function register() {
            $VIEW_VARS = $this->VIEW_VARS;
            
            include_once($this->DOOMA_GLOBALS["siteRoot"] . "/controller/Register.php");
            
            $register = new Register();
            $errors = '';
            
            if( isset($_REQUEST['submit']) ) {
                if( isset($_REQUEST['username']) && isset($_REQUEST['password']) ){
                    $userName   = $_REQUEST['username'];
                    $password   = $_REQUEST['password'];
                    $birthDate  = $_REQUEST['birthyear'] 
                                    . "-" . $_REQUEST['birthmonth']
                                    . "-" . $_REQUEST['birthday'];
                    $gender     = $_REQUEST['gender'];
                    
                    if(  !$register->userNameExists( $this->db, $userName )  ) {
                        $register->createAccount( $this->db, $userName, $password, $birthDate, $gender );
                        
                        include_once($this->DOOMA_GLOBALS["siteRoot"] . "/controller/Login.php");
                        $login = new Login();
                        
                        header("Location: " . $VIEW_VARS['siteUrl']);
                        $login->createSession( $this->db, $userName, $_SERVER['REMOTE_ADDR'] );
                    }
                    else {
                        $errors .= "The user name you are trying to create has already exists";
                    }
                }
            }
            
            $VIEW_VARS['captchaHtml']=$register->getCaptcha();
            $VIEW_VARS['registerError']=$errors;
            
            include_once($this->DOOMA_GLOBALS["siteRoot"] . "/views/Register.php");
        }
        
        public function index() {
            $VIEW_VARS = $this->VIEW_VARS;
            
            include_once($this->DOOMA_GLOBALS['siteRoot'] . "/models/Video.php");
            
            $video = new Video($this->db);
            $VIEW_VARS['videoList'] = $video->findAllByUpdateTime();
            
            include_once($this->DOOMA_GLOBALS["siteRoot"] . "/views/IndexPage.php"); 
        }        
        
        public function main() {
            if(isset($_REQUEST['ajaxRequest'])) {
                switch ($_REQUEST['ajaxRequest']) {
                    case 'searchSuggestion':
                        $this->searchSuggestion();
                    break;
                }
                return ;
            }
            
            //handling sessions
            
            if( isset($_COOKIE['DOOMA_SESSION']) ) {
                include_once($this->DOOMA_GLOBALS['siteRoot'] . "/controller/Login.php");
                $login = new Login( $this->db );
                
                if( $sessionRow = $login->validateSession( $_COOKIE['DOOMA_SESSION'] ) ) {
                    include_once($this->DOOMA_GLOBALS['siteRoot'] . "/models/Users.php");
                    $users = new Users( $this->db );
                    $this->VIEW_VARS['user_data'] = $users->findUserByUserId( $sessionRow['userid'] );
                }
                else {
                    $login->removeSession( $_COOKIE['DOOMA_SESSION'] );
                }
                
            }
            
            if (isset($_REQUEST['action'])) {
                switch ($_REQUEST['action']) {
                    case 'searchResult':
                        $this->searchResult();
                    break;
                    case 'episode':
                        $this->episode();
                    break;
                    case 'login':
                        $this->login();
                    break;
                    case 'logout':
                        $this->logout();
                    break;
                    case 'register':
                        $this->register();
                    break;
                }
            }
            else {
                $this->index();
            }
        }
    }
?>