<?php
namespace ZerobRSS\Dao;

use \Doctrine\DBAL\Connection as Db;

class Articles
{
    /** @var Db */
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }



    public function create($values)
    {
        $query = $this->db->createQueryBuilder()
               ->insert('articles');

        // Append parameters to insert to the query
        foreach ($values as $key => $value) {
            $query = $query->setValue($key, ':'.$key)->setParameter(':'.$key, $value);
        }

        $query->execute();

        // @TODO: Check if this works in MariaDB
        return $this->db->lastInsertId('articles_id_seq');
    }



    // Update article by unique identifier
    public function update($identifier, $values)
    {
        $query = $this->db->createQueryBuilder()
               ->update('articles')
               ->where('identifier = :id AND feed_id = :feedid')
               ->setParameter(':id', $values['identifier'])
               ->setParameter(':feedid', $values['feed_id']);

        // Append parameters to insert to the query
        foreach ($values as $key => $value) {
            $query = $query->set($key, ':'.$key)->setParameter(':'.$key, $value);
        }

        return $query->execute();
    }


    public function getArticles($feedId, $userId)
    {
        return $this->db->createQueryBuilder()
            ->select('a.*')
            ->from('articles', 'a')
            ->innerJoin('a', 'feeds', 'f', 'f.id = a.feed_id')
            ->where('a.feed_id = :feed_id AND f.user_id = :user_id')
            ->orderBy('date', 'DESC')
            ->setParameter(':feed_id', $feedId)
            ->setParameter(':user_id', $userId)
            ->execute();
    }

    /**
     * Get Paged Articles
     * @param $feedId integer Feed ID
     * @param $previousId integer (optional) default: 0
     * @param $read boolean Choose if you want read articles or not, default null -> returns both
     */
    public function getPagedArticles($feedId, $previousId = null, $read = null)
    {
        $pageSize = 20;

        $queryBuilder = $this->db->createQueryBuilder();

        // Prepare default where-clause
        $whereClause = $queryBuilder->expr()->eq('feed_id', ':feed_id');

        // Append Read Where-Clause if defined
        if (null !== $read) {
            $whereClause = $queryBuilder->expr()->andX(
                $whereClause,
                $queryBuilder->expr()->eq('read', ':read')
            );

            $queryBuilder->setParameter(':read', $read);
        }

        // Append previous article where clause if defined
        if (null !== $previousId) {
            $whereClause = $queryBuilder->expr()->andX(
                $whereClause,
                $queryBuilder->expr()->lt('articles.id', ':previous_id')
            );

            $queryBuilder->setParameter(':previous_id', $previousId);
        }

        $query = $queryBuilder
            ->select('*')
            ->from('articles')
            ->where($whereClause)
            ->setParameter(':feed_id', $feedId)
            ->orderBy('date', 'DESC')
            ->setMaxResults($pageSize);

        return $query->execute();
    }

    /**
     * Get single article by UserId and Article Identifier (not article.id)
     */
    public function getArticleByIdentifier($userId, $identifier)
    {
        return $this->db->createQueryBuilder()
            ->select('a.*')
            ->from('articles', 'a')
            ->innerJoin('a', 'feeds', 'f', 'f.id = a.feed_id')
            ->where('f.user_id = :user_id AND a.identifier = :identifier')
            ->orderBy('date', 'DESC')
            ->setParameter(':user_id', $userId)
            ->setParameter(':identifier', $identifier)
            ->execute();
    }
}
