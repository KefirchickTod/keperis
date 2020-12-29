<?php


namespace src\Traits\User;


use App\BCApi;

use src\Collection;
use src\Core\User\UserAuth;

trait UserTrait
{

    /**
     * @var UserAuth
     */
    protected $auth;
    private $userId;

    public function checkPassword($user_id, $password): array
    {
        return db()->selectSql(
            'bc_user',
            '*',
            "bc_user_id = {$user_id} AND bc_user_password = '" . self::password($password) . "'"
        );
    }

    public function updatePassword($user_id, $password)
    {
        return db()->updateSql(
            'bc_user', ['bc_user_password' => self::password($password)], 'bc_user_id = ' . $user_id
        );
    }

    public function applicationStatus($user_id)
    {
        $status = db()->selectSql('bc_user', 'bc_user_approved AS status', 'bc_user_id = ' . $user_id)[0];
        return (int)$status['status'];
    }

    public function checkToken($token): bool
    {
        $sql = db()->selectSql('bc_user', 'bc_user_id', "bc_user_token = '" . $token . "'");

        if (empty($sql[0]['bc_user_id'])) {
            return false;
        }

        return true;
    }

    public function deletePhoto($user_id)
    {
        $bcapi = new BCApi();
        return $bcapi->sendRequest([
            'action'  => 'deleteUserPhoto',
            'user_id' => $user_id,
        ]);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->userId;
    }

    /**
     * @return bool
     */
    public function isLogin()
    {
        return ($this->userId && $this->userId > 0);
    }

    /**
     * @return bool
     */
    public function isSuperAdmin()
    {
        return $this->auth->isSuperAdmin();
    }


