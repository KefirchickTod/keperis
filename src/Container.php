<?php


namespace src;


use App\BCApi;
use src\Controller\User\Auth;
use src\Core\Page\PageCreator;
use src\Http\Environment;
use src\Http\Headers;
use src\Http\Request;
use src\Http\Response;
use src\Http\ServerData;
use src\Http\Session;
use src\Router\Router;
use src\Structure\Structure;
use Exception;
use Psr\Container\ContainerInterface;

/**
 * Class Container
 * @package App\src
 * @property Environment        $env
 * @property Collection         $setting
 * @property Session            $session
 * @property Request            $request
 * @property Structure          $structure
 * @property PageCreator        $pageCreator
 * @property BCApi              $api
 * @property Response           $response
 * @property CallableResolver   $callableResolver
 * @property Router             $router
 */
final class Container extends \Pimple\Container implements ContainerInterface
{
    private $defaultSettings = [
        'displayErrorDetails' => true,
        'httpVersion'         => '1.1',
        'responseChunkSize'   => 4096,
        'api' => []//todo
    ];


    public function __construct(array $values = [])
    {
        parent::__construct($values);

        $userSettings = isset($values['setting']) ? $values['setting'] : [];
        //var_dump($userSettings);
        $this->registerDefaultServices($userSettings);
    }

    public function registerDefaultServices($userSetting)
    {

        $defaultSettings = $this->defaultSettings;

        $this['setting'] = function () use ($userSetting, $defaultSettings) {
            //debug(array_merge($userSetting, $defaultSettings));
            return new Collection(array_merge($userSetting, $defaultSettings));
        };

        if(!isset($this['session'])){
            $this['session'] = function (){
                return new Session();
            };
        }

        if (!isset($this['serverdata'])) {
            $this['serverdata'] = function () {
                return new ServerData($_SERVER);
            };
        }
        if (!isset($this['request'])) {
            $this['request'] = function ($c) {
                return Request::creatFromServerData($c->get('serverdata'));
            };
        }
        if (!isset($this['structure'])) {
            $this['structure'] = function () {
                return \structure();
            };
        }
        if (!isset($this['pageCreator'])) {
            $this['pageCreator'] = function ($c) {
                return new PageCreator($c->get('structure'), []);
            };
        }
        if (!isset($this['router'])) {
            $this['router'] = function () {
                return new Router();
            };
        }
        if (!isset($this['response'])) {
            $this['response'] = function ($c) {
                $headers = new Headers(["Content-Type" => 'text/html; charset=UTF-8']);
                $response = new Response(200, $headers);
                return $response->withProtocolVersion($c->get('setting')->get('httpVersion'));
            };
        }
        if(!isset($this['env'])){
            $this['env'] = function ($c){
                return Environment::mock($_ENV);
            };
        }

        if (!isset($this['api'])) {
            $this['api'] = function () {
                return new BCApi();
            };
        }

        if (!isset($this['callableResolver'])) {
            $this['callableResolver'] = function ($c) {
                return new CallableResolver($c);
            };
        }
        if(!isset($this['auth'])){
            $this['auth'] = function (){
                return new \App\Controller\User\Auth();
            };
        }

    }

    public function __get($name)
    {
        try {
            return $this->get($name);
        } catch (Exception $e) {
            error_log($e->getMessage());
            die();
        }
    }

    /**
     * @param string $id
     * @return mixed|void
     * @throws \RuntimeException
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            error_log(sprintf('Identifier "%s" is not defined.', $id));
            throw new \RuntimeException(sprintf('Identifier "%s" is not defined.', $id));
        }
        return $this->offsetGet($id);
    }

    public function has($id)
    {
        return $this->offsetExists($id);
    }

    public function __isset($name)
    {
        return $this->has($name);
    }
}