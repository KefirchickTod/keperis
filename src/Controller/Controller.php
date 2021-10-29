<?php


namespace src\Controller;


use App\Provides\Mask;
use src\Container;
use src\Core\Page\PageCreator;
use src\Core\Page\ProvideTable;
use src\Http\Request;
use src\Http\Response;
use src\Models\Model;
use src\Structure\ProvideFilter;
use src\Structure\Structure;


abstract class Controller
{

    /**
     * @var Structure
     */
    public $structure;
    public $authentification = true;
    protected $role = null;
    protected $exportTitle = null;
    /**
     * @var Container
     */
    protected $container;
    /**
     * @var ProvideFilter
     */
    protected $filter;
    /**
     * @var Mask
     */
    protected $mask;
    /**
     * @var Model
     */

    private $ajax = false;

    function __construct(Container $container)
    {

        $this->container = $container;
        $this->structure = $this->container->get('structure');

    }


    /**
     * @param Model $model
     * @return $this
     */
    public function withModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function __invoke(Response $response, Request $request = null)
    {

        if ($this->isAjax() && $this->ajax === false) {
            return $request->isXhr() ? $this->ajax($response, $this) : $response;
        }

        return $response;

    }

    public function isAjax() //todo remove to request->isXhr();
    {

        return $this->container->get('request')->isXhr();

        //return valid($_SERVER, 'HTTP_X_REQUESTED_WITH', 'no') === 'XMLHttpRequest';
    }

    public function ajax(Response $response, Controller $controller)
    {
        PageCreator::$script = false;
        $controller->filter = [];
        // debug($this->action);
        $json = $controller->render(null, false);
        $json['success'] = true;

        /** @var  $table ProvideTable */
        $table = $controller->table;
        $json['row'] = $table->getRow();
        $this->ajax = true;
        return $response->withJson($json);
    }

    protected function prepare(Mask $mask)
    {
        $this->filter = new ProvideFilter();
        $this->mask = $mask;

    }


}