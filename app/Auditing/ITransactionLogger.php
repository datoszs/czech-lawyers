<?php declare(strict_types=1);
namespace App\Auditing;

/**
 * Interface for Audit log to be used in transactions
 * (e.g. when data are changed at the end of transaction).
 *
 * When no commit happens and transaction log is non-empty, exception is thrown.
 */
interface ITransactionLogger extends ILogger
{
	/**
	 * Commits given transaction log, logging is done and log is flushed, and the instance is not usable anymore.
	 */
	public function commit(): void;

	/**
	 * Cancel current transaction, empty the log and prevents the instance from being usable anymore.
	 */
	public function rollback(): void;
}