    public function all($id = 0, $img = true)
    {
//        $data = structure()->set([
//            'info' =>
//                [
//                    'get'     => 'all',
//                    'class'   => 'bcUser',
//                    'setting' =>
//                        [
//                            'where' => "id = $id",
//                        ],
//                ],
//        ])->getData(function ($row) use ($id, $img) {
//            if (valid($row, 0)) {
//                $row = $row[0];
//                if($img === true){
//                    $row['img'] = $this->img($id);
//                }
//                $row['name'] = valid($row, 'bc_user_first_name') . ' ' . valid($row, 'bc_user_second_name');
//                $row = new Collection($row);
//                return $row;
//            }
//            return [];
//        }, 'info');
        $query = "SELECT
	bcu.*,
	CONCAT_WS( ' ', ba.bc_user_secondname_uk, ba.bc_user_name_uk ) AS bc_user_ba_full_name,
	CONCAT_WS( ' ', recr.bc_user_secondname_uk, recr.bc_user_name_uk ) AS bc_user_recruiter_full_name,
	affiliate.bc_dictionary_title_uk AS bc_user_affiliate,
	bs.bc_dictionary_title_uk AS bc_user_status_in_business,
	ucn.bc_user_companies_name_uk AS bc_user_companies_1_name_uk,
	ucp.bc_user_companies_position_uk AS bc_user_companies_1_position,
	bcw1.*,
	bcw2.*,
	area.bc_dictionary_title_uk AS bc_user_area,
	city.bc_dictionary_title_uk AS bc_user_city,
	dmom.bc_dictionary_title_uk AS bc_user_mom_status,
	GROUP_CONCAT( DISTINCT dlang.bc_dictionary_title_uk SEPARATOR ', ' ) AS bc_user_languages,
	GROUP_CONCAT( DISTINCT dlangid.bc_dictionary_id SEPARATOR '|' ) AS bc_user_language_ids,
	GROUP_CONCAT( DISTINCT dtr.bc_dictionary_title_uk SEPARATOR ', ' ) AS bc_user_trainings,
	GROUP_CONCAT( DISTINCT dtrid.bc_dictionary_id SEPARATOR '|' ) AS bc_user_training_ids,
	ist.bc_dictionary_title_uk AS bc_user_internal_status,
	country.bc_dictionary_title_uk AS bc_user_country,
	sex.bc_dictionary_title_uk AS bc_user_sex,
	marrige.bc_dictionary_title_uk AS bc_user_marrige,
	subord.bc_dictionary_title_uk AS bc_user_subordinates,
	bp.bc_packages_title_uk AS bc_user_package_code_title,
IF
	(
		bcu.bc_user_package_code NOT IN ( 'member_comfort', 'member_associated' ) 
		OR bcu.bc_user_package_status_id NOT IN ( 658, 659 ),
		pst.bc_dictionary_title_uk,
	IF
		(
			DATEDIFF( NOW(), bcu.bc_user_is_participant_till ) > 30,
			'Заборгований',
		IF
			( DATEDIFF( NOW(), bcu.bc_user_is_participant_till ) > 0, 'Місяць довіри', pst.bc_dictionary_title_uk ) 
		) 
	) AS bc_user_package_status_title,
IF
	( bcu.bc_user_associated = 1, 'так', '' ) AS associated,
	fbcg.bc_dictionary_title_uk AS facebook_closed_group,
	exp.bc_dictionary_title_uk AS bc_user_expert_type,
	AT.bc_dictionary_title_uk AS bc_user_at 
FROM
	bc_user AS bcu
	LEFT JOIN bc_user AS ba ON ba.bc_user_id = bcu.bc_user_business_assistant_id
	LEFT JOIN bc_user AS recr ON recr.bc_user_id = bcu.bc_user_recruiter_id
	LEFT JOIN bc_dictionary AS affiliate ON affiliate.bc_dictionary_id = bcu.bc_user_affiliate_id
	LEFT JOIN bc_dictionary AS bs ON bs.bc_dictionary_id = bcu.bc_user_status_in_business_1_id
	LEFT JOIN bc_user_companies AS ucn ON bcu.bc_user_id = ucn.bc_user_companies_user_id
	LEFT JOIN bc_user_companies AS ucp ON bcu.bc_user_id = ucp.bc_user_companies_user_id
	LEFT JOIN bc_wallet AS bcw1 ON bcw1.bc_wallet_user_id = bcu.bc_user_id
	LEFT JOIN bc_wallet AS bcw2 ON bcw2.bc_wallet_user_id = bcu.bc_user_id
	LEFT JOIN bc_dictionary AS area ON area.bc_dictionary_id = bcu.bc_user_area_id
	LEFT JOIN bc_dictionary AS city ON city.bc_dictionary_id = bcu.bc_user_city_id
	LEFT JOIN bc_dictionary AS dmom ON dmom.bc_dictionary_id = bcu.bc_user_mom_status_id
	LEFT JOIN bc_connections_db AS lang ON bcu.bc_user_id = lang.bc_connections_db_left_id 
	AND lang.bc_connections_db_right_key = 'bc_user_language'
	LEFT JOIN bc_dictionary AS dlang ON lang.bc_connections_db_right_id = dlang.bc_dictionary_id
	LEFT JOIN bc_connections_db AS clangid ON bcu.bc_user_id = clangid.bc_connections_db_left_id 
	AND clangid.bc_connections_db_right_key = 'bc_user_language'
	LEFT JOIN bc_dictionary AS dlangid ON clangid.bc_connections_db_right_id = dlangid.bc_dictionary_id
	LEFT JOIN bc_connections_db AS ctr ON bcu.bc_user_id = ctr.bc_connections_db_left_id 
	AND ctr.bc_connections_db_right_key = 'bc_user_training_id'
	LEFT JOIN bc_dictionary AS dtr ON ctr.bc_connections_db_right_id = dtr.bc_dictionary_id
	LEFT JOIN bc_connections_db AS ctrid ON bcu.bc_user_id = ctrid.bc_connections_db_left_id 
	AND ctrid.bc_connections_db_right_key = 'bc_user_training_id'
	LEFT JOIN bc_dictionary AS dtrid ON ctrid.bc_connections_db_right_id = dtrid.bc_dictionary_id
	LEFT JOIN bc_dictionary AS ist ON ist.bc_dictionary_id = bcu.bc_user_internal_status_id
	LEFT JOIN bc_dictionary AS country ON country.bc_dictionary_id = bcu.bc_user_country_id
	LEFT JOIN bc_dictionary AS sex ON sex.bc_dictionary_id = bcu.bc_user_sex_id
	LEFT JOIN bc_dictionary AS marrige ON marrige.bc_dictionary_id = bcu.bc_user_marrige_id
	LEFT JOIN bc_dictionary AS subord ON subord.bc_dictionary_id = bcu.bc_user_subordinates_id
	LEFT JOIN bc_packages AS bp ON bp.bc_packages_code = bcu.bc_user_package_code
	LEFT JOIN bc_dictionary AS pst ON pst.bc_dictionary_id = bcu.bc_user_package_status_id
	LEFT JOIN bc_dictionary AS fbcg ON fbcg.bc_dictionary_id = bcu.bc_user_fb_closed_group_id
	LEFT JOIN bc_dictionary AS exp ON exp.bc_dictionary_id = bcu.bc_user_expert_type_id
	LEFT JOIN bc_dictionary AS AT ON AT.bc_dictionary_id = bcu.bc_user_at_id 
WHERE
	bcu.bc_user_id = $id";

        $data = db()->selectSqlPrepared($query)[0];
        if($img === true){
            $data['img'] = '/images/no_photo.jpg';
        }
        $data['name'] = valid($data, 'bc_user_first_name') . ' ' . valid($data, 'bc_user_second_name');

        return new Collection($data);


    }

