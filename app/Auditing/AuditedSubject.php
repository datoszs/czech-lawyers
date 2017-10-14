<?php declare(strict_types=1);
namespace App\Auditing;

use Nette\StaticClass;

/**
 * Enum class with constants which are subject of auditing log
 */
class AuditedSubject
{
	use StaticClass;

	public const USER_INFO = 'user_info'; /* User ID, fullname, username (e-mail) */
	public const ADVOCATE_INFO = 'advocate_info'; /* IČ, EČ, fullname, address, e-mail,... */
	public const CASE_TAGGING = 'case_tagging'; /* connection between advocate and case and result */
	public const DATA_EXPORT = 'data_export'; /* selected information in data exports */
}
