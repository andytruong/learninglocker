<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use app\commands\generate\GenerateUser;
use app\commands\generate\GenerateLrs;
use app\commands\generate\GenerateStatement;

class Generate extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'generate:data';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Generate dummy data.';

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
    
	    // Get input data
	    $model = $this->option('model');
	    $number = $this->argument('number');

	    // Find model class and generate.
	    $class = "app\commands\generate\Generate{$model}";
	    $interface = class_implements($class);
	    if (class_exists($class) && isset($interface['app\commands\generate\GenerateInterface'])) {
	    	$gen = new $class();
	    	for ($i=0; $i < $number; $i++) { 
	    		$gen->generate();	
	    	}
	    } else {
	    	$this->info('Model does\'nt have any implement. Coming soon!');
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
			array('number', InputArgument::REQUIRED, 'Numbers of model that you want to generation.'),
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
			array('model', null, InputOption::VALUE_REQUIRED, 'Model\'s name.', null),
		);
	}

}
