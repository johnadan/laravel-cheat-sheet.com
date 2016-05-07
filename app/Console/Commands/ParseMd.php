<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use League\CommonMark\Converter;
use \Htmldom;
use App\Clause;
use App\Section;
use DB;

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

    protected $language;

    protected $sections;

    protected $section;

    protected $repository;

    protected $clauseLink;

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
    public function handle(Converter $converter)
    {
        $this->converter = $converter;

        $this->languages = config('csheet.languages');

        $this->repository = config('csheet.repository');

        $this->sections = Section::all();

        DB::statement('truncate clauses');

        $this->parseMdFiles();
    }

    /**
     * Parse all MD files to DB.
     *
     * @return mixed
     */
    private function parseMdFiles()
    {
        foreach ($this->sections as $section) {
            $this->section = $section;

            $data = $this->parseOneFile();
        }
    }

    /**
     * Parse one file of given language.
     *
     * @return array Array of parsed clauses from one file, ready
     *         for inserting into a DB.
     */
    private function parseOneFile()
    {
        // Get MD file contents.
        $md = $this->getMdFileContents();

        // Convert MD to HTML.
        $sourceHtml = $this->converter->convertToHtml($md);

        // Parse clauses, passing HTML before [0] and after [1] hr tag
        return $this->parseClauses($sourceHtml);
    }

    /**
     * Get MD file contents from GitHub.
     *
     * @return string
     */
    private function getMdFileContents()
    {
        $filename = str_replace(' ', '%20', $this->section->filename);

        $url = $this->repository.$filename.'.md';

        return file_get_contents($url);
    }

    /**
     * Parse all clauses one by one.
     *
     * @return array Clauses array of arrays, ready for inserting to a DB
     */
    private function parseClauses($sourceHtml)
    {
        // This contents HTML of part before hr tag
        $html = new Htmldom($sourceHtml);

        // Find all clauses by their tags.
        $allClauses = $html->find('p a');

        // Iterate over all clauses to get Html elements
        foreach ($allClauses as $clauseLink) {
            // Adding resulting array into clauses array
            $this->clauseLink = $clauseLink;
            $data = $this->parseClause();
/*
            // Save data to a model
            $clause = new Clause($data);
            $this->section->clauses()->save($clause);
*/
        }
    }

    /**
     * Gets Html of two elements.
     *
     * @return array Clause, text, link to documentation.
     */

    private function parseClause()
    {
        foreach ($this->languages as $language) {
            $this->parseOneLanguageDescription($language);
        }


        //$clauseLink->parent()->next_sibling()->plaintext);
/*
        // This clause HTML
        $clauseHtml = $html->find('a')[0];

        // This clause 'href'
        $href = $clauseHtml->href;

        // This clause link contents
        $clause = $clauseHtml->innertext;

        // Find 'a' element in descriptions by it's 'name'
        $description = $descriptionHtml->find('p a[name='.$href.']');

        // Link from description
        $link = $description[0]->parent()->last_child()->href;

        // Description itself
        $description = $description[0]->parent()->next_sibling()->plaintext;

        $language = $this->language;

        $slug = str_slug($clause);

        return compact('clause', 'description', 'link', 'language', 'slug');
        */
    }


    /**
     * Parse files of given language.
     *
     */
    private function parseOneLanguageDescription($language)
    {
        $clauseDescriptionLang = $this->clauseLink->parent()->next_sibling()->plaintext;

        $langHtml = substr($clauseDescriptionLang, 0, 2);

        $descriptionHtml = substr($clauseDescriptionLang, 3);

        if($langHtml === $language)
        echo($langHtml.' ---- '.$descriptionHtml);
        //echo($clauseDescriptionLang);
        exit;
    }
}
