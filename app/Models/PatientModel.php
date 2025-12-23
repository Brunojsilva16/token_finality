<?php

namespace App\Models;

use App\Database\Connection;
use PDO;

class PatientModel
{
    protected $conn;

    public function __construct()
    {
        $this->conn = Connection::getInstance();
    }

    // Cria um novo paciente
    public function create(array $data)
    {
        $sql = "INSERT INTO pacientes (user_id, nome, email, cpf, datanascimento, genero, tag, observacoes) 
                VALUES (:user_id, :nome, :email, :cpf, :datanascimento, :genero, :tag, :observacoes)";
        
        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':email', $data['email']);
        $stmt->bindValue(':cpf', $data['cpf']);
        $stmt->bindValue(':datanascimento', $data['datanascimento']);
        $stmt->bindValue(':genero', $data['genero']);
        $stmt->bindValue(':tag', $data['tag']);
        $stmt->bindValue(':observacoes', $data['observacoes']);

        return $stmt->execute();
    }

    // Busca pacientes de um usuário específico
    public function getByUserId($userId)
    {
        $sql = "SELECT * FROM pacientes WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Busca um paciente pelo ID (útil para edição futura)
    public function find($id)
    {
        $sql = "SELECT * FROM pacientes WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Verifica se paciente existe (exemplo por nome e user_id para evitar duplicidade simples)
    public function exists($nome, $userId)
    {
        $sql = "SELECT id FROM pacientes WHERE nome = :nome AND user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':nome', $nome);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}