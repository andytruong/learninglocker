<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UserPassword extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'user:password';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Reset password for a user';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        $credentials = array(
            'email' => $this->argument('email'),
            'password' => $this->argument('password'),
            'password_confirmation' => $this->argument('password'),
        );

        $user = Password::getUser($credentials);
        if ($user) {
            $user->password = Hash::make($password);
            $user->save();
            $this->info('Updated');
        }
        else {
            $this->info('User not found.');
        }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('email', InputArgument::REQUIRED, 'Email of user'),
            array('password', InputArgument::REQUIRED, 'New password'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			// array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
