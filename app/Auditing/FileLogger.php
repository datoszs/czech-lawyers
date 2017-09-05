<?php declare(strict_types=1);
namespace App\Auditing;


use App\Model\Users\User as CurrentUser;
use LogicException;
use Nette\Http\Request;
use Nette\Security\User;
use RuntimeException;

class FileLogger implements ITransactionLogger
{
	private const PATH = '%s/%s.log';

	/** @var string */
	private $path;

	/** @var null */
	private $transactionStatus = null; // null -- not in transaction; true - transaction in progress; false - rollbacked or already commited

	/** @var string[] */
	private $delayed = [];

	/** @var User */
	private $user;

	/** @var CurrentUser */
	private $currentUser;

	/** @var Request */
	private $request;

	public function __construct(string $directory, User $user, Request $request)
	{
		if (!$directory) {
			throw new LogicException('Directory is not specified.');
		} elseif (!is_dir($directory) ) {
			if (!@mkdir($directory) || !is_dir($directory)) {
				throw new RuntimeException("Directory '$directory' is not found or is not directory (could not create).");
			}
		} elseif (!is_writable($directory)) {
			throw new RuntimeException("Directory '$directory' is not writable.");
		}
		$this->path = sprintf(static::PATH, $directory, date('Y-m'));
		$this->user = $user;
		$this->request = $request;
	}

	/**
	 * Set user entity which override currently logged user
	 * @param CurrentUser $user
	 */
	public function setCurrentUser(CurrentUser $user): void
	{
		$this->currentUser = $user;
	}

	/**
	 * Returns formatted countryparty info (name and ID if present)
	 * @return string
	 */
	public function getCounterparty(): string
	{
		if ($this->currentUser) {
			return sprintf('[%s] %s', $this->currentUser->getPersistedId(), $this->currentUser->fullname ?? '< no name >');
		} elseif ($this->user->isLoggedIn()) {
			return sprintf('[%s] %s', $this->user->getId(), $this->user->getIdentity()->fullname ?? '< no name >');
		} else {
			return '[null] Anonymous';
		}
	}

	/**
	 * Returns formatted countrypart identification (IP + User Agent if present)
	 * @return string
	 */
	public function getCountrypartyIdentification(): string
	{
		return sprintf(
			'[%s] => %s',
			$this->request->getRemoteAddress(),
			$_SERVER['HTTP_USER_AGENT'] ?? 'NO USER AGENT'
		);
	}

	private function prepareMessage(string $operation, string $auditedSubject, string $description, string $reason): string {
		global $argv;
		return sprintf(
			"DATE AND TIME: %s
REQUEST: %s
COUNTERPARTY: %s
IDENTIFICATION: %s
AUDITED SUBJECT TYPE: %s
OPERATION: %s
REASON: %s
DESCRIPTION: %s
-----------\n",
			date('Y-m-d H:i:s'),
			PHP_SAPI === 'cli' ? implode(' ', $argv) : $this->request->getUrl(),
			$this->getCounterparty(),
			$this->getCountrypartyIdentification(),
			$auditedSubject,
			$operation,
			$reason,
			$description
		);
	}

	/**
	 * Log given message into auditing log or throws an exception
	 * @param string $message
	 * @throws RuntimeException when log file is not writable
	 */
	private function log(string $message): void
	{
		if (!@file_put_contents($this->path, $message, FILE_APPEND | LOCK_EX)) { // @ is escalated to exception
			throw new RuntimeException("Unable to write to log file '{$this->path}'. Is directory writable?");
		}
	}

	public function logCreate(string $auditedSubject, string $description, string $reason): void
	{
		$message = $this->prepareMessage(static::OPERATION_CREATE, $auditedSubject, $description, $reason);
		if ($this->transactionStatus === true) {
			$this->delayed[] = $message;
			return;
		}
		$this->log($message);
	}

	public function logAccess(string $auditedSubject, string $description, string $reason): void
	{
		$message = $this->prepareMessage(static::OPERATION_ACCESS, $auditedSubject, $description, $reason);;
		if ($this->transactionStatus === true) {
			$this->delayed[] = $message;
			return;
		}
		$this->log($message);
	}

	public function logChange(string $auditedSubject, string $description, string $reason): void
	{
		$message = $this->prepareMessage(static::OPERATION_CHANGE, $auditedSubject, $description, $reason);
		if ($this->transactionStatus === true) {
			$this->delayed[] = $message;
			return;
		}
		$this->log($message);
	}

	public function logRemove(string $auditedSubject, string $description, string $reason): void
	{
		$message = $this->prepareMessage(static::OPERATION_REMOVE, $auditedSubject, $description, $reason);
		if ($this->transactionStatus === true) {
			$this->delayed[] = $message;
			return;
		}
		$this->log($message);
	}

	public function createTransactionLogger(): ITransactionLogger
	{
		if ($this->transactionStatus !== null) {
			throw new LogicException('Transaction logger can be created only from the root logger.');
		}
		$temp = clone $this;
		$temp->transactionStatus = true;
		$temp->delayed = [];
		return $temp;
	}

	public function commit(): void
	{
		if ($this->transactionStatus !== true) {
			throw new LogicException('This transaction is not active, cannot commit.');
		}
		foreach ($this->delayed as $message) {
			$this->log($message);
		}
		$this->transactionStatus = false;
		$this->delayed = [];
	}

	public function rollback(): void
	{
		if ($this->transactionStatus !== true) {
			throw new LogicException('This transaction is not active, cannot rollback.');
		}
		$this->transactionStatus = false;
		$this->delayed = [];
	}
}
