<?php
return [
    'page' => [
        'table'              => [
            'attrTable' => 'class = "table responsive-full filter-table table-striped table-bordered table-sm eventFirst" style = "font-size: 0.9em; table-layout: fixed; margin-bottom:2px; height:max-content;"',
            'thead'     => 'class ="thead-light"',
        ],
        'paginator'          => [
            '{%_counter_%}'      => '<li class="btn-group__item "><button class="btn btn--basic  {%_current_%} " onclick="paginator(\'{%_value_%}\')">{%_value_%}</button></li>',
            '{%_styleForLast_%}' => 'btn btn--basic',
            '{%_current_%}'      => 'current',
        ],
        'userSearch'         =>
            [
                'title' =>
                    [
                        'fullName' =>
                            [
                                'text' => "Ім'я та прізвище",
                                'sort' => true,
                            ],
                        'mobile'   =>
                            [
                                'text' => 'Телефон',

                            ],
                        'email'    =>
                            [
                                'text' => "E-mail",

                            ],
                        'city'     =>
                            [
                                'text' => "Місто",

                            ],
                        [
                            'text' => 'Додати',
                        ],
                    ],
                'data'  => [
                    'userSearch' =>
                        [
                            'get'     =>
                                [
                                    'firstName',
                                    'secondName',
                                    'fullName',
                                    'mobile',
                                    'email',
                                    'city',
                                    'reverseFullName',
                                    'email',
                                    'email2',
                                    'email3',
                                    'loyalty',
                                    'reputation',
                                ],
                            'class'   => 'bcUser',
                            'setting' =>
                                [
                                    'where' => 'bc_user_delete <> 1 ',
                                ],
                        ],
                ],
            ],
        'tableWithoutFilter' =>
            [
                'attrTable' => 'class = "table table-striped table-bordered table-sm " style = "font-size: 0.9em;  table-layout: fixed;  margin-bottom:2px"',
                'thead'     => 'class ="thead-light"',
            ],
        'minifile'           =>
            [
                'js'  => [
//                    '/app/js/autosize.min.js',
//                    '/app/js/freeze-table.js',
//                    "/app/public/js/animsition.min.js",
//                    "/app/js/modernizr-2.6.2.min.js",
                    //"/app/public/js/custom.js",
                    "/js/lib/function.js",
                    "/js/lib/test.js",
                    "/js/lib/GroupEvent.js",

                    "/js/lib/SWEditor.js",
                    "/js/lib/search.js",
                    "/js/lib/doubleScroll.js",
                    "/js/lib/api.js",
                    "/js/lib/api_function.js",
                    "/js/lib/dragscroll.js",
                  //  "/app/js/Notes.js",
                    "/app/public/js/module.js",
                ],
                'css' => [
                    '/css/css.css',
                    '/css/table.css'
                ],
            ],

    ],
    'session' =>
    [
        'htmlTemp'  => "        <div class='alert {%_cssClass_%} alert-dismissible fade show' role='alert'>
            <strong>{%_statusText_%}</strong>
            <span>{%_massage_%}</span>
            <button type='button' class='close closeAlert'>
                <span aria-hidden='true'>&times;</span>
            </button>
        </div>"
    ],

];