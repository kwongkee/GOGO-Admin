<?php
// Ä£¿éLTDÌá¹©
namespace LeanCloud;

class LeanUser extends LeanObject
{
	static protected $className = '_User';
	static protected $currentUser;

	public function setUsername($username)
	{
		$this->set('username', $username);
		return $this;
	}

	public function setEmail($email)
	{
		$this->set('email', $email);
		return $this;
	}

	public function setPassword($password)
	{
		$this->set('password', $password);
		return $this;
	}

	public function setMobilePhoneNumber($number)
	{
		$this->set('mobilePhoneNumber', $number);
		return $this;
	}

	public function signUp()
	{
		if ($this->getObjectId()) {
			throw new CloudException('User has already signed up.');
		}

		parent::save();
		static::saveCurrentUser($this);
	}

	public function save()
	{
		if ($this->getObjectId()) {
			parent::save();
			return NULL;
		}

		throw new CloudException('Cannot save new user, please signUp ' . 'first.');
	}

	public function updatePassword($old, $new)
	{
		if ($this->getObjectId()) {
			$path = '/users/' . $this->getObjectId() . '/updatePassword';
			$resp = LeanClient::put($path, array('old_password' => $old, 'new_password' => $new), $this->getSessionToken());
			$this->mergeAfterFetch($resp);
			static::saveCurrentUser($this);
			return NULL;
		}

		throw new CloudException('Cannot update password on new user.');
	}

	public function getUsername()
	{
		return $this->get('username');
	}

	public function getEmail()
	{
		return $this->get('email');
	}

	public function getMobilePhoneNumber()
	{
		return $this->get('mobilePhoneNumber');
	}

	public function getSessionToken()
	{
		return $this->get('sessionToken');
	}

	static protected function setCurrentSessionToken($token)
	{
		LeanClient::getStorage()->set('LC_SessionToken', $token);
	}

	static public function getCurrentSessionToken()
	{
		return LeanClient::getStorage()->get('LC_SessionToken');
	}

	static public function getCurrentUser()
	{
		if (self::$currentUser instanceof LeanUser) {
			return self::$currentUser;
		}

		$token = static::getCurrentSessionToken();

		if ($token) {
			return static::become($token);
		}
	}

	static private function saveCurrentUser($user)
	{
		self::$currentUser = $user;
		self::setCurrentSessionToken($user->getSessionToken());
	}

	static private function clearCurrentUser()
	{
		self::$currentUser = null;
		self::setCurrentSessionToken(null);
	}

	static public function become($token)
	{
		$resp = LeanClient::get('/users/me', array('session_token' => $token));
		$user = new static();
		$user->mergeAfterFetch($resp);
		static::saveCurrentUser($user);
		return $user;
	}

	static public function logIn($username, $password)
	{
		$resp = LeanClient::post('/login', array('username' => $username, 'password' => $password));
		$user = new static();
		$user->mergeAfterFetch($resp);
		static::saveCurrentUser($user);
		return $user;
	}

	static public function logOut()
	{
		$user = static::getCurrentUser();

		if ($user) {
			try {
				LeanClient::post('/logout', null, $user->getSessionToken());
			}
			catch (CloudException $exp) {
			}

			static::clearCurrentUser($user);
		}
	}

	static public function logInWithSmsCode($phoneNumber, $smsCode)
	{
		$params = array('mobilePhoneNumber' => $phoneNumber, 'smsCode' => $smsCode);
		$resp = LeanClient::get('/login', $params);
		$user = new static();
		static::saveCurrentUser($user);
		return $user;
	}

	static public function requestLoginSmsCode($phoneNumber)
	{
		LeanClient::post('/requestLoginSmsCode', array('mobilePhoneNumber' => $phoneNumber));
	}

	static public function requestEmailVerify($email)
	{
		LeanClient::post('/requestEmailVerify', array('email' => $email));
	}

	static public function requestPasswordReset($email)
	{
		LeanClient::post('/requestPasswordReset', array('email' => $email));
	}

	static public function requestPasswordResetBySmsCode($phoneNumber)
	{
		LeanClient::post('/requestPasswordResetBySmsCode', array('mobilePhoneNumber' => $phoneNumber));
	}

	static public function resetPasswordBySmsCode($smsCode, $newPassword)
	{
		LeanClient::put('/resetPasswordBySmsCode/' . $smsCode, array('password' => $newPassword));
	}

	static public function requestMobilePhoneVerify($phoneNumber)
	{
		LeanClient::post('/requestMobilePhoneVerify', array('mobilePhoneNumber' => $phoneNumber));
	}

	static public function verifyMobilePhone($smsCode)
	{
		LeanClient::post('/verifyMobilePhone/' . $smsCode, null);
	}

	static public function logInWith($provider, $authToken)
	{
		$user = new static();
		$user->linkWith($provider, $authToken);
		static::saveCurrentUser($user);
		return $user;
	}

	public function linkWith($provider, $authToken)
	{
		if (!is_string($provider) || empty($provider)) {
			throw new \InvalidArgumentException('Provider name can only ' . 'be string.');
		}

		$data = $this->get('authData');

		if (!$data) {
			$data = array();
		}

		$data[$provider] = $authToken;
		$this->set('authData', $data);
		parent::save();
		return $this;
	}

	public function unlinkWith($provider)
	{
		if (!is_string($provider) || empty($provider)) {
			throw new \InvalidArgumentException('Provider name can only ' . 'be string.');
		}

		if (!$this->getObjectId()) {
			throw new \RuntimeException('Cannot unlink with unsaved user.');
		}

		$data = $this->get('authData');

		if (isset($data[$provider])) {
			$data[$provider] = null;
			$this->set('authData', $data);
			$this->save();
		}

		return $this;
	}
}

?>
