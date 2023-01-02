<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\ScriptController;

class activate_modules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activate_module';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activated module for all merchants';

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
    public function handle()
    {
	    //
	    $confirm = $this->ask('Are you sure you want to activate all the modules for all merchants? (y/n)');
	    if ($confirm == 'y' || $confirm == 'Y') {

	    	$SC =  new ScriptController();
			$m_list = $SC->activate_all_merchant_module();
			
			$this->info("Activation process started..");	

			foreach ($m_list as $m) {
				$m = empty($m) ? 'Merchant Name':$m;
				$this->info("Module activated for $m");
			}
	    }	
    }
}
