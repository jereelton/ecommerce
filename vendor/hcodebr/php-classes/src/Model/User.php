<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class User extends Model {

	const SESSION        = "User";
	const SECRET         = "FFFFFFFFFFFFFFFF";
	const SECRET_IV      = "F1F2F3F4F5F6F7F8F9F0";
	const ERROR          = "UserError";
	const ERROR_REGISTER = "UserErrorRegister";
	const SUCCESS        = "UserSucesss";

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

	public function saveUser() {

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

	public function deleteUser() {

		$sql = new Sql();

		$sql->query("CALL sp_users_delete(:IDUSER)", array(
			":IDUSER"=>$this->getiduser()
		));

	}

	public static function getForgot($email, $inadmin = true) {

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :EMAIL", array(
			":EMAIL"=>$email
		));

		if(count($results) === 0){

			throw new \Exception("Nao foi possivel recuperar a senha.");

		} else {


			$data = $results[0];

			$results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
				":iduser"=>$data["iduser"],
				":desip"=>$_SERVER["REMOTE_ADDR"]
			));

			if(count($results2) === 0) {

				throw new \Exception("Nao foi possivel recuperar a senha.");
				
			} else {


				$dataRecovery = $results2[0];

				$code = openssl_encrypt($dataRecovery['idrecovery'], 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

				$code = base64_encode($code);

				if ($inadmin === true) {

					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";

				} else {

					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
					
				}	

				$mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir Senha de Acesso", "forgot", array(
					"name"=>$data["desperson"],
					"link"=>$link
				));

				$mailer->send();

				return $link;

			}

		}

	}

	public function validForgotDecrypt($code) {

		$code = base64_decode($code);

		$idrecovery = openssl_decrypt($code, 'AES-128-CBC', pack("a16", User::SECRET), 0, pack("a16", User::SECRET_IV));

		$sql = new Sql();

		$results = $sql->select("
			SELECT * FROM tb_userspasswordsrecoveries a
			INNER JOIN tb_users b USING(iduser)
			INNER JOIN tb_persons c USING(idperson)
			WHERE
				a.idrecovery = :idrecovery
			    AND 
			    a.dtrecovery IS NULL
			    AND
			    DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW()", array(
			    	":idrecovery"=>$idrecovery
		));

		if(count($results) === 0) {

			throw new \Exception("Nao foi possivel recuperar a senha");
			
		} else {

			return $results[0];

		}

	}

	public static function setForgotUsed($idrecovery) {

		$sql = new Sql();

		$sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
			":idrecovery"=>$idrecovery
		));

	}

	public function setPassword($password) {

		$sql = new Sql();

		$sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
			":password"=>password_hash($password, PASSWORD_BCRYPT),
			":iduser"=>$this->getiduser()
		));

	}

}


?>