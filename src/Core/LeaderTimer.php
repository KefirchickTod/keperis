<?php


namespace src\Core;


class LeaderTimer
{

    const TIMER_STOP  = 2;
    const TIMER_OFF = 0;
    const TIMER_ONN = 1;

    private $mode;

    private $userId;

    protected $db;

    public function __construct(int $mode, int $user_id)
    {
        $this->mode = $mode;
        $this->userId = $user_id;
        $this->db = db();
    }

    public function stop()
    {
        return $this->update(2, [
            'bc_user_leaders_timer_stop_at' => date("Y-m-d H:i:s"),
        ]);
    }

    public function start()
    {
        return $this->update(1, [
            'bc_user_leaders_timer_start_at' => date("Y-m-d H:i:s"),
        ]);
    }

    public function clean()
    {
        return $this->update(0);
    }

    private function update($mode, $value = [])
    {

        if ($this->isTimer($this->userId) || $this->isNote($this->userId)) {
            $value = $value ? array_merge($value, [
                'bc_user_leaders_cout_timer' => $mode,
                'bc_user_leaders_update_at'  => date("Y-m-d H:i:s"),
            ]) : [
                'bc_user_leaders_cout_timer' => $mode,
                'bc_user_leaders_update_at'  => date("Y-m-d H:i:s"),
            ];
            return (bool)$this->db->updateSql("bc_user_leaders", $value,
                'bc_user_leaders_user_id = ' . (int)$this->userId);
        }
        $value = $value ? array_merge($value, [
            'bc_user_leaders_cout_timer' => $mode,
            'bc_user_leaders_created_at' => date("Y-m-d H:i:s"),
            'bc_user_leaders_user_id'    => $this->userId,
            'bc_user_leaders_author_id'  => current_user_id(),
        ]) : [
            'bc_user_leaders_cout_timer' => $mode,
            'bc_user_leaders_created_at' => date("Y-m-d H:i:s"),
            'bc_user_leaders_user_id'    => $this->userId,
            'bc_user_leaders_author_id'  => current_user_id(),
        ];
        return (bool)$this->db->dynamicInsert('bc_user_leaders', $value);

    }

    public function isTimer($userId)
    {
        if (!structure()->isEmpty('isTimer')) {
            structure()->delete('isTimer');
        }

        return structure()->set([
            'isTimer' => [
                'get'     => ['userLeaderUserId', 'timerMode'],
                'class'   => 'bcUserLeader',
                'setting' => [
                    'where' => 'userLeaderUserId = ' . (int)$userId,
                ],
            ],
        ])->getData(function ($row) {

            return (!empty($row) && isset($row[0]) && $row[0]['timerMode'] !== null);
        }, 'isTimer');
    }

    public function isNote($userId){
        if (!structure()->isEmpty('isNote')) {
            structure()->delete('isNote');
        }

        return structure()->set([
            'isNote' => [
                'get'     => ['userLeaderUserId'],
                'class'   => 'bcUserLeader',
                'setting' => [
                    'where' => 'userLeaderUserId = ' . (int)$userId,
                ],
            ],
        ])->getData(function ($row) {

            return (!empty($row) && isset($row[0]));
        }, 'isNote');
    }

    public function auto(){
        if($this->mode === LeaderTimer::TIMER_ONN){
            return $this->start();
        }elseif ($this->mode === LeaderTimer::TIMER_STOP){
            return $this->stop();
        }elseif ($this->mode === LeaderTimer::TIMER_OFF){
            return $this->clean();
        }
        return false;
    }

    public static function getStopDays(int $userId){
        structure()->set([
            "stopDay$userId" => [
                'get' => ['userLeaderUserId', 'timerStopDays2'],
                'class' => bcUserLeader::class,
                'setting' => [
                    'where' => 'userLeaderUserId = ' . (int)$userId,
                ]
            ]
        ])->getData(function ($row){
            if(!empty($row) && isset($row[0]['timerStopDays2'])){
                return $row[0]['timerStopDays2'];
            }
            return '';
        },"stopDay$userId");
    }


}