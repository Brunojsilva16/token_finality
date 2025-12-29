<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\PatientModel;

class PatientController extends BaseController
{
    private $patientModel;

    public function __construct()
    {
        if (!Auth::init()) {
            // Se for uma requisição API (verificando cabeçalho ou URL), retorna 401 JSON
            if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
                header('HTTP/1.1 401 Unauthorized');
                echo json_encode(['error' => 'Não autorizado']);
                exit;
            }
            
            $redirect = defined('URL_BASE') ? URL_BASE . '/login' : '/login';
            header("Location: $redirect");
            exit;
        }
        $this->patientModel = new PatientModel();
    }

    // LISTAR: Página HTML (Tabela)
    public function index()
    {
        $pagina = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
        $search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
        
        $limite = 10;
        $offset = ($pagina - 1) * $limite;

        try {
            $totalRegistros = $this->patientModel->countAll($search);
            $totalPaginas = ceil($totalRegistros / $limite);
            $pacientes = $this->patientModel->getPaginated($limite, $offset, $search);
        } catch (\PDOException $e) {
            $pacientes = [];
            $totalPaginas = 1;
            $totalRegistros = 0;
            $_SESSION['error'] = "Erro: " . $e->getMessage();
        }
        
        $this->view('pages/pacientes_lista', [
            'pacientes' => $pacientes,
            'paginaAtual' => $pagina,
            'totalPaginas' => $totalPaginas,
            'totalRegistros' => $totalRegistros,
            'search' => $search
        ]);
    }

    // --- NOVO MÉTODO API ---
    // Retorna JSON para o autocomplete da página Gerar Token
    public function apiSearch()
    {
        // Aceita 'q' (padrão) ou 'term' (select2) ou 'search'
        $search = $_GET['q'] ?? $_GET['term'] ?? $_GET['search'] ?? '';
        
        try {
            // Busca até 30 pacientes que correspondam ao termo
            // Reutiliza o método getPaginated do model que já filtra por Nome ou CPF
            $pacientes = $this->patientModel->getPaginated(30, 0, $search);
            
            // Garante que o header seja JSON
            header('Content-Type: application/json');
            echo json_encode($pacientes);
            exit; // Interrompe para não carregar layout/view
        } catch (\Exception $e) {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public function create()
    {
        $this->view('pages/cadastro_paciente');
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) { $this->redirect('/pacientes'); return; }

        $paciente = $this->patientModel->find($id);
        if (!$paciente) { $this->redirect('/pacientes'); return; }

        $this->view('pages/cadastro_paciente', ['paciente' => $paciente]);
    }

    public function store()
    {
        $data = $_POST;
        try {
            if (!empty($data['id_paciente'])) {
                if ($this->patientModel->update($data['id_paciente'], $data)) {
                    $_SESSION['success'] = "Paciente atualizado!";
                } else {
                    $_SESSION['error'] = "Erro ao atualizar.";
                }
            } else {
                if ($this->patientModel->create($data)) {
                    $_SESSION['success'] = "Paciente cadastrado!";
                } else {
                    $_SESSION['error'] = "Erro ao cadastrar.";
                }
            }
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erro: " . $e->getMessage();
        }
        $this->redirect('/pacientes');
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id && $this->patientModel->delete($id)) {
            $_SESSION['success'] = "Paciente excluído.";
        } else {
            $_SESSION['error'] = "Erro ao excluir.";
        }
        $this->redirect('/pacientes');
    }

}