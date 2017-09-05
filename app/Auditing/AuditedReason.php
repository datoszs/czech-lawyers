<?php declare(strict_types=1);
namespace App\Auditing;

use Nette\StaticClass;

/**
 * Enum class with reasons
 */
class AuditedReason
{
	use StaticClass;

	public const REQUESTED_INDIVIDUAL = 'Requested in one record (direct access)';
	public const REQUESTED_BATCH = 'Requested in batch (search/browse/list)';
	public const SCHEDULED = 'Scheduled';
	public const FIXUP = 'Fix up';
	public const MANUAL_INPUT = 'Manual input';
	public const REMOTE_UPDATE = 'Remote update'; // E.g. changed in ČAK
	public const INTERNAL_MANAGEMENT = 'Internal management and control'; // E.g. management of users, permissions,...
	public const SELF_MANAGEMENT = 'Self management'; // E.g. changing own profile
}
