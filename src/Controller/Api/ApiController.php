<?php


namespace src\Controller\Api;


use App\Models\User\UserModel;
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

        if ($this->errors) {
            $result = ['error' => $this->errors];
        }


        if (error_get_last()) {
            return $response->withJson(['error' => error_get_last()]);
        }

        if (ErrorApi::$error) {
            return $response->withJson(ErrorApi::$error);
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

//        if(array_key_exists('token', $post) && $post['token']){
////            if(!$this->checkToken($post['token'])){
////                $this->error("Undefined token");
////                return false;
////            }
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

    /**
     * @param string $token
     * @param int $userId
     * @return bool
     */
    protected function checkToken(string $token, int $userId = null): bool
    {
        return checkToken($token, $userId);
    }
}