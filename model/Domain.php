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

    public function insertData()
    {
        if (!empty($_POST)) {

            try
            {
                $sql = 'INSERT INTO domains (domain) VALUES (:domain)';

                $statement = self::$db->prepare($sql);
                $statement->execute($_POST);
            }
            catch (PDOException $e)
            {
                echo $e;
            }
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