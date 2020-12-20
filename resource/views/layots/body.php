

<head>
    <script>
        <?php if(isset($title) && !empty($title)) : ?>
        if (typeof title != 'object') {
            var title = <?= json_encode(array_keys($title)) ?>;
        }
        <?php endif; ?>
        <?php if(isset($data_array) && !empty($data_array)) : ?>
        if (typeof data_array != 'object') {
            var data_array = <?= json_encode($data_array) ?>;
        }
        <?php endif; ?>
    </script>

    <?= resource('include.head')->render() ?>
</head>
<!--<body class="nav-sm animsition" style="height: 100%">-->
<body class="nav-sm animsition" style="height: 100%">
<div class="arrow_top_container">
    <div class="arrow_top">
        <div class="arrow">
            <i id="arrow" class="fa fa-arrow-circle-up"></i>
        </div>
    </div>
</div>
<?php /*$menu*/ ?>

<div class="alert alert_fixed alert-success alert-dismissible fade successMassage" role="alert">
    <strong>Успішно!</strong> <span id="successMassage"></span>
    <button type="button" class="close closeAlert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="alert  alert_fixed alert-danger alert-dismissible fade errorMessageParent" role="alert">
    <strong>Помилка!</strong> <span id="errorMassage"></span>
    <button type="button" class="close closeAlert">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="container body">
    <div class="main_container" style="height: 100%">
        <div class="col-md-3 left_col menu_fixed " style="/*max-height: 100%; overflow-y: scroll; overflow-x: hidden*/">
            <div class="left_col scroll-view mcs_container col_fixed col">
                <div class="navbar nav_title" style="border: 0;">
                    <a href="/" class="site_title"><img src="https://bc-club.org.ua/bctemplate/img/logo3.png" width="50"
                                                        height="50" alt="">
                        <span>Gentelella Alela!</span></a>
                </div>

                <div class="clearfix"></div>

                <!-- menu profile quick info -->
                <div class="profile clearfix">
                    <div class="profile_pic">
                        <?= getUserName(current_user_id()) ?>
                    </div>
                    <div class="profile_info">
                        <span>Welcome,</span>
                        <h2>John Doe</h2>
                    </div>
                </div>
                <!-- /menu profile quick info -->

                <br/>

                <!-- sidebar menu -->
                <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                    <div class="menu_section customScrollBox">
                        <h3>General</h3>
                        <ul class="nav side-menu" id="style-20">
                            <li><a href="<?= route('user.list') ?>"><i class="fa fa-user"></i>Персони<span
                                            class="fa fa-chevron-down"></span></a></li>
                            <?php if (role_check('applications.list.view')): ?>
                                <li><a href='/user/application?filter={"userApproved":"1,2"}'><i class="fa fa-handshake-o"></i> Заявки на
                                        участь <span
                                                class="fa fa-chevron-down"></span></a>
                                </li>
                            <?php endif; ?>
                            <li><a href="/event/list"><i class="fa fa-calendar"></i> Події <span
                                            class="fa fa-chevron-down"></span></a>
                            </li>
                            <li><a href='/opportunities?search=-1&filter={"grantStatus":"3","grantShowOnSite":"1"}'><i class="fa fa-xing"></i> Бізнес-можливості <span
                                            class="fa fa-chevron-down"></span></a></li>
                            <li><a><i class="fa fa-envelope"></i> Запити <span class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu">
                                    <li><a href="<?= route('application') ?>">Усі запити</a></li>

                                    <li><a href="<?= route('application.create') ?>">Додати запит</a></li>
                                    <?php if(\App\Models\Roles\RoleModel::isUserRolese([1,2])): ?>
                                        <li><a href="<?= route('application.setting') ?>">Налаштування</a></li>
                                    <?php endif; ?>
                                </ul>
                            </li>
                            <li><a><i class="fa fa-commenting"></i>Бізнес-координу-вання<span
                                            class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu">
                                    <li><a href="/meeting">Зустрічі</a></li>
                                </ul>
                            </li>
                            <li><a><i class="fa fa-server "></i> Інше <span
                                            class="fa fa-chevron-down"></span></a>
                                <ul class="nav child_menu" style="top: -17em;">
                                    <li><a href="/packages">Пакети</a></li>
                                    <li><a href="/roles">Ролі</a></li>
                                    <li><a href="/dictionary">Словники</a></li>



                                    <li><a href="/update/min/all" target="_blank">Оновити мініфікацію файлів</a></li>

                                </ul>
                            </li>

                        </ul>
                    </div>
                </div>
                <!-- /sidebar menu -->

            </div>
        </div>
        <!-- top navigation -->
        <div class="top_nav">
            <div class="nav_menu">
                <h2 class="titlePage"><?= $pageName ?? '' ?></h2>
                <nav class="nav navbar-nav">
                    <ul class=" navbar-right">
                        <li class="nav-item dropdown" style="padding-left: 15px;">
                            <a href="javascript:;" class="user-profile dropdown-toggle " aria-haspopup="true"
                               data-toggle="dropdown" aria-expanded="false">
                                <?= getNameById(current_user_id()) ?>
                            </a>
                            <div class="dropdown-menu dropdown-usermenu pull-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" target="_blank"
                                   href="<?= route('user.info', ['id' => current_user_id()]) ?>">
                                    Profile</a>
                                <a class="dropdown-item" href="/logout"><i class="fa fa-sign-out pull-right"></i>
                                    Log
                                    Out</a>
                            </div>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <!-- /top navigation -->
        <div class="right_col" style="min-height: 100%">

            <?php if(session()->has('group.invate.error', \src\Http\Session::ERROR)): ?>
                <div class="alert  alert_fixed alert-danger alert-dismissible fade errorMessageParent" role="alert" style="display: block;">
                    <strong>Помилка!</strong> <span id="errorMassage"><?=  session()->get('group.invate.error', \src\Http\Session::ERROR) ?></span>
                    <button type="button" class="close closeAlert">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
            <?php endif;?>

            <?php if (isset($pageName) && $pageName): ?>
                <div class="x_content">
                    <div class="x_content"><?= $content ?></div>
                </div>
            <?php else: ?>
                <?= $content ?>
            <?php endif; ?>

        </div>
    </div>
</div>
<div class="loader_container" style="display: none">
    <div class="loader">
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
        <div class="line"></div>
    </div>
</div>
<?= resource('include.modal.groupevent')->render() ?>
<script>
    let localVariable;
    let currentUserId = <?= current_user_id() ?>;
    let currentUserName = '<?= getNameById(current_user_id()) ?>';

</script>
<script>
    <?php if((isset($copyText) && $copyText === true) || current_user_id() == 1264): ?>

    <?php else: ?>
    document.ondragstart = noselect;   // заборона на перетягування
    document.onselectstart = noselect; // заборона на виділення елементів сторінки
    function noselect() {
        return false;
    }
    <?php endif; ?>

</script>
</body>