<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Parse MD files from GitHub.
 */
class ParseMd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cs:parsemd';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse MD files from GitHub';

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
        parseMdFiles();

    }

    /**
     * Parse all MD files to DB.
     *
     * @return mixed
     */
    private function parseMdFiles(){
        $languages = config('csheet.languages');

        foreach ($languages as $language) {
            $parseOneMdFile($language);
        }
    }

    private function parseOneLanguageMdFiles($language){
        $filenames = config('csheet.filenames');

        foreach ($filenames as $filename) {
            // concatinate all md files
        }
    }
}
