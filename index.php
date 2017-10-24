<?php

declare(strict_types=1);
session_start();

require_once 'view/searchInput.html';
require_once 'model/Domain.php';
require_once 'model/inc/db.inc.php';

Domain::connectToDb($db);

$domain = new Domain();
$domains = $domain->getAllDomains();

$reloaded = false;

function searchURL($domain)
{

    $url = 'http://' . $domain->getDomain();
    $theHtmlToParse = file_get_contents($url);

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($theHtmlToParse);

    $links = $doc->getElementsByTagName('a');
    $linksArray = [];

    foreach ($links as $link)
    {
        array_push($linksArray, $link->nodeValue);
    }
    return $linksArray;
}

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

if(isset($_SESSION['lastSubmit']) && $_SESSION['lastSubmit'] == $_POST['domain'])
{
    $reloaded = true;
}

if(!empty($_POST['domain']) && !$reloaded)
{
    $domain->setDomain($_POST['domain']);
    $domain->insertData($_POST);

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


// Search URL in the web and list all links >> DONE

// Group the links in category intern and extern >> DONE

// Search extern links and sort them by domain

// Save result in database