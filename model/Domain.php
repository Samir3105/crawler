<?php

declare(strict_types=1);
require_once 'Traits/DbConnector.php';

class Domain
{
    public $domain = '';
    public $allDomains = [];

    use DbConnector;

    public function __construct()
    {
        $this->setAllDomains($this->getAll());
    }

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain(string $domain)
    {
        if($domain != null)
        {
            $this->domain = $domain;
        }
        else
        {
            echo 'Wrong Type Given!';
        }
    }

    /**
     * @return array
     */
    public function getAllDomains(): array
    {
        return $this->allDomains;
    }

    /**
     * @param array $allDomains
     */
    public function setAllDomains(array $allDomains)
    {
        $this->allDomains = $allDomains;
    }

    /**
     * ACTIVE RECORD
     */

    public function insertData($filteredExternLinks, $filteredInternLinks)
    {
        if (!empty($_POST)) {

            $url = $_POST['domain'];

            try
            {
                $this->insertGivenUrl($_POST);
                $this->insertExternLinks($filteredExternLinks, $url);
                $this->insertInternLinks($filteredInternLinks, $url);
            }
            catch (PDOException $e)
            {
                echo $e;
            }
        }
    }

    private function insertGivenUrl($url)
    {
        $sql = 'INSERT INTO domains (domain) VALUES (:domain)';
        $statement = self::$db->prepare($sql);
        $statement->execute($_POST);
    }

    private function getUrlId($url)
    {
        $givenUrl = [$url];
        $sql = 'SELECT id from domains WHERE domain=?';
        $statement = self::$db->prepare($sql);
        //$statement->bindValue(':url', $url);
        $statement->execute($givenUrl);
        $id = $statement->fetch();
        return $id;
    }

    private function insertExternLinks($filteredExternLinks, $url)
    {
        $id = $this->getUrlId($url);

        foreach ($filteredExternLinks as $externLink)
        {
            $sqlExternLinks= 'INSERT INTO externdomains (externdomain, domain_id_fk) VALUES (?, ?)';
            $statement = self::$db->prepare($sqlExternLinks);

            $link = [$externLink, $id['id']];
            $statement->execute($link);
        }
    }

    private function insertInternLinks($filteredInternLinks, $url)
    {
        $id = $this->getUrlId($url);

        foreach ($filteredInternLinks as $internLink)
        {
            $sqlInternLinks= 'INSERT INTO subdomains (subdomain, domain_id_fk) VALUES (?, ?)';
            $statement = self::$db->prepare($sqlInternLinks);

            $link = [$internLink, $id['id']];
            $statement->execute($link);
        }
    }

    public function getAll()
    {

        $sql = 'SELECT * FROM domains';

        $statement = self::$db->prepare($sql);
        $statement->execute();

        $domains = $statement->fetchAll();
        return $domains;
    }
}