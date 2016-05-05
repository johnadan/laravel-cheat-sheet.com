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

        // Parse clauses, passing HTML before [0] and after [1] hr tag
        $clausesArray = $this->parseClauses($htmlExploded[0], $htmlExploded[1]);

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

    /**
     * Parse all clauses one by one.
     *
     * @return array Clauses array of arrays, ready for inserting to a DB
     */
    private function parseClauses($clauseList, $descriptionList)
    {
        $clausesArray = [];

        $html = new Htmldom($clauseList);

        $allClauses = $html->find('p a');

        // Iterate over all clauses to get Html elements
        foreach ($allClauses as $clauseLink) {
            // Adding resulting array into clauses array
            $clausesArray[] = $this->getHtmlDataFromClause($clauseLink, $descriptionList);
        }

        return $clausesArray;
    }

    /**
     * Gets Html of two elements.
     *
     * @return array Clause, text, link to documentation.
     */

    private function getHtmlDataFromClause($clauseLink, $descriptionList)
    {
        $html = new Htmldom($clauseLink);

        $descriptionHtml = new Htmldom($descriptionList);

        // This clause HTML
        $clauseHtml = $html->find('a')[0];

        // This clause 'href'
        $href = $clauseHtml->href;

        // This clause link contents
        $clause = $clauseHtml->innertext;

        // Find 'a' element in descriptions by it's 'name'
        $description = $descriptionHtml->find('p a[name='.$href.']');

        // Link from description
        $descriptionLink = $description[0]->next_sibling();

        // Description itself
        $descriptionText = $description[0]->parent()->next_sibling();

        return compact('clause', 'descriptionText', 'descriptionLink');
    }
}
