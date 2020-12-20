<?php


namespace src\Core\Page;


use src\Core\easyCreateHTML;
use src\Interfaces\Buttons;
use src\Structure\Structure;

class PageCreateButtons implements Buttons
{
    public $length = [
        '10',
        '20',
        '50',
        '100',
    ];
    /**
     * @var Structure
     */
    private $structure;
    /**
     * @var array
     */
    private $dataArray;
    private $setting = null;

    public function __construct()
    {
        $this->setting = [
            'col-lg-8 col-md-12 col-12 insertMargin-5px' => join('', [
                $this->getLength(get('length', '10')),
                (PageCreator::$export_allow === true ? $this->getExport() : ''),
                $this->getDeleteFilter(),
            ]),
            'col-lg-4 col-md-12 col-12 search-div'       => $this->getSearch(get('search', '') !== '-1' ? get('search', '') : ''),
        ];
    }

    public function getLength($default = '')
    {
        $result = "<select onchange='setTableLength(this.value)' class='dt-button ui-button ui-state-default ui-button-text-only' id = 'lengthSend'>";
        foreach ($this->length as $length) {
            $result .= "<option value='$length' " . ($length == $default ? 'selected' : '') . ">$length</option>";
        }
        return $result . '</select>';
    }

    public function getExport($default = '', $link = '')
    {
        return html()->a
        (
            [
                'class' => 'dt-button ui-button ui-state-default ui-button-text-only',
                'href'  => "javascript:export_xlsx()",
                'role'  => 'button',
                'text'  => 'Export',
            ]
        )->end('a');

    }

    public function getDeleteFilter()
    {
        return html()->a
        (
            [
                'class' => 'dt-button ui-button ui-state-default ui-button-text-only',
                'href'  => "javascript:deleteFilter()",
                'role'  => 'button',
                'text'  => 'Скинути фільтри',
            ]
        )->end('a');
    }

    public function getSearch($default = '')
    {
        return html()->form(['class' => 'form-inline'])
            ->div(['class' => 'form-group has-search'])
            ->span([
                'class' => 'fa fa-search form-control-feedback',
                'style' => 'display: block; margin-top: 0',
            ])->end('span')
            ->input([
                'class'       => 'form-control',
                'name'        => 'search',
                'type'        => 'text',
                'placeholder' => 'Search',
                'aria-label'  => 'Search',
                'id'          => 'autoSend',
                'value'       => $default,
            ])
            ->end('div')
            ->end('form')->render(true);
    }

    public function setting(array $setting)
    {
        $this->setting = $setting;
    }

    public function addSetting($key, $value)
    {

        if (isset($this->setting[$key])) {
            $this->setting[$key] .= $value;
        } else {
            $this->setting[$key] = $value;
        }
        return $this;
    }

    public function getGroupEvent()
    {
        return html()->button(
            [
                'type'    => 'button',
                'class'   => 'dt-button ui-button ui-state-default ui-button-text-only',
                'onclick' => 'removeIds()',
                'style' => 'margin-bottom:0;'
                //'text'        => 'Групове запрошення',
            ]
        )->span(['class' => 'ui-button-text', 'text' => 'Скинути групову дію'])->end('span')->end('button')->input([
            'type'  => 'hidden',
            'value' => '-1',
            'id'    => 'usersIdBank',
        ]);
    }

    public function setData(Structure $structure, array $dataArray)
    {
        $this->structure = $structure;
        $this->dataArray = $dataArray;
    }

    public function groupInvation()
    {
        return html()->button(
            [
                'type'        => 'button',
                'class'       => 'dt-button ui-button ui-state-default ui-button-text-only',
                'id'          => 'ButtonGroupInvite',
                'style'       => 'margin-bottom: 0;',
                'data-toggle' => 'modal',
                'data-target' => '#GroupInvite',
                //'text'        => 'Групове запрошення',
            ]
        )->span(['class' => 'ui-button-text', 'text' => 'Групове запрошення'])->end('span')->end('button');
    }

    public function render(): string
    {
        $result = '';
        $result .= "<div class='row table_buttons_action'>";
        foreach ($this->setting as $class => $value) {
            $result .= "<div class='$class'>$value</div>";
        }
        $result .= "</div>";
        return $result;
    }


}