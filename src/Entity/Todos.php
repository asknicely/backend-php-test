<?php
namespace Entity;
/**
 * Todos
 *
 * @Table(name="todos")
 * @Entity()
 */
class Todos
{
    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;
    /**
     * @Column(name="user_id", type="integer")
     */
    private $user_id;
    /**
     * @Column(name="description", type="string", length=255)
     */
    private $description;
    /**
     * @Column(name="complete", type="integer")
     */
    private $complete;

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
     * @param integer $user_id
     * @return Todos
     */
    public function setUser_id($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }
    /**
     * Get user_id
     *
     * @return integer
     */
    public function getUser_id()
    {
        return $this->user_id;
    }

    /**
     * Get user_id
     *
     * @return integer
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
     * Set complete
     *
     * @param integer $complete
     * @return Todos
     */
    public function setComplete($complete)
    {
        $this->complete = $complete;
        return $this;
    }
    /**
     * Get complete
     *
     * @return integer
     */
    public function getComplete()
    {
        return $this->complete;
    }
}
