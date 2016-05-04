<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use League\CommonMark\Converter;

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

    protected $languages;

    protected $filenames;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Converter $converter)
    {
        parent::__construct();

        $this->converter = $converter;

        $this->languages = config('csheet.languages');

        $this->filenames = config('csheet.filenames');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->parseMdFiles();

    }

    /**
     * Parse all MD files to DB.
     *
     * @return mixed
     */
    private function parseMdFiles()
    {
        foreach ($this->languages as $language) {
            $this->parseOneLanguage($language);
        }
    }

    /**
     * Parse files of given language.
     *
     */
    private function parseOneLanguage($language)
    {
        foreach ($this->filenames as $filename) {
            $this->parseOneFile($filename, $language);
        }
    }

    /**
     * Parse one file of given language.
     *
     */
    private function parseOneFile($filename, $language)
    {
        $md = $this->getMdFileContents($filename, $language);

        //$converter->convertToHtml('')
    }

    /**
     * Get MD file contents from GitHub.
     *
     * @return mixed
     */
    private function getMdFileContents($filename, $language)
    {
        echo $language.' - '.$filename.'<br />';
    }
}
