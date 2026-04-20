<?php

namespace App\Controllers;

use App\Models\MaintenanceTaskModel;
use App\Models\AccommodationUnitModel;

/**
 * MaintenanceController
 *
 * Gestiona el tablero Kanban de mantenimiento.
 * Fixes: delete por POST, view_cell reemplazado, scheduled_date en formulario.
 * Mejoras: filtros, stats, AJAX para cambio de estado.
 */
class MaintenanceController extends BaseController
{
    private MaintenanceTaskModel   $taskModel;
    private AccommodationUnitModel $unitModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface            $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->taskModel = new MaintenanceTaskModel();
        $this->unitModel = new AccommodationUnitModel();
    }

    // =========================================================================
    // INDEX — Tablero Kanban
    // =========================================================================
    public function index(): string
    {
        // Filtros opcionales por GET
        $filterUnit     = $this->request->getGet('unit_id')  ?? '';
        $filterPriority = $this->request->getGet('priority') ?? '';

        // Query base con join a unidades
        $query = $this->taskModel
            ->select('maintenance_tasks.*, accommodation_units.name as unit_name')
            ->join('accommodation_units',
                'accommodation_units.id = maintenance_tasks.unit_id', 'left')
            ->orderBy('maintenance_tasks.priority',
                'FIELD(maintenance_tasks.priority,"alta","media","baja")')
            ->orderBy('maintenance_tasks.scheduled_date', 'ASC');

        if ($filterUnit !== '') {
            $query->where('maintenance_tasks.unit_id', $filterUnit);
        }
        if ($filterPriority !== '') {
            $query->where('maintenance_tasks.priority', $filterPriority);
        }

        $allTasks = $query->findAll();

        // Organizar por estado para el Kanban
        $kanban = [
            'pending'     => [],
            'in_progress' => [],
            'completed'   => [],
        ];
        foreach ($allTasks as $task) {
            $kanban[$task['status']][] = $task;
        }

        // Stats del dashboard
        $stats = [
            'total'       => count($allTasks),
            'pending'     => count($kanban['pending']),
            'in_progress' => count($kanban['in_progress']),
            'completed'   => count($kanban['completed']),
            'blocking'    => count(array_filter($allTasks,
                fn($t) => $t['blocks_unit'] && $t['status'] !== 'completed')),
            'overdue'     => count(array_filter($allTasks,
                fn($t) => !empty($t['scheduled_date'])
                    && $t['scheduled_date'] < date('Y-m-d')
                    && $t['status'] !== 'completed')),
        ];

        $units = $this->unitModel->findAll();

        return view('maintenance/index', [
            'kanban'         => $kanban,
            'units'          => $units,
            'stats'          => $stats,
            'filterUnit'     => $filterUnit,
            'filterPriority' => $filterPriority,
        ]);
    }

    // =========================================================================
    // STORE — Crear nueva tarea
    // =========================================================================
    public function store(): \CodeIgniter\HTTP\RedirectResponse
    {
        $rules = [
            'title'    => 'required|max_length[150]',
            'priority' => 'required|in_list[baja,media,alta]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode(' ', $this->validator->getErrors()));
        }

        $blocksUnit = $this->request->getPost('blocks_unit') ? 1 : 0;
        $unitId     = $this->request->getPost('unit_id') ?: null;

        $this->taskModel->db->transStart();

        $this->taskModel->createForTenant([
            'unit_id'        => $unitId,
            'title'          => $this->request->getPost('title'),
            'description'    => $this->request->getPost('description'),
            'priority'       => $this->request->getPost('priority'),
            'scheduled_date' => $this->request->getPost('scheduled_date') ?: null,
            'blocks_unit'    => $blocksUnit,
            'status'         => 'pending',
        ]);

        // Bloquear unidad si aplica
        if ($blocksUnit && $unitId) {
            $this->unitModel->update($unitId, ['status' => 'maintenance']);
            log_message('info', "[Maintenance] Unidad #{$unitId} bloqueada por nueva tarea.");
        }

        $this->taskModel->db->transComplete();

        return redirect()->to('/maintenance')
            ->with('success', 'Tarea registrada correctamente.');
    }

    // =========================================================================
    // UPDATE STATUS — Cambiar estado (POST + AJAX)
    // =========================================================================
    public function updateStatus(int $id): \CodeIgniter\HTTP\ResponseInterface|\CodeIgniter\HTTP\RedirectResponse
    {
        $task = $this->taskModel->find($id);

        if (!$task) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tarea no encontrada']);
            }
            return redirect()->back()->with('error', 'Tarea no encontrada.');
        }

        $newStatus = $this->request->getPost('status');

        if (!in_array($newStatus, ['pending', 'in_progress', 'completed'])) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Estado inválido']);
            }
            return redirect()->back()->with('error', 'Estado inválido.');
        }

        $this->taskModel->db->transStart();

        $this->taskModel->update($id, ['status' => $newStatus]);

        // Liberar unidad al completar
        if ($newStatus === 'completed' && $task['blocks_unit'] && $task['unit_id']) {
            // Verificar que no haya otras tareas bloqueando la misma unidad
            $otherBlocking = $this->taskModel
                ->where('unit_id', $task['unit_id'])
                ->where('blocks_unit', 1)
                ->where('status !=', 'completed')
                ->where('id !=', $id)
                ->countAllResults();

            if ($otherBlocking === 0) {
                $this->unitModel->update($task['unit_id'], ['status' => 'available']);
                log_message('info', "[Maintenance] Unidad #{$task['unit_id']} liberada al completar tarea #{$id}.");
            }
        }

        $this->taskModel->db->transComplete();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success'    => true,
                'new_status' => $newStatus,
                'task_id'    => $id,
            ]);
        }

        return redirect()->to('/maintenance')
            ->with('success', 'Estado actualizado.');
    }

    // =========================================================================
    // DELETE — Eliminar tarea (POST — FIX seguridad)
    // =========================================================================
    public function delete(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $task = $this->taskModel->find($id);

        if ($task) {
            // Liberar unidad si la tarea la bloqueaba
            if ($task['blocks_unit'] && $task['unit_id'] && $task['status'] !== 'completed') {
                $otherBlocking = $this->taskModel
                    ->where('unit_id', $task['unit_id'])
                    ->where('blocks_unit', 1)
                    ->where('status !=', 'completed')
                    ->where('id !=', $id)
                    ->countAllResults();

                if ($otherBlocking === 0) {
                    $this->unitModel->update($task['unit_id'], ['status' => 'available']);
                }
            }

            $this->taskModel->delete($id);
            log_message('info', "[Maintenance] Tarea #{$id} eliminada.");
        }

        return redirect()->to('/maintenance')
            ->with('success', 'Tarea eliminada.');
    }
}