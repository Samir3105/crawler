<?php

declare(strict_types=1);
session_start();

require_once 'view/searchInput.html';
require_once 'model/Domain.php';
require_once 'model/inc/db.inc.php';

// /https?:\/\/w{3}\.[a-z]+\.[a-z]{2,3}/
// https?:\/\/w{3}\.[a-z]+\.[a-z]+\/?([a-z]+)?
// https?:\/\/w{3}\.[a-z]+\.[a-z]+(\/?([a-z]+)?)+(\.html)?

// Create db connection
Domain::connectToDb($db);

// Create domain and get all searched
$domain = new Domain();
$domains = $domain->getAllDomains();

// Flag for page reload
$reloaded = false;

/**
 * @param string $urlSearchThrough
 * the given url (Browse the URL)
 *
 * @return array
 * array with search results (links found)
 *
 */
function searchWebLinkInUrl(string $urlSearchThrough) : array
{
    // Retrieve the entire content(html) from the specified domain
    $url = 'http://' . $urlSearchThrough;
    $theHtmlToParse = file_get_contents($url);

    // Filter all links from the content
    $pattern = '/https?:\/\/w{3}\.[a-z]+\.[a-z]+(\/?([a-z]+)?)+(\.html)?/';
    preg_match_all($pattern, $theHtmlToParse, $ausgabe);

    // Return array with the result links
    return $ausgabe;
}

/**
 * @param array $linksArray
 * array which contains internal and external links from the given url
 *
 * @param string $givenUrl
 * the given url
 *
 * @return array
 * associative array with sorted links (intern, extern)
 */
function sortUrlList(array $linksArray, string $givenUrl) : array
{
    // Separate internal and external link and store it in the respective array
    $internPattern = '/https?:\/\/'.$givenUrl.'/';
    $internLinks = [];
    $externLinks = [];

    foreach ($linksArray[0] as $url)
    {
        if(preg_match_all($internPattern, $url) === 1)
        {
            array_push($internLinks, $url);
        }
        else
        {
            array_push($externLinks, $url);
        }
    }

    $sortedlinks = [
        'intern' => $internLinks,
        'extern' => $externLinks
    ];

    return $sortedlinks;
}

function filterSortedUrlListAndSave(array $sortedUrlList, $domain)
{
    $filteredInternLinks = array_unique($sortedUrlList['intern']);
    $filteredExternLinks = array_unique($sortedUrlList['extern']);

    // Save the url's here
    $domain->insertData($_POST, $filteredExternLinks, $filteredInternLinks);
}

// Set reload flag
if(isset($_SESSION['lastSubmit']) && $_SESSION['lastSubmit'] == $_POST['domain'])
{
    $reloaded = true;
}

// Get the input url
if(!empty($_POST['domain']) && !$reloaded)
{
    // Set domain from input field
    $domain->setDomain($_POST['domain']);

    // Start crawling
    $linksArrayGlobal = searchWebLinkInUrl($domain->getDomain());
    $sortedUrlList = sortUrlList($linksArrayGlobal, $domain->getDomain());

    // Filter $sortedUrlList and save them in db
    filterSortedUrlListAndSave($sortedUrlList, $domain);

    var_dump($sortedUrlList);
    echo '----------------------------- INTERNE LINKS';
    var_dump($sortedUrlList['intern']);

    echo '----------------------------- EXTERNE LINKS';
    var_dump($sortedUrlList['extern']);
}

echo '----------------------------- ALL DOMAINS';
var_dump($domains);