<?php
namespace App\Utils;


use App\Auditing\ITransactionLogger;
use App\Exceptions\CannotDetermineExecutorException;
use App\Model\Jobs\Job;
use App\Model\Jobs\JobRun;
use App\Model\Services\JobService;
use App\Model\Services\UserService;
use App\Model\Users\User;
use App\Presenters\JobPresenter;
use Nette\InvalidStateException;
use Symfony\Component\Console\Command\Command;

trait JobCommand
{

	/** @var JobService @inject */
	public $jobService;

	/** @var UserService @inject */
	public $userService;

	/** @var ITransactionLogger @inject */
	public $auditing;

	/** @var Job */
	protected $job;
	/** @var JobRun */
	protected $jobRun;
	/** @var int */
	protected $jobId = null;
	/** @var int */
	protected $userId = null;
	/** @var User|null */
	protected $user = null;

	/**
	 * This function prepares context of job command.
	 * If $associateToJob is true, then job is found based on class name, its database user its used.
	 * If $associateToJob is false, then user can potencially be empty.
	 * In both cases when run via su or sudo in interactive mode then login of user is requested.
	 * @param bool $associateToJob
	 */
	public function prepare(bool $associateToJob = true)
	{
		$this->ensureUserUnderSudo();
		if (!$associateToJob) {
			if ($this->user) {
				$this->auditing->setCurrentUser($this->user);
			}
			return;
		}
		$this->job = $this->jobService->getByClassName(static::class);
		$this->jobRun = $this->jobService->newRun($this->job);
		$this->jobId = $this->job->id;
		if (!$this->userId) {
			$this->userId = $this->job->databaseUser;
		}
		if ($this->userId && !$this->user) {
			$this->user = $this->userService->get($this->userId);
		}

		if (!$this->jobId || !$this->userId || !$this->user) {
			throw new InvalidStateException("No such job matching name [{static::class}]. Have you registered it database table?");
		}
		$this->auditing->setCurrentUser($this->user);
	}

	public function finalize(int $returnCode, ?string $output, ?string $message)
	{
		$jobRunId = $this->jobRun->id;
		file_put_contents('compress.bzip2://' . JobPresenter::JOB_RUN_LOGS_DIR . $jobRunId . '.bz', $output);
		$this->jobService->finishRun($this->jobRun, $returnCode, null, $message);
	}

	public function ensureUserUnderSudo(): void
	{
		if (Helpers::isRunUnderSudo()) {
			if (!Helpers::isRunInteractive()) {
				throw new CannotDetermineExecutorException('This command was executed under sudo in non-interactive mode, therefore proper database user could not been determined.');
			}
			printf("Please login with system user to provide accoutability:\n");
			$user = $this->performLogin();
			printf("Continuing as {$user->username}...\n");
			$this->user = $user;
			$this->userId = $user->id;
		}
	}

	public function performLogin(): User
	{
		$username = Helpers::inputPrompt('Username');
		for($i = 3; $i > 0; $i--) {
			$password = Helpers::passwordPrompt('Password');
			$user = $this->userService->getByUsernameAndPassword($username, $password);
			if ($user) {
				return $user;
			} else {
				printf("Entered credentials were not correct.\n");
			}
		}
		throw new CannotDetermineExecutorException('This command has to be executed in privileged mode, however authentication has failed. Cannot continue;');
	}
}
