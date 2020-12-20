<?php


namespace src\Core\Page;


use src\Core\ApostropheCreat;
use src\Core\Filtration\DateFilter;
use src\Core\provideExport;
use src\Interfaces\PageInterface;
use src\Models\Event\EventModel;
use src\Models\Opportunities\OpportunitiesModel;
use src\Structure\bcProvideSearch;
use src\Structure\Structure;
use src\Interfaces\Buttons;
use src\Interfaces\Table;
use src\Interfaces\Paginator;
use Closure;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PageCreator extends Creator
{

    const default_length = 10;

    public static $script = true;
    public static $getting = [];
    public static $title;
    public static $export_title = '';
    public static $export_allow = true;
    /**
     * @var Closure
     */
    public static $row_init;
    /**
     * @var PageInterface
     */
    public $current;

    public function __construct(Structure $structure, array $dataArray)
    {
        parent::__construct($structure, $dataArray);
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) && self::$script == true) {
            echo "
            <script>
            if(typeof data_array == 'undefined'){
                var data_array = '" . base64_encode(serialize($this->dataArray)) . "'
            }
               
            </script>
            ";
        }
    }

    /**
     * @param $dataArray
     * @param null $callback
     * @param bool $position
     * @throws Exception
     */
    public static function init(&$dataArray, $callback = null, $position = true, $getting = [])
    {


        $name = key($dataArray);
        $get = $getting ?: (self::$getting ?: $_GET);
        $where = [];
        $filter = new DateFilter();
        if ($callback && $position == true) {
            call_user_func_array($callback, [&$dataArray, &$filter, &$get]);
        }

        if (valid($get, 'sort')) {
            if (isset($dataArray[$name]['setting']['order'])) {
                unset($dataArray[$name]['setting']['order']);
            }
            $join = isset($dataArray[$name]['setting']['join']) ? array_keys($dataArray[$name]['setting']['join']) : null;
            creatSort($dataArray, $name, $join, $get);
        }

        if (valid($get, 'from') && valid($get, 'to')) {
            $parent = valid($get, 'parent');
            $where[] = $filter->setDataStructure($dataArray)->parse()->creatFromTo($get['from'], $get['to'], $parent);

        }
        if (valid($get, 'filter')) {
            PageCreatePaginator::$distinct = array_keys(json_decode($get['filter'], true));
            $where[] = $filter->setDataStructure($dataArray)->parseFilter(json_decode($get['filter']))->setDelimtr(' AND ')->getResult();
        }
        if (valid($get, 'search', '-1') != '-1') {

            $search = new bcProvideSearch();
//            $search2 = new bcProvideSearch();
            $apostophe = new ApostropheCreat();
            $searchValue = str_replace('"', "'",$search->cleanSearch(trim($get['search'])));

            $apostopheValue = $apostophe->setStr($searchValue)->render();
            if($apostopheValue !== $searchValue){
                $where[] = join(" OR ", [
                    $search->setDataStructure($dataArray)->setSearch($searchValue)->parse()->creatQuery()->getWhere(),
                    $search->withSearch(addslashes($apostopheValue))->parse()->creatQuery()->getWhere()
                ]);

               // echo $test ."<br><br>$test1";exit;
            }else{
                $where[] = $search->setDataStructure($dataArray)->setSearch($search->cleanSearch(trim($get['search'])))->parse()->creatQuery()->getWhere();
            }

        }

        $limit = PageCreatePaginator::limit((int)valid($get, 'page', 1), (int)valid($get, 'length', self::default_length));

        $dataArray[$name]['setting']['limit'] = $limit;
        $where = $where ? join(' AND  ', $where) : null;
//        var_dump($where, $get);
        if ($where) {

            addToArray($dataArray[$name]['setting'], 'where', $where, ' AND ');
        }
        if (valid($get, 'event_outer_id') != 0 || valid($get, 'grant_outer_id') != 0) {
            $structure = new Structure();
            $name = key($dataArray);
            if (!$structure->isEmpty($name)) {
                $structure->delete($name);
            }
            $size = (new PageCreatePaginator());
            $structure->set($dataArray);
            $size->setData($structure, $dataArray);
            $size = $size->size();

            if($size > 300){
                session()->error('Перевищоно кількість вибраних персон на '.($size - 300) . '(Помилка 1)', 'group.invate.error');
                return;
            }
            $groupInviteIdList = $structure->get($name);
            $outerId = array_diff([
                EventModel::class         => get('event_outer_id'),
                OpportunitiesModel::class => get('grant_outer_id'),
            ], [null, 'undefined', '0']);
            $size = sizeof($groupInviteIdList);
            foreach ($outerId as $class => $id) {

                if ($id && ($size <= 300)) {
                    $userIds = array_column($groupInviteIdList, 'bc_user_id');
                    if(!empty($userIds)){
                        foreach ($userIds as $userId){
                            if(method_exists($class, 'checkUserRegistered') && $class::checkUserRegistered($id, $userId) === '0'){
                                $class::registerUser($id, $userId, true);
                            }
                        }
                    }else{
                        session()->error('Немає користувачів для додавання (Помилка 3)', 'group.invate.error');
                    }

                    // $this->router->redirect($this->router->getFullUrl());
                }else{
                    session()->error('Перевищоно кількість вибраних персон на '.($size - 300).'(Помилка 2)', 'group.invate.error');
                    return;//http://crm.bc-club.local/user/list?search=-1&filter={%22area%22:%222,10,7,23,6%22,%22titleUK%22:%22member_base%22}
                }
            }
        }
        if ($callback && $position == false) {
            call_user_func_array($callback, [&$dataArray, &$filter, &$get]);
        }
        if (valid($get, 'export') && self::$export_allow === true) {
           // debug($dataArray);

            //debug($da);
            //debug($dataArray, self::$title, self::$export_title);
            $data = provideExport::exportTable($dataArray, self::$title, self::$export_title,
                PageCreator::$row_init);

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename=' . $data[1] . ".xlsx");
            header('Cache-Control: max-age=0');
            $file = $data[0];

            $file = IOFactory::createWriter($file, ucfirst('xlsx'));
            $file->save('php://output');
//            return     $response->withHeader('Content-Type', 'application/vnd.ms-excel')
//                ->withAddedHeader('Content-Disposition', 'filename=' . $data[1] . ".xlsx")
//            ->withAddedHeader('Cache-Control', 'max-age=0');
            die;
        }
        //var_dump($dataArray);
    }


    public function getButtons(): Buttons
    {
        $this->current = new PageCreateButtons();
        return $this->current;
    }

    public function getPaginator(): Paginator
    {
        $this->current = new PageCreatePaginator();
        return $this->current;
    }

    public function getTable(): Table
    {
        $this->current = new ProvideTable();
        return $this->current;
    }


}