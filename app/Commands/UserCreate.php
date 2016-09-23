<?php
namespace App\Commands;


use App\Enums\UserRole;
use App\Enums\UserType;
use App\Model\Services\UserService;
use App\Model\Users\User;
use App\Utils\Normalize;
use Nette\Security\Passwords;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserCreate extends Command
{
	/** @var UserService @inject */
	public $userService;

	protected function configure()
	{
		$this->setName('app:user-create')
			->setDescription('Create new user (type = person, isActive = true, isLoginAllowed = true).')
			->addArgument(
				'username',
				InputArgument::REQUIRED,
				'Username used for login (typically e-mail).'
			)->addArgument(
				'role',
				InputArgument::REQUIRED,
				sprintf("Role of the user (available: %s).", implode(', ', array_keys(UserRole::$statuses)))
			);
		;
	}

	protected function execute(InputInterface $input, OutputInterface $consoleOutput)
	{
		$username = Normalize::username($input->getArgument('username'));
		$role = $input->getArgument('role');
		$entity = $this->userService->findByUsername($username);
		if ($entity) {
			$consoleOutput->writeln('Error: User with such username already exists.');
			exit(1);
		}

		if (!in_array($role, array_keys(UserRole::$statuses))) {
			$consoleOutput->writeln('Error: Unknown role');
			exit(2);
		}
		printf("%s: ", 'Enter password');
		$password = mb_substr(fgets(STDIN), 0, -2);
		printf("%s: ", 'Enter password (again)');
		$password2 = mb_substr(fgets(STDIN), 0, -2);
		if (mb_strlen($password) == 0 || $password != $password2) {
			$consoleOutput->writeln('Error: Password mismatch or empty. Cannot continue.');
			exit(3);
		}
		$user = new User();
		$user->type = UserType::TYPE_PERSON;
		$user->username = $username;
		$user->role = $role;
		$user->password = Passwords::hash($password);
		$user->isActive = true;
		$user->isLoginAllowed = true;

		$this->userService->save($user);
		$consoleOutput->writeln('User created.');
	}
}
