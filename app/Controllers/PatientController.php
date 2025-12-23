<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\PatientModel;

class PatientController extends BaseController
{
    private $patientModel;

    public function __construct()
    {
        // Verifica se o usuário está logado
        Auth::protect();
            // header('Location: login');
            // exit;
        
        $this->patientModel = new PatientModel();
    }

    public function create()
    {
        // Renderiza a view de cadastro
        $this->view('pages/cadastro_paciente');
    }

    public function store()
    {
        // Validação básica
        if (empty($_POST['nome'])) {
            $_SESSION['error'] = "O campo Nome é obrigatório.";
            header('Location: /paciente/novo');
            exit;
        }

        // Prepara os dados
        $data = [
            'user_id' => Auth::id(), // Vincula ao profissional logado
            'nome' => trim($_POST['nome']),
            'email' => !empty($_POST['email']) ? trim($_POST['email']) : null,
            'cpf' => !empty($_POST['cpf']) ? trim($_POST['cpf']) : null,
            'datanascimento' => !empty($_POST['datanascimento']) ? $_POST['datanascimento'] : null,
            'genero' => !empty($_POST['genero']) ? $_POST['genero'] : null,
            'tag' => !empty($_POST['tag']) ? trim($_POST['tag']) : null,
            'observacoes' => !empty($_POST['observacoes']) ? trim($_POST['observacoes']) : null
        ];

        // Tenta salvar no banco
        if ($this->patientModel->create($data)) {
            $_SESSION['success'] = "Paciente cadastrado com sucesso!";
            header('Location: /meus-tokens'); // Redireciona para lista ou onde preferir
            exit;
        } else {
            $_SESSION['error'] = "Erro ao cadastrar paciente. Tente novamente.";
            header('Location: /paciente/novo');
            exit;
        }
    }
}