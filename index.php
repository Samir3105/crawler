<?php

declare(strict_types=1);
session_start();

require_once 'view/searchInput.html';
require_once 'model/Domain.php';
require_once 'model/inc/db.inc.php';

// /https?:\/\/w{3}\.[a-z]+\.[a-z]{2,3}/

// Create db-connection
Domain::connectToDb($db);

$domain = new Domain();
$domains = $domain->getAllDomains();

$reloaded = false;

/**
 * @param string $domain
 * the given url (Browse the URL)
 *
 * @return array
 * array with search results (links found)
 *
 */
function searchURL(string $domain) : array
{
    // Retrieve the entire content(html) from the specified domain
    $url = 'http://' . $domain->getDomain();
    $theHtmlToParse = file_get_contents($url);

    // Filter all links from the content
    $pattern = '/https?:\/\/w{3}\.[a-z]+\.[a-z]{2,3}/';
    preg_match_all($pattern, $theHtmlToParse, $ausgabe);

    // Return array with the result links
    return $ausgabe;
}

// Filter the links >> work on here
function sortUrlList($linksArray)
{
    $externPattern = '/w{3}./';
    $externLinks = preg_grep($externPattern, $linksArray);  // Extern Links

    $internMailLinks = array_diff($linksArray, $externLinks);

    $mailPattern = '/.[@]./';
    $mailLinks = preg_grep($mailPattern, $internMailLinks);

    $internLinks = array_diff($internMailLinks, $mailLinks);
    $trimmedArray = array_map('trim', $internLinks);

    $internLinksFiltered = array_filter($trimmedArray);  // Intern Links

    $sortedlinks = [
        'intern' => $internLinksFiltered,
        'extern' => $externLinks
    ];

    return $sortedlinks;
}

function createInternUrl($domain, $sortedUrlList)
{
    $internUrlList = $sortedUrlList['intern'];

    foreach ($internUrlList as &$url)
    {
        $url = 'http://' . $domain->getDomain() . '/' . $url;
    }

    return $internUrlList;
}

// Set reload flag
if(isset($_SESSION['lastSubmit']) && $_SESSION['lastSubmit'] == $_POST['domain'])
{
    $reloaded = true;
}

// Get the input url
if(!empty($_POST['domain']) && !$reloaded)
{
    // Set domain from input field and save it in db
    $domain->setDomain($_POST['domain']);
    $domain->insertData($_POST);

    // Start crawling
    $linksArrayGlobal = searchURL($domain);
    $sortedUrlList = sortUrlList($linksArrayGlobal);

    $internLinks = createInternUrl($domain, $sortedUrlList);
    $externUrlList = $sortedUrlList['extern'];

    $_SESSION['lastSubmit'] = $_POST['domain'];
    var_dump($sortedUrlList);
    var_dump($internLinks);
    var_dump($externUrlList);
}

var_dump($domains);