    protected function img(int $id = null): string
    {

        if (!$id && !$this->id) {
            return '/images/no_photo.jpg';
        }
        $id = $id ?: $this->id;
        $url = APP_URL . "theme/bc_user_{$id}/photo/{$id}";
        foreach (['jpg', 'jpeg', 'png', 'gif'] as $type) {
            if (isPhoto("$url.$type") !== false) {
                return "$url.$type";
            }
        }
        return '/images/no_photo.jpg';
    }


    public function apiGetUserPhoto(int $userId){
        $bc = new BCApi();

        $link = $bc->sendRequest([
            'action' => 'getUserPhoto',
            'user_id' => $userId
        ]);
        return $link['link'];
    }

    public function forParserImg($id)
    {
        $url = APP_URL . "theme/bc_user_{$id}/photo/{$id}";
        foreach (['jpg', 'jpeg', 'png', 'gif'] as $type) {
            if (isPhoto("$url.$type") !== false) {
                return ['link' => "$url.$type", 'type' => $type];
            }
        }
        return null;
    }

    /**
     * INIT VARST
     */
    protected function initiliaztion()
    {
        $this->auth = new UserAuth();
        $this->userId = valid($_SESSION, 'BCUserId');
    }

    protected function uploadUserPhoto($user_id)
    {
        $user_photo = empty($_FILES['user_photo']) ? null : $_FILES['user_photo'];
        if ($user_photo) {
            $image_types = ['image/jpeg', 'image/png', 'image/gif'];

            if (!in_array($user_photo['type'], $image_types)) {
                return 'Недозволений формат файлу (виберіть файл з розширенням .jpg або .png)';
            }

            if ($user_photo['error']) {
                return 'Помилка завантаження фото, спробуйте файл меншого розміру';
            }

            $user_photo_dir = UPLOAD_PATH . 'user_photos';
            $user_photo_url = UPLOAD_URL . 'user_photos';

            if (!is_dir($user_photo_dir)) {
                mkdir($user_photo_dir, 0777, true);
            }

            $user_photo = $this->extentionToLowerCase($user_photo);

            $ext = strtolower(pathinfo($user_photo['name'], PATHINFO_EXTENSION));
            $new_file_name = $user_id . '.' . $ext;

            if (move_uploaded_file($user_photo['tmp_name'], $user_photo_dir . '/' . $new_file_name)) {
                //TODO - remove schema
                $bcapi = new BCApi();
                $result = $bcapi->sendRequest([
                    'action'    => 'uploadUserPhoto',
                    'user_id'   => $user_id,
                    'url'       => $user_photo_url . '/',
                    'file_name' => $new_file_name,
                ]);
                if ($result) {
                    unlink($user_photo_dir . '/' . $new_file_name);

                    return true;
                }
                return false;
            }

            return false;
        }
        return false;
    }

    private function extentionToLowerCase($image_array)
    {
        $ext = ['.JPG', '.PNG', '.GIF'];
        if (key_exists('name', $image_array)) {
            foreach ($ext as $k => $v) {
                if (stristr($image_array['name'], $v)) {
                    $image_array['name'] = str_replace($v, strtolower($v), $image_array['name']);
                }
            }
        }
        return $image_array;
    }

}