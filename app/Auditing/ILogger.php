<?php declare(strict_types=1);
namespace App\Auditing;

use App\Model\Users\User;

/**
 * Interface for audit log
 * @see: https://www.uoou.cz/files/101_cz.pdf (Zákon č. 101/2000 Sb., o ochraně osobních údajů)
 *
 * Logged information:
 *  - When (date and time)
 *  - Who (Fullname + User ID) (IP + User Agent)
 *  - Reason (textual description)
 *  - Operation (i.e. "read of X", "change of X to Y"...)
 *
 * The logger should log into given directory and split logs per month basis.
 */
interface ILogger
{
	public const OPERATION_CREATE = 'create';
	public const OPERATION_ACCESS = 'access';
	public const OPERATION_CHANGE = 'change';
	public const OPERATION_REMOVE = 'remove';

	public function setCurrentUser(User $user): void;

	public function logCreate(string $auditedSubject, string $description, string $reason): void;

	public function logAccess(string $auditedSubject, string $description, string $reason): void;

	public function logChange(string $auditedSubject, string $description, string $reason): void;

	public function logRemove(string $auditedSubject, string $description, string $reason): void;

	/**
	 * Create transaction logger from current logger which starts with no messages to be logged.
	 */
	public function createTransactionLogger(): ITransactionLogger;
}
