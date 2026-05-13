<?php
class Notification
{
    private $id;
    private $utilisateur_id;
    private $message;
    private $lu;
    private $created_at;

    public function __construct($utilisateur_id, $message, $lu = 0, $id = null, $created_at = null)
    {
        $this->utilisateur_id = $utilisateur_id;
        $this->message = $message;
        $this->lu = $lu;
        $this->id = $id;
        $this->created_at = $created_at;
    }

    public function getId() { return $this->id; }
    public function getUtilisateurId() { return $this->utilisateur_id; }
    public function getMessage() { return $this->message; }
    public function getLu() { return $this->lu; }
    public function getCreatedAt() { return $this->created_at; }
}
?>
