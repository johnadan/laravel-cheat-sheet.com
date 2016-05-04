<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use League\CommonMark\Converter;
use \Htmldom;

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

    protected $repository;

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

        $this->repository = config('csheet.repository');
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
        // Get MD file contents.
        $md = $this->getMdFileContents($filename, $language);

        // Convert MD to HTML.
        $sourceHtml = $this->converter->convertToHtml($md);

        $htmlExploded = explode('<hr />', $sourceHtml);

        $clausesArray = $this->parseClauses($htmlExploded[0]);

        //dd($clausesArray);
    }

    /**
     * Get MD file contents from GitHub.
     *
     * @return string
     */
    private function getMdFileContents($filename, $language)
    {
        return file_get_contents($this->repository.$language.'/'.$filename.'.md');
    }

    private function parseClauses($htmlList)
    {
        $html = new Htmldom($htmlList);

        $clausesArray = [];

        $clausesArray = $html->find('p a');

        foreach ($clausesArray as $clauseLink) {
            $clause = new Htmldom($clauseLink);
            echo $clause->find('a')[0]->innertext.' - ';
        }

        return $clausesArray;
    }
}
