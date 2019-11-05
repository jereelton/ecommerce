<?php 

session_start();

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function(){
	
	$page = new Page();

	$page->setTpl("body");

});

$app->get('/testes', function(){

	$senha = "123mudar";

	$senha_enc = password_hash($senha, PASSWORD_BCRYPT);

	echo "SENHA-ENC:";
	var_dump($senha_enc);

	$senha_comp = password_verify($senha, $senha_enc);

	echo "<br/>SENHA-COMPLETA: ";
	var_dump($senha_comp);

});

$app->get('/admin', function(){
		
	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("body");

});

$app->get('/admin/login', function(){

		$page = new PageAdmin([
			"header" => false,
			"footer" => false
		]);

		$page->setTpl("login");
});

$app->post('/admin/login', function(){

	User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;
});

$app->get('/admin/logout', function(){

	User::logout();

	header("Location: /admin/login");
	exit;

});

//Lista todos os registros de usuarios
$app->get('/admin/users', function(){

	User::verifyLogin();

	$users = User::listAll();

	$page = new PageAdmin();

	$page->setTpl("users", array(
		"users"=>$users
	));

});

$app->get('/admin/users/create', function(){

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl("users-create");

});

$app->post('/admin/users/create', function(){

	User::verifyLogin();

	$user = new User();

	$_POST['inadmin'] = (isset($_POST['inadmin'])) ? 1: 0;

	$user->setData($_POST);

	$user->save();

	header("Location: /admin/users");
	exit;

});

//Carrega os dados de um usuario para edicao do cadastro
$app->get('/admin/users/:iduser', function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->listUser((int)$iduser);

	$page = new PageAdmin();

	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));

});

//Carrega os dados de um usuario para edicao do cadastro
$app->get('/admin/users/:iduser/updated', function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->listUser($iduser);

	$page = new PageAdmin();

	$user_details = json_decode('{"msg_status" : 1, "msg" : "Usuario Atualizado com sucesso !"}');

	$page->setTpl("user-updated", array(
		"user"=>$user->getValues(),
		"user_status"=>$user_details->msg_status,
		"user_msg"=>$user_details->msg
	));

});

//Carrega os dados de um usuario para edicao do cadastro
$app->get('/admin/users/:iduser/failed', function($iduser){

	User::verifyLogin();

	$user = new User();

	$user->listUser($iduser);

	$page = new PageAdmin();

	$user_details = json_decode('{"msg_status" : 0, "msg" : "Erro ao tentar atualizar usuario !"}');

	$page->setTpl("user-updated", array(
		"user"=>$user->getValues(),
		"user_status"=>$user_details->msg_status,
		"user_msg"=>$user_details->msg
	));

});

$app->post('/admin/users/:iduser', function($iduser){

	User::verifyLogin();

	$_POST['inadmin'] = (isset($_POST['inadmin'])) ? 1: 0;

	$user = new User();

	$user->listUser($iduser);

	$user_status = ($user->updateUser($iduser, $_POST)) ? "updated" : "failed";

	header("Location: /admin/users/".$iduser."/".$user_status);

	exit;

});

$app->get('/admin/users/:iduser/delete', function($iduser){

	User::verifyLogin();

});

$app->run();

 ?>