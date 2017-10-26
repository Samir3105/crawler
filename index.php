<?php

declare(strict_types=1);
session_start();

require_once 'view/searchInput.html';
require_once 'model/Domain.php';
require_once 'model/inc/db.inc.php';
require_once 'controller/IndexController.php';


// Create db connection
Domain::connectToDb($db);

// Create IndexController
$indexController = new IndexController();

// Create domain and get all found
$domain = new Domain();
$domains = $domain->getAllDomains();

// Flag for page reload
$reloaded = false;


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
    $linksArrayGlobal = $indexController->searchWebLinkInUrl($domain->getDomain());
    $sortedUrlList = $indexController->sortUrlList($linksArrayGlobal, $domain->getDomain());

    // Filter $sortedUrlList and save them in db
    $indexController->filterSortedUrlListAndSave($sortedUrlList, $domain);

    var_dump($sortedUrlList);
    echo '----------------------------- INTERN LINKS';
    var_dump($sortedUrlList['intern']);

    echo '----------------------------- EXTERN LINKS';
    var_dump($sortedUrlList['extern']);
}

echo '----------------------------- ALL DOMAINS';
var_dump($domains);