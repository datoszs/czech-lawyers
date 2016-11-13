<?php
namespace app\Utils;


use App\Model\Jobs\Job;
use App\Model\Jobs\JobRun;
use App\Model\Services\JobService;
use App\Model\Services\UserService;
use App\Model\Users\User;
use Nette\InvalidStateException;
use Symfony\Component\Console\Command\Command;

trait JobCommand
{

	/** @var JobService @inject */
	public $jobService;

	/** @var UserService @inject */
	public $userService;

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

	public function prepare()
	{
		$this->job = $this->jobService->getByClassName(static::class);
		$this->jobRun = $this->jobService->newRun($this->job);
		$this->jobId = $this->job->id;
		$this->userId = $this->job->databaseUser;
		if ($this->userId) {
			$this->user = $this->userService->get($this->userId);
		}

		if (!$this->jobId || !$this->userId || !$this->user) {
			throw new InvalidStateException("No such job matching name [{static::class}]. Have you registered it database table?");
		}
	}

	public function finalize($returnCode, $output, $message)
	{
		$this->jobService->finishRun($this->jobRun, $returnCode, $output, $message);
	}
}