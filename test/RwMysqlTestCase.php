<?php
namespace ffan\php\mysql;

require_once 'UserEntity.php';
require_once '../vendor/autoload.php';
require_once 'config.php';

/** @var RwMysql $mysql */
$mysql = MysqlFactory::get('rw');

//下面几个select应该在从库读数据
print_r($mysql->getMultiAssocRow('select * from `users` limit 10', 'username'));

print_r($mysql->getMultiAssocCol('select id, username from `users` limit 10'));

print_r($mysql->getMultiFirstCol('select id from `users` limit 10'));

//insert会连接主库
$mysql->insert('users', make_user());
$rows = array();
for ($i = 0; $i < mt_rand(10, 50); $i++) {
    $rows[] = make_user();
}
$mysql->insert('users', $rows);

$rows = array();
for ($i = 0; $i < mt_rand(10, 50); $i++) {
    $rows[] = make_user(true);
}
$mysql->insert('users', $rows);

$user_id = $mysql->lastInsertId();
echo 'Last insert id:', $user_id, PHP_EOL;

/**
 * @param bool|false $re_obj 是否返回对象
 * @return array|object
 */
function make_user($re_obj = false)
{
    $user_name = 'test_user_' . mt_rand(1, 10000000);
    $user_arr = array(
        'username' => $user_name,
        'password' => md5(uniqid(true)),
        'email' => $user_name . '@wanda.cn',
        'mobile' => sprintf('180%08s', mt_rand(0, 99999999)),
        'age' => mt_rand(1, 99),
        'money' => round(mt_rand(10000, 100000000) / 89, 2),
        'remark' => ''
    );
    if (!$re_obj) {
        return $user_arr;
    }
    $user_obj = new UserEntity();
    $user_obj->username = $user_arr['username'];
    $user_obj->password = $user_arr['password'];
    $user_obj->email = $user_arr['email'];
    $user_obj->mobile = $user_arr['mobile'];
    $user_obj->age = $user_arr['age'];
    $user_obj->money = $user_arr['money'];
    $user_obj->remark = $user_arr['remark'];
    return $user_obj;
}

echo 'Affect rows:', $mysql->affectRows(), PHP_EOL;
$user_obj = make_user(true);
$mysql->insert('users', $user_obj);

$up_arr = array('username' => 'update_user_' . mt_rand(1, 2000000));
$mysql->update('users', $up_arr, 'id=' . $user_id);
$mysql->commit();

//之后的select将继续使用主库
print_r($mysql->getMultiRow('select * from `users` limit 10'));

print_r($mysql->getRow('select * from `users` where `id`=' . $user_id));

var_dump($mysql->getFirstCol('select * from `users` where `id`=' . $user_id));

print_r($mysql->getRow('select * from `users` where `id`=' . $user_id, '\ffan\php\mysql\UserEntity'));

//强制使用从库
$mysql->setForceSlave(true);
//接下来的查询将使用从库
print_r($mysql->getMultiRow('select * from `users` limit 10', '\ffan\php\mysql\UserEntity'));
print_r($mysql->getMultiAssocRow('select * from `users` limit 10', 'username', '\ffan\php\mysql\UserEntity'));

//delete 又将使用主库
$mysql->delete('users', 'id=' . $user_id);
$mysql->commit();