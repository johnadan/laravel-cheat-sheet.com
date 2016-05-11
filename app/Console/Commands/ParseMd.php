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

    protected $currentElement;

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
     * Parse all MD files.
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
     * Parse one file.
     */
    private function parseOneFile()
    {
        // Get MD file contents.
        $md = $this->getMdFileContents();

        // Convert MD to HTML.
        $sourceHtml = $this->converter->convertToHtml($md);

        // Parse clauses
        $this->parseClauses($sourceHtml);
        
        return;
    }

    /**
     * Reverse parsing, because of wrong parsing of <code>.
     *
     * @return string
     */
    private function reverseHtmlSpaces($html)
    {
        return str_replace('ё', '&nbsp;', $html);
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
        $html = new Htmldom(nl2br($sourceHtml));

        // Find all clauses by their tags.
        $allClauses = $html->find('p a');

        // Iterate over all clauses to get Html elements
        foreach ($allClauses as $clauseLink) {
            // Adding resulting array into clauses array
            $this->clauseLink = $clauseLink;
            
            $data = $this->parseClause();
        }
    }

    /**
     * Gets Html of two elements.
     *
     * @return array Clause, text, link to documentation.
     */

    private function parseClause()
    {
        $this->currentElement = $this->clauseLink->parent();

        // Iterate through languages
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
        $this->currentElement = $this->currentElement
                                ->next_sibling()
                                ->next_sibling();

        $clauseLang = $this->currentElement->plaintext;

        $this->currentElement = $this->currentElement
                                ->next_sibling()
                                ->next_sibling();

        $desc = trim($this->currentElement->innertext);

        // Small adjustments to <code> tags
        $desc = str_replace('<code>', '<code> ', $desc);

        $desc = str_replace('<br /> </code>', '</code>', $desc);

        if ($clauseLang === $language) {
            // Save data to a model
            $clause = new Clause([
                'clause' => $this->clauseLink->plaintext,
                'link' => $this->clauseLink->href,
                'language' => $language,
                'description' => $desc,
                'slug' => str_slug($this->clauseLink->plaintext)
            ]);

            $this->section->clauses()->save($clause);
        }
    }
}
