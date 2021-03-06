<?php


namespace App\Api;



use src\Container;
use src\Controller\Controller;
use src\Http\Request;
use src\Http\Response;

class ApiController extends Controller
{


    const NON_ARRAY = 'convrt';
    protected $token;
    protected $post;
    protected $errors = [];
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var Request
     */
    protected $request;

    public function __construct()
    {
        parent::__construct(\container());
        $this->authentification = false;
    }

    public function run(Request $request, Response $response, $method)
    {
        $this->response = $response;
        $this->request = $request;
        $this->post = $request->getParsedBody();
        if (!$this->valid($this->post)) {
            return $response->withJson(['error' => $this->errors]);
        }

        $result = [];
        if (is_callable([$this, $method])) {
            $result = $this->callbackMethod($method);
        }




        return $response->withJson($result);
    }

    public function valid($post)
    {

        if (is_string($post) && $this->isJson($post)) {
            $post = json_decode($post, true);

        }
        if (is_string($post) && unserialize($post)) {
            $post = unserialize($post);
        }

        if (!is_array($post)) {
            $this->error('Undefined type of post');
            $post = [$post];
        }

//
//        if (array_key_exists('token', $post)) {
//            $this->token = $post['token']; //todo
//            if (!$this->checkToken($this->token)) {
//                $this->error("Undefined token");
//                return false;
//            }
//        }
        $this->post = $post;
        return true;

    }

    protected function isJson($string)
    {
        return is_string($string) && is_array(json_decode($string,
            true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    protected function error($massage, $type = null)
    {
        if ($type) {
            $this->errors[$type] = $massage;
        } else {
            $this->errors[] = $massage;
        }
    }

    /**
     * @param string $token
     * @return bool
     */
    protected function checkToken(string $token): bool
    {
        return checkToken($token);
    }

    protected function callbackMethod($method)
    {
        $result = call_user_func([$this, $method], $this->post);
        if ($result instanceof Response) {
            return $result;
        }

        if (!is_array($result)) {
            $result = [self::NON_ARRAY => $result];
        }
        return $result;

    }
}