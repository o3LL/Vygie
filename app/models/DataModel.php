<?php

class DataModel
{
    /**
     * DataReport constructor.
     */
    function __construct()
    {

        $this->basedir = BASEPATH;
        //init db object
        require_once BASEPATH . '/app/models/Dbconnect.php';
        $this->db = new Dbconnect(BASEPATH . "/test.ini");
        $this->error = false;
        $this->settings = [];
        $this->settings['basepath'] = BASEPATH. '/';
        $this->search = '';
        $this->errorMsg = [];
        $this->databases = [
            "data" => "DATA_POST",
            "ecoles" => "dbecole"
        ];

        //init auth object
        require_once BASEPATH . '/app/models/Auth.php';
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
        //$pagedata['reportfromsearch'] = $_REQUEST['fromsearch'];



        switch ($method) {
            case '404' :
                $pagedata['results'] = '';
                break;

            case 'index' :
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

            case 'api_add':
                $pagedata['results'] = $this->add($arg['data']);
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
    public function add( $data ) 
    {
        $sql = "INSERT INTO " . $this->databases['data'] . "(`date_request`, `type`, `send_from`) VALUES (NOW(), :type, :send_from);";
        $exec = [
            "type" => $data["disease"],
            "send_from" =>  $data["id"]
        ];
        $result = $this->db->selectSQL($sql, $exec);
        if ($result != false) {
        	$return = 'Merci d\' avoir signalé ' . $data['disease'];
        } else {
        	$return = 'Erreur.';
        }
        return $return;
    }

    public function parseTwig($data, $view)
    {
        $loader = new Twig_Loader_Filesystem(BASEPATH . '/app/views');
        $twig = new Twig_Environment($loader, array(
            'cache' => false,
            'debug' => true
        ));
        
        $twig->addExtension(new Twig_Extension_Debug());
    
        return $twig->render($view . '.twig', $data);
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