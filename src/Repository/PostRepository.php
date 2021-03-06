<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Post $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Post $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

     /*
     * @return Livre[] Returns an array of Livre objects
     */
    
    public function search($value)
    {   //SELECT * FROM post as p WHERE p.title or WHERE p.content LIKE "%xxx%" ORDER BY p.created_at
        return $this->createQueryBuilder('p')//le paramètre p représente la table post (comme un alias dans une requête SQL)
            ->where('p.content LIKE :val')
            ->setParameter('val', "%$value%")
            ->orderBy('p.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    
    public function findByUserid($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere("p.user = :val")
            ->setParameter('val', $value)
            ->orderBy('p.createdAt', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * @return Post[] Returns an array of Post objects order by like
    */
    
    public function orderByLike()
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->addSelect('COUNT(l.post)')
            ->leftJoin(
                'App\Entity\PostLike',
                'l',
                'WITH',
                'p.id = l.post'
            ) 
            ->groupBy('p.id')
            ->orderBy('l.post', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
    


    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
