<?php
return [
    'page' => [
        'table'              => [
            'attrTable' => 'class = "table table-main" id= "resize-table"',
            'thead'     => 'class ="thead-light"',
        ],
        'paginator'          => [
            '{%_counter_%}'      => '<li class="btn-group__item "><button class="btn btn--basic paginator-fetch  {%_current_%} " data-page="{%_value_%}">{%_value_%}</button></li>',
            '{%_styleForLast_%}' => 'btn btn--basic paginator-fetch',
            '{%_current_%}'      => 'current',
        ],
    ],
];