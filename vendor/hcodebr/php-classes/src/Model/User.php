<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class User extends Model {

	const SESSION = "User";

	public static function login($login, $password){

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN" => $login
		));

		if(count($results) === 0) {
		
			throw new \Exception("Usuario inexistente ou senha invalida");
			
		}

		$data = $results[0];

		if(password_verify($password, $data["despassword"])) {

			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {

			throw new \Exception("Usuario inexistente ou senha invalida");

		}

	}

	public static function verifyLogin($inadmin = true) {

		if(
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			||
			(bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
		) {
		
			session_destroy();
			header("Location: /admin/login");
			exit;
		}

	}

	public static function logout() {

		$_SESSION[User::SESSION] = NULL;
		session_destroy();

	}

	public static function listAll() {

		$sql = new Sql();

		return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

	}

	public function listUser($iduser) {

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :ID ORDER BY b.desperson", array(
			":ID"=>$iduser
		));

		$this->setData($results[0]);

	}

	public function updateUser($iduser, $params = array()) {

		$sql = new Sql();

		$checkpass = ($params['despassword'] === $this->getdespassword()) ? $params['despassword'] : password_hash($params['despassword'], PASSWORD_BCRYPT);

		return $sql->query("CALL sp_usersupdate_save(:IDUSER, :USERNAME, :USERLOGIN, :USERPASSWORD, :USERMAIL, :USERPHONE, :USERLEVEL)", array(
			":IDUSER"=>$iduser,
			":USERNAME"=>$params['desperson'],
			":USERLOGIN"=>$params['deslogin'],
			":USERPASSWORD"=>$checkpass,
			":USERMAIL"=>$params['desemail'],
			":USERPHONE"=>$params['nrphone'],
			":USERLEVEL"=>$params['inadmin']
		));

	}

	public function save() {

		$sql = new Sql();

		$results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
			":desperson"=>$this->getdesperson(),
			":deslogin"=>$this->getdeslogin(),
			":despassword"=>password_hash($this->getdespassword(), PASSWORD_BCRYPT),
			":desemail"=>$this->getdesemail(),
			":nrphone"=>$this->getnrphone(),
			":inadmin"=>$this->getinadmin()
		));

		$this->setData($results[0]);

	}

}


?>