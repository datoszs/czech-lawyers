<?php
namespace App\Model\Services;


use App\Model\Orm;
use App\Model\Users\User;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Nette\Security\Passwords;

class UserService implements IAuthenticator
{

	const DISABLED_ACCOUNT = 5;
	const DISABLED_LOGIN = 6;

	/** @var Orm */
	private $orm;

	public function __construct(Orm $orm)
	{
		$this->orm = $orm;
	}

	public function get($id)
	{
		return $this->orm->users->getById($id);
	}

	public function save(User $entity)
	{
		$this->orm->persistAndFlush($entity);
	}

	public function findAll()
	{
		return $this->orm->users->findAll()->orderBy('id')->fetchAll();
	}

	public function findByUsername($value)

	{
		return $this->orm->users->getBy([
			'username' => $value,
		]);
	}

	public function delete($id)
	{
		$entity = $this->orm->users->getById($id);
		$this->orm->users->remove($entity);
		$this->orm->users->persistAndFlush($entity);
	}

	public function enable($id)
	{
		/** @var User $entity */
		$entity = $this->orm->users->getById($id);
		$entity->isActive = true;
		$this->orm->users->persistAndFlush($entity);
		return $entity;
	}

	public function disable($id)
	{
		/** @var User $entity */
		$entity = $this->orm->users->getById($id);
		$entity->isActive = false;
		$this->orm->users->persistAndFlush($entity);
		return $entity;
	}

	/**
	 * Performs an authentication against e.g. database.
	 * and returns IIdentity on success or throws AuthenticationException
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		/** @var User $entity */
		$entity = $this->orm->users->findBy(['username' => $username])->fetch();

		if (!$entity) {
			throw new AuthenticationException('The username is incorrect.', static::IDENTITY_NOT_FOUND);

		} elseif (!$entity->isActive) {
			throw new AuthenticationException('This account is disabled.', static::DISABLED_ACCOUNT);
		} elseif (!$entity->isLoginAllowed) {
			throw new AuthenticationException('Login of this account is disabled.', static::DISABLED_LOGIN);
		} elseif (!Passwords::verify($password, $entity->password)) {
			throw new AuthenticationException('The password is incorrect.', static::INVALID_CREDENTIAL);
		} elseif (Passwords::needsRehash($entity->password)) {
			$entity->password = Passwords::hash($password);
			$this->orm->persistAndFlush($entity);
		}

		$arr = $entity->toArray();
		unset($arr['password']);
		return new Identity($entity->id, $entity->role, $arr);
	}
}