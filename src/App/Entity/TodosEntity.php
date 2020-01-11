<?php

namespace App\Entity;

/**
 * Form
 *
 * @ORM\Entity
 * @Table(name="todos")
 * @Entity()
 */
class TodosEntity
{
    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Column(name="user_id", type="integer", length=11, nullable=false)
     */
    private $user_id;

    /**
     * @Column(name="description", type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @Column(type="string", columnDefinition="ENUM('0', '1')")
     */

    private $is_complete;

    /**
     * __construct
     */
    public function __construct()
    {
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user_id
     *
     * @param string $user_id
     * @return Todos
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;

        return $this;
    }

    /**
     * Get user_id
     *
     * @return string
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Todos
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }


    /**
     * Set is_complete
     *
     * @param string $is_complete
     * @return Todos
     */
    public function setIsComplete($is_complete)
    {
        $this->is_complete = $is_complete;

        return $this;
    }

    /**
     * Get is_complete
     *
     * @return string
     */
    public function getIsComplete()
    {
        return $this->is_complete;
    }

}
