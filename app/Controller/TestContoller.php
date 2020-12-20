<?php


namespace App\Controller;


use App\Models\TestModel;
use src\Container;
use src\Controller\Controller;
use src\Core\Page\PageCreator;
use src\Model;
use src\Traits\TableTrait;

class TestContoller extends Controller
{

    use TableTrait;

    /**
     * @var Model
     */
    private $model;

    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->model= new TestModel();
    }

    public function index(){
        $this->prepare($this->model->getMask());
        $this->dataArray = $this->model->getMask()->getMask('test');
        $this->title = $this->model->getMask()->getTitle('test');
        PageCreator::init($this->dataArray);
        $this->init();

        $table = $this->render(null, true);

        return view('');
    }
}