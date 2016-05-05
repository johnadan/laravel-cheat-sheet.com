<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use League\CommonMark\Converter;
use \Htmldom;
use App\Clause;
use App\Section;

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

    protected $sections;

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

        $this->repository = config('csheet.repository');

        $this->sections = Section::all();
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
        foreach ($this->sections as $section) {
            $data = $this->parseOneFile($section->filename, $language);

            dd($data);
            // Save clauses parsed from one file of one language
            //$section->

        }

    }

    /**
     * Parse one file of given language.
     *
     * @return array Array of parsed clauses from one file, ready
     *         for inserting into a DB.
     */
    private function parseOneFile($filename, $language)
    {
        // Get MD file contents.
        $md = $this->getMdFileContents($filename, $language);

        // Convert MD to HTML.
        $sourceHtml = $this->converter->convertToHtml($md);

        $htmlExploded = explode('<hr />', $sourceHtml);

        // Parse clauses, passing HTML before [0] and after [1] hr tag
        return $this->parseClauses($htmlExploded[0], $htmlExploded[1]);
    }

    /**
     * Get MD file contents from GitHub.
     *
     * @return string
     */
    private function getMdFileContents($filename, $language)
    {
        $filename = str_replace(' ', '%20', $filename);

        $url = $this->repository.$language.'/'.$filename.'.md';

        return file_get_contents($url);
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

        // Find all clauses by their tags.
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
        $link = $description[0]->next_sibling()->plaintext;

        // Description itself
        $description = $description[0]->parent()->next_sibling()->plaintext;

        return compact('clause', 'description', 'link');
    }
}
