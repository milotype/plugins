<?php
/**
 * Search
 *
 * This REST module hooks on
 * following URLs
 *
 * /search
 */

use \API\Core\Tool;
use \Illuminate\Database\Capsule\Manager as DB;

// Minimal length of search string
$search_min_length = 2;
// Allowed languages for search
$allowed_languages = ['en', 'fr'];

$search = function() use($app) {
   global $search_min_length,
          $allowed_languages;

   $body = Tool::getBody();
   if ( $body == NULL ||
       !isset($body->query_string) ||
       strlen($body->query_string) < $search_min_length )
   {
      return Tool::endWithJson([
         "error" => "Your search string needs to ".
                  "have at least ".$search_min_length." chars"
      ], 400);
   }
   $query_string = $body->query_string;

   if (!isset($body->lang) ||
      !in_array($body->lang, $allowed_languages))
      $lang = 'en';
   else
      $lang = $body->lang;

   $_search = \API\Model\Plugin::short()
                               ->with('authors', 'versions', 'descriptions')
                               ->withDownloads()
                               ->withAverageNote()
                               ->descWithLang($lang)
                         ->where('name', 'LIKE', "%$query_string%")
                         ->orWhere('plugin_description.short_description', 'LIKE', "%$query_string%")
                         ->orWhere('plugin_description.long_description', 'LIKE', "%$query_string%")
                         ->get();
   Tool::endWithJson($_search);
};

$app->post('/search', $search);

$app->options('/search', function(){});