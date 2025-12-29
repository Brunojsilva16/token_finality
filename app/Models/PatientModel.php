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

    // Busca pacientes com paginação e pesquisa
    public function getPaginated($limit, $offset, $search = '')
    {
        $sql = "SELECT * FROM pacientes WHERE 1=1";
        
        if (!empty($search)) {
            $sql .= " AND (nome LIKE :search OR cpf LIKE :search)";
        }
        
        $sql .= " ORDER BY nome ASC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%");
        }
        
        // IMPORTANTE: PDO exige PARAM_INT para limit/offset
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Contagem total para a paginação
    public function countAll($search = '')
    {
        $sql = "SELECT COUNT(*) as total FROM pacientes WHERE 1=1";
        
        if (!empty($search)) {
            $sql .= " AND (nome LIKE :search OR cpf LIKE :search)";
        }
        
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%");
        }
        
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    // Buscar um paciente pelo ID
    public function find($id)
    {
        $sql = "SELECT * FROM pacientes WHERE id_paciente = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // CRIAR PACIENTE (INSERT) - Corrigido erro de parâmetros
    public function create(array $data)
    {
        $sql = "INSERT INTO pacientes (
            user_id, nome, email, cpf, telefone, data_nascimento, genero, 
            nome_responsavel, responsavel_financeiro,
            cep, logradouro, numero, complemento, bairro, cidade, estado, tags, observacoes
        ) VALUES (
            :user_id, :nome, :email, :cpf, :telefone, :data_nascimento, :genero, 
            :nome_responsavel, :responsavel_financeiro,
            :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :estado, :tags, :observacoes
        )";
        
        $stmt = $this->conn->prepare($sql);
        
        // Binds obrigatórios (Removemos o 'if' para evitar o erro de parâmetro faltante)
        $stmt->bindValue(':user_id', $data['user_id']); 
        $stmt->bindValue(':nome', $data['nome']);
        
        // Binds opcionais (Null Coalescing)
        $stmt->bindValue(':email', $data['email'] ?? null);
        $stmt->bindValue(':cpf', $data['cpf'] ?? null);
        $stmt->bindValue(':telefone', $data['telefone'] ?? null);
        $stmt->bindValue(':data_nascimento', !empty($data['datanascimento']) ? $data['datanascimento'] : null);
        $stmt->bindValue(':genero', $data['genero'] ?? null);
        $stmt->bindValue(':nome_responsavel', $data['responsavel'] ?? null);
        $stmt->bindValue(':responsavel_financeiro', $data['responsavel_financeiro'] ?? null);
        $stmt->bindValue(':cep', $data['cep'] ?? null);
        $stmt->bindValue(':logradouro', $data['logradouro'] ?? null);
        $stmt->bindValue(':numero', $data['numero'] ?? null);
        $stmt->bindValue(':complemento', $data['complemento'] ?? null);
        $stmt->bindValue(':bairro', $data['bairro'] ?? null);
        $stmt->bindValue(':cidade', $data['cidade'] ?? null);
        $stmt->bindValue(':estado', $data['estado'] ?? null);
        $stmt->bindValue(':tags', $data['tags'] ?? null);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);

        return $stmt->execute();
    }

    // ATUALIZAR PACIENTE (UPDATE)
    public function update($id, array $data)
    {
        // Nota: Não atualizamos o user_id na edição para manter histórico de quem criou
        $sql = "UPDATE pacientes SET 
            nome = :nome,
            email = :email,
            cpf = :cpf,
            telefone = :telefone,
            data_nascimento = :data_nascimento,
            genero = :genero,
            nome_responsavel = :nome_responsavel,
            responsavel_financeiro = :responsavel_financeiro,
            cep = :cep,
            logradouro = :logradouro,
            numero = :numero,
            complemento = :complemento,
            bairro = :bairro,
            cidade = :cidade,
            estado = :estado,
            tags = :tags,
            observacoes = :observacoes
            WHERE id_paciente = :id";

        $stmt = $this->conn->prepare($sql);
        
        $stmt->bindValue(':id', $id);
        
        // Binds dos dados
        $stmt->bindValue(':nome', $data['nome']);
        $stmt->bindValue(':email', $data['email'] ?? null);
        $stmt->bindValue(':cpf', $data['cpf'] ?? null);
        $stmt->bindValue(':telefone', $data['telefone'] ?? null);
        $stmt->bindValue(':data_nascimento', !empty($data['datanascimento']) ? $data['datanascimento'] : null);
        $stmt->bindValue(':genero', $data['genero'] ?? null);
        $stmt->bindValue(':nome_responsavel', $data['responsavel'] ?? null);
        $stmt->bindValue(':responsavel_financeiro', $data['responsavel_financeiro'] ?? null);
        $stmt->bindValue(':cep', $data['cep'] ?? null);
        $stmt->bindValue(':logradouro', $data['logradouro'] ?? null);
        $stmt->bindValue(':numero', $data['numero'] ?? null);
        $stmt->bindValue(':complemento', $data['complemento'] ?? null);
        $stmt->bindValue(':bairro', $data['bairro'] ?? null);
        $stmt->bindValue(':cidade', $data['cidade'] ?? null);
        $stmt->bindValue(':estado', $data['estado'] ?? null);
        $stmt->bindValue(':tags', $data['tags'] ?? null);
        $stmt->bindValue(':observacoes', $data['observacoes'] ?? null);

        return $stmt->execute();
    }

    // Deletar Paciente
    public function delete($id)
    {
        // Removemos o vínculo obrigatório com user_id na exclusão para permitir admins excluírem qualquer um, 
        // mas você pode voltar com "AND user_id = :user_id" se quiser travar.
        $sql = "DELETE FROM pacientes WHERE id_paciente = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        
        return $stmt->execute();
    }

    // Métodos auxiliares legados (se necessário)
    public function getAll()
    {
        $sql = "SELECT * FROM pacientes ORDER BY nome ASC";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}