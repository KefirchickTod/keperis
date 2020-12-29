<?php


namespace Xlsx\Import\Renderer;


use src\Core\Page\Table\ProvideTableContainer;
use src\Xlsx\Import\Parse\Parser;
use src\Xlsx\Import\Renderer\RenderByCache;

class RenderNewNote extends RenderByCache
{

//    protected function initTable()
//    {
//        parent::initTable();
//        $container = new ProvideTableContainer();
//        $container->execute(
//            $this->table->container()->setting, $this->table->container()->title, $this->table->container()->filter, [
//                'icon' => [
//                    'fa fa-plus'
//                ],
//                'link' => [
//                    '/import/user/add'
//                ],
//                'id' =>true,
//                'callback' => [
//                    $this->getCallbackAction()
//                ]
//        ]);
//    }
//    protected  function getCallbackAction()
//    {
//        $callback = function ($action, $row, $param) {
//            if (array_key_exists(Parser::PARSER_MARKER, $row)) {
//                $json = json_encode($row);
//                return html()->a([
//                    'text'      => '<i class="fa fa-plus"></i>',
//                    'data-json' => $json,
//                    'href'      => '#',
//                ])->render(true);
//
//            }
//        };
//
//        return $callback;
//    }
}