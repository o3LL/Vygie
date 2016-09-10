<?php

class DataModel
{
    /**
     * DataReport constructor.
     */
    function __construct()
    {
        $this->basedir = dirname(__FILE__) . '/';
        //init db object
        require_once BASEPATH . 'models/Dbconnect.php';
        $this->db = new db();
        $this->error = false;
        $this->settings = [];
        $this->settings['basepath'] = '/alert/';
        $this->search = '';
        $this->errorMsg = [];

        //init auth object
        require_once 'Auth.php';
        $this->auth = new Auth();


    }

    /**
     * @param $method
     * @param $arg
     * @return string
     */
    public function execute($method, $arg)
    {

        //export to twig
        $pagedata['formdata'] = $_REQUEST;
        $pagedata['userdata'] = $this->auth->user;
        $pagedata['settings'] = $this->settings;
        $pagedata['user'] = $this->auth->user;
        $pagedata['reportfromsearch'] = $_REQUEST['fromsearch'];



        switch ($method) {
            case '404' :
                $pagedata['results'] = '';
                break;

            case 'subscribe' :
                $data = $this->auth->verifUser($pagedata['formdata']);
                if ($data) {
                    $data = $this->auth->newUser($pagedata['formdata']);
                    $pagedata['results'] = $data;
                }else{

                }
                break;

            case 'logout' :
                $this->auth->disconnect();
                $pagedata['results'] = '';
                header ('location: '. $this->settings['basepath'] );
                break;

            case 'login' :
                $login = $this->login($pagedata['formdata']);
                if ($this->auth->user == false) {

                } else {
                    $this->auth->createCookie($login);
                    header('Location: ' . $this->settings['basepath'] . '');
                }
                break;

        }


        switch ($arg['format']) {
            case 'json' :
                $data = json_encode($pagedata['results']);
                break;
            case 'html' :
                $data = $this->parseTwig($pagedata, $arg['view']);
                break;
            default :
                //$data = $pagedata;
                $data = $this->parseTwig($pagedata, $arg['view']);
                break;
        }
        
        return $data;

    }


    /**
     * @param $formdata
     * @return bool|string
     */
    public function login($formdata)
    {
        if (isset($formdata['pseudo']) && isset($formdata['password']))
        {
            $check = $this->auth->checkLogin($formdata);
        }

        if (@$check) {
            $token = $this->auth->createSessionToken();
            return $token;
        } else {
            return false;
        }
    }

    /**
     * @param $data
     * @return array
     */
    public function post($data) 
    {
        return $array;
    }

    public function parseTwig($data, $view)
    {
        $loader = new Twig_Loader_Filesystem('view/');
        $twig = new Twig_Environment($loader, array(
            'cache' => false,
            'debug' => true
        ));
        $twig->addExtension(new Twig_Extension_Debug());
        return $twig->render($view . '.twig', $data);
    }

    /**
     * Function addReport($report)
     *
     * @param (array) $report = from form
     * @return (bool) true if it works
     */
    public function addReport($exec)
    {
        if ($this->reportExist($exec)) {
            $this->error = true;
            $this->errorMsg[] = 'Doublon !!!';
        } else {
            $sql = "INSERT INTO " . $this->report . "(`country`, `number`, `type`, `date`, `resume`, `author_id`, `json`) VALUES (:country, :number, :type, NOW(), :resume, :author_id, :json);";
            $result = $this->db->selectSQL($sql, $exec);
        }
        return $result;
    }

    /**
     * Function reportExist
     * @param  [type] $sql [description]
     * @return [type]      [description]
     */
    private function reportExist($array)
    {
        $number = array("number" => $array['number']);
        $sql = "SELECT * FROM " . $this->report . " WHERE number = :number";
        $result = $this->db->selectSQL($sql, $number);
        if ($result == array()) {
            $this->error = array("doublon" => false);
            return false;
        } else {
            $this->error = array("doublon" => true);
            return true;
        }
    }

    public function checkAuth($arg, $cookie)
    {
        $res = '';
        if (isset($arg['pseudo']) && isset($arg['password']))
        {
            $res = $this->login($arg);
        }
        elseif (isset($cookie['token']))
        {
            $res = $this->auth->checkSessionToken($cookie['token']);
        }
        return $res;
    }

}

?